<?php

namespace tests\unit\models;

use app\models\basic\KinshipCalculus as K;

/**
 * Unit tests for the pure kinship algebra (no DB).
 */
class KinshipCalculusTest extends \Codeception\Test\Unit
{
    public function testComposeFirstHopReturnsEdge()
    {
        $this->assertSame('parent', K::compose(K::SELF, 'parent'));
        $this->assertSame('friend', K::compose(K::SELF, 'friend'));
        $this->assertSame('grandparent', K::compose(null, 'grandparent'));
    }

    public function testComposeBloodLineage()
    {
        $this->assertSame('grandparent', K::compose('parent', 'parent'));
        $this->assertSame('grandchild', K::compose('child', 'child'));
        $this->assertSame('great-grandparent', K::compose('grandparent', 'parent'));
        $this->assertSame('great-grandparent', K::compose('parent', 'grandparent'));
        $this->assertSame('great-great-grandparent', K::compose('grandparent', 'grandparent'));
    }

    public function testComposeCollateral()
    {
        $this->assertSame('uncle', K::compose('parent', 'sibling'));   // parent's sibling
        $this->assertSame('nephew', K::compose('sibling', 'child'));   // sibling's child
        $this->assertSame('sibling', K::compose('sibling', 'sibling'));
        $this->assertSame('grand-uncle', K::compose('grandparent', 'sibling'));
        $this->assertSame('cousin', K::compose('uncle', 'child'));     // uncle's child
    }

    public function testComposeCoParentResolvesToSelfAndIsNotInferred()
    {
        // My child's parent is me or my partner — the tree model collapses to
        // self, so no relation is inferred (null = do not extend).
        $this->assertNull(K::compose('child', 'parent'));
    }

    public function testComposeMarriageOneStep()
    {
        $this->assertSame('child-in-law', K::compose('child', 'partner'));
        $this->assertSame('sibling-in-law', K::compose('sibling', 'partner'));
        $this->assertSame('parent-in-law', K::compose('partner', 'parent'));
        $this->assertSame('sibling-in-law', K::compose('partner', 'sibling'));
        $this->assertSame('uncle', K::compose('uncle', 'partner')); // aunt/uncle by marriage
    }

    public function testComposeUnexpressibleReturnsNull()
    {
        $this->assertNull(K::compose('partner', 'partner'));
        $this->assertNull(K::compose('friend', 'friend'));
        $this->assertNull(K::compose('parent-in-law', 'parent'));
    }

    public function testComposeBeyondNamedDepthFallsBackToRelative()
    {
        $this->assertSame('relative', K::compose('great-great-grandparent', 'parent'));
    }

    public function testTokenComplement()
    {
        $this->assertSame('child', K::tokenComplement('parent'));
        $this->assertSame('parent', K::tokenComplement('child'));
        $this->assertSame('sibling', K::tokenComplement('sibling'));
        $this->assertSame('partner', K::tokenComplement('partner'));
        $this->assertSame('nephew', K::tokenComplement('uncle'));
        $this->assertSame('grandchild', K::tokenComplement('grandparent'));
        $this->assertSame('child-in-law', K::tokenComplement('parent-in-law'));
        $this->assertSame('grand-nephew', K::tokenComplement('grand-uncle'));
    }

    public function testTokenComplementUnknownIsNull()
    {
        // godmother's inverse "godchild" has no token → no reverse edge.
        $this->assertNull(K::tokenComplement('godmother'));
    }

    public function testCoordToToken()
    {
        $this->assertSame('cousin', K::coordToToken(2, 2));
        $this->assertSame('grand-uncle', K::coordToToken(3, 1));
        $this->assertSame('grand-nephew', K::coordToToken(1, 3));
        $this->assertSame('great-grandchild', K::coordToToken(0, 3));
        $this->assertNull(K::coordToToken(0, 0));
        $this->assertSame('relative', K::coordToToken(5, 0));
        $this->assertSame('relative', K::coordToToken(2, 3));
    }
}
