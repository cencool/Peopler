<?php

namespace tests\unit\models;

use app\models\basic\KinshipResolver;

/**
 * Unit tests for the BFS traversal, exercised on an injected in-memory graph
 * (no DB) via KinshipResolver::traverse().
 *
 * Fixture family (source = person 1, male):
 *   person 1's parent is 2 (Mary, f) — his mother
 *   2's parent is 3 (Anna, f) — person 1's grandmother
 *   3's child is 4 (Paul, m) — grandmother's son => person 1's uncle
 * Only the explicit relation 1<->2 is "given".
 *
 * Relations are listed as "this person is <relation> of <to_whom>", so from
 * person 1's (male) viewpoint: he is the *grandson* of 3 and the *nephew* of 4.
 * Adjacency edges are in "to is node's <token>" form (2 is 1's parent, etc.).
 */
class KinshipResolverTest extends \Codeception\Test\Unit
{
    private function adjacency(): array
    {
        return [
            1 => [['to' => 2, 'token' => 'parent', 'to_name' => 'Doe Mary', 'to_gender' => 'f']],
            2 => [
                ['to' => 1, 'token' => 'child', 'to_name' => 'Doe John', 'to_gender' => 'm'],
                ['to' => 3, 'token' => 'parent', 'to_name' => 'Doe Anna', 'to_gender' => 'f'],
            ],
            3 => [
                ['to' => 2, 'token' => 'child', 'to_name' => 'Doe Mary', 'to_gender' => 'f'],
                ['to' => 4, 'token' => 'child', 'to_name' => 'Doe Paul', 'to_gender' => 'm'],
            ],
            4 => [['to' => 3, 'token' => 'parent', 'to_name' => 'Doe Anna', 'to_gender' => 'f']],
        ];
    }

    private function nameMap(): array
    {
        // Output tokens describe the SOURCE (male), so we need his-gender labels
        // for the inverse tokens: grandchild -> grandson, nephew -> nephew.
        return [
            'm' => ['grandchild' => 'grandson', 'nephew' => 'nephew', 'relative' => 'relative'],
            'f' => ['grandchild' => 'granddaughter', 'niece' => 'niece', 'relative' => 'relative'],
        ];
    }

    public function testTraverseFindsDeepRelations()
    {
        $resolver = new KinshipResolver(1, 'owner', 'm', 4);
        $out = $resolver->traverse($this->adjacency(), [2 => true], $this->nameMap());

        $byId = [];
        foreach ($out as $r) {
            $byId[$r['to_whom_id']] = $r;
        }

        $this->assertCount(2, $out);
        // "person 1 is grandson of 3" — inverse of "3 is 1's grandparent",
        // gendered by the source (male). Depth 2.
        $this->assertSame('grandson', $byId[3]['relation']);
        $this->assertSame('Doe Anna', $byId[3]['relation_to_whom']);
        $this->assertSame(-1, $byId[3]['relation_id']);
        // "person 1 is nephew of 4" at depth 3 — unreachable by the old shallow
        // (depth-2) algorithm.
        $this->assertSame('nephew', $byId[4]['relation']);
        // The explicitly-related person 2 is never emitted as computed.
        $this->assertArrayNotHasKey(2, $byId);
    }

    public function testMaxDepthLimitsTraversal()
    {
        $out = (new KinshipResolver(1, 'owner', 'm', 2))
            ->traverse($this->adjacency(), [2 => true], $this->nameMap());
        $ids = array_column($out, 'to_whom_id');

        $this->assertContains(3, $ids);      // grandson-of-3 (depth 2) still found
        $this->assertNotContains(4, $ids);   // nephew-of-4 (depth 3) excluded by the cap
    }

    public function testSourceIsNeverEmitted()
    {
        $out = (new KinshipResolver(1, 'owner', 'm', 4))
            ->traverse($this->adjacency(), [2 => true], $this->nameMap());
        foreach ($out as $r) {
            $this->assertNotSame(1, $r['to_whom_id']);
        }
    }
}
