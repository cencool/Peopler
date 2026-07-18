<?php

namespace app\models\basic;

/**
 * KinshipCalculus — the pure (DB-free) kinship algebra used to compute implicit
 * relations.
 *
 * Relations are handled as gender-neutral *tokens* (the `token` column of
 * `relation_name`, e.g. `parent`, `child`, `grandparent`, `uncle`, ...).
 *
 * Two operations are provided:
 *  - {@see tokenComplement()} — the inverse token of a directed edge, used to
 *    build the reverse edge of a stored relation (parent -> child, uncle ->
 *    nephew, sibling -> sibling, ...).
 *  - {@see compose()} — given the relation `source -> u` (pathToken) and the
 *    relation `u -> v` (edgeToken), returns the relation `source -> v`.
 *
 * Blood (consanguineal) relations are composed with a small coordinate model:
 * a token maps to `[up, down]` = generations up to the nearest common ancestor,
 * then generations down to the target. Composition is then arithmetic, so deep
 * lineage/collateral relations (great-grandparent, grand-uncle, ...) fall out
 * automatically without a giant hand-written table. Marriage steps (partner and
 * the *-in-law tokens) don't fit the single-tree model, so the common one-step
 * cases are handled by an explicit table and anything else returns null (the
 * path is simply not extended).
 *
 * See COMPUTED_RELATIONS.md for the full rationale.
 */
class KinshipCalculus
{
    /** Sentinel path token for the source person (relation of a person to itself). */
    const SELF = 'self';

    /** Generic token for a valid blood relation that has no specific name. */
    const RELATIVE = 'relative';

    /**
     * Blood tokens as [generationsUp, generationsDown] from the nearest common
     * ancestor. Only these tokens take part in the coordinate arithmetic.
     */
    const COORDS = [
        'parent'                    => [1, 0],
        'child'                     => [0, 1],
        'sibling'                   => [1, 1],
        'grandparent'               => [2, 0],
        'grandchild'                => [0, 2],
        'uncle'                     => [2, 1], // uncle/aunt
        'nephew'                    => [1, 2], // nephew/niece
        'cousin'                    => [2, 2],
        'great-grandparent'         => [3, 0],
        'great-grandchild'          => [0, 3],
        'grand-uncle'               => [3, 1], // great-uncle/great-aunt
        'grand-nephew'              => [1, 3], // grand-nephew/grand-niece
        'great-great-grandparent'   => [4, 0],
        'great-great-grandchild'    => [0, 4],
    ];

    /**
     * Inverse token for a directed edge. Tokens absent here (e.g. godmother,
     * whose inverse "godchild" has no token) yield no reverse edge.
     */
    const COMPLEMENTS = [
        'parent'                    => 'child',
        'child'                     => 'parent',
        'grandparent'               => 'grandchild',
        'grandchild'                => 'grandparent',
        'great-grandparent'         => 'great-grandchild',
        'great-grandchild'          => 'great-grandparent',
        'great-great-grandparent'   => 'great-great-grandchild',
        'great-great-grandchild'    => 'great-great-grandparent',
        'sibling'                   => 'sibling',
        'partner'                   => 'partner',
        'uncle'                     => 'nephew',
        'nephew'                    => 'uncle',
        'grand-uncle'               => 'grand-nephew',
        'grand-nephew'              => 'grand-uncle',
        'cousin'                    => 'cousin',
        'parent-in-law'             => 'child-in-law',
        'child-in-law'              => 'parent-in-law',
        'sibling-in-law'            => 'sibling-in-law',
        'friend'                    => 'friend',
        'colleague'                 => 'colleague',
        'classmate'                 => 'classmate',
        'acquaintance'              => 'acquaintance',
        self::RELATIVE              => self::RELATIVE,
    ];

    /**
     * Explicit one-marriage-step compositions [pathToken][edgeToken] => result.
     * Only well-defined, unambiguous cases are listed; everything else involving
     * a partner/in-law token returns null (ambiguous step-relations are not
     * inferred).
     */
    const MARRIAGE = [
        'child'   => ['partner' => 'child-in-law'],   // my child's spouse
        'sibling' => ['partner' => 'sibling-in-law'], // my sibling's spouse
        'uncle'   => ['partner' => 'uncle'],          // aunt/uncle by marriage
        'partner' => [
            'parent'  => 'parent-in-law',             // my spouse's parent
            'sibling' => 'sibling-in-law',            // my spouse's sibling
        ],
    ];

    /**
     * Inverse of a directed relation token, or null when the reverse has no
     * representable token.
     */
    public static function tokenComplement(string $token)
    {
        return self::COMPLEMENTS[$token] ?? null;
    }

    /**
     * Compose two directed relations.
     *
     * @param string|null $pathToken relation source -> u (self::SELF or null for the source itself)
     * @param string      $edgeToken relation u -> v
     * @return string|null relation source -> v, or null when it cannot be
     *                     expressed (the traversal should not be extended)
     */
    public static function compose($pathToken, string $edgeToken)
    {
        // First hop from the source: the composed relation is just the edge.
        if ($pathToken === null || $pathToken === self::SELF) {
            return $edgeToken;
        }

        // Marriage / in-law one-step rules take precedence.
        if (isset(self::MARRIAGE[$pathToken][$edgeToken])) {
            return self::MARRIAGE[$pathToken][$edgeToken];
        }

        // Pure blood-line composition via coordinate arithmetic.
        if (isset(self::COORDS[$pathToken], self::COORDS[$edgeToken])) {
            list($a1, $d1) = self::COORDS[$pathToken];
            list($a2, $d2) = self::COORDS[$edgeToken];
            // Walk up a1, down d1 (reach u), then up a2, down d2 (reach v).
            // The middle "down d1 then up a2" retraces the same lineage, so it
            // collapses; the nearest common ancestor of source and v sits
            // max(0, a2 - d1) further up.
            $up   = $a1 + max(0, $a2 - $d1);
            $down = $d2 + max(0, $d1 - $a2);
            return self::coordToToken($up, $down);
        }

        // partner/in-law/social tokens outside the marriage table: not extended.
        return null;
    }

    /**
     * Map a [up, down] coordinate back to a relation token. Valid blood
     * relations with no specific name fall back to the generic RELATIVE token so
     * the person is still surfaced; (0,0) (self) returns null.
     */
    public static function coordToToken(int $up, int $down)
    {
        if ($up === 0 && $down === 0) {
            return null; // self
        }
        // Direct ancestors.
        if ($down === 0) {
            switch ($up) {
                case 1: return 'parent';
                case 2: return 'grandparent';
                case 3: return 'great-grandparent';
                case 4: return 'great-great-grandparent';
                default: return self::RELATIVE;
            }
        }
        // Direct descendants.
        if ($up === 0) {
            switch ($down) {
                case 1: return 'child';
                case 2: return 'grandchild';
                case 3: return 'great-grandchild';
                case 4: return 'great-great-grandchild';
                default: return self::RELATIVE;
            }
        }
        // Siblings.
        if ($up === 1 && $down === 1) {
            return 'sibling';
        }
        // Uncles/aunts line (up the tree, one step down).
        if ($down === 1 && $up >= 2) {
            return $up === 2 ? 'uncle' : ($up === 3 ? 'grand-uncle' : self::RELATIVE);
        }
        // Nephews/nieces line (one step up, down the tree).
        if ($up === 1 && $down >= 2) {
            return $down === 2 ? 'nephew' : ($down === 3 ? 'grand-nephew' : self::RELATIVE);
        }
        // Cousins and everything further out.
        if ($up === 2 && $down === 2) {
            return 'cousin';
        }
        return self::RELATIVE;
    }
}
