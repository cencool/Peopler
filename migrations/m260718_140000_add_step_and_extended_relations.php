<?php

use yii\db\Migration;

/**
 * Adds explicit (user-entered) relation vocabulary: step-family, half-siblings,
 * godchildren, ex-partners, fiance(e)s, adoptive and foster relations.
 *
 * These are NOT auto-computed (they can't be reliably inferred from the graph),
 * so they only need relation_name rows (so they appear in the add-relation
 * dropdown and map to a token) plus relation_pair rows (so their inverse can be
 * displayed on the other person's page). Slovak labels live in messages/sk-SK/.
 *
 * Also backfills the missing godfather/godmother <-> godson/goddaughter pairs and
 * fixes the pre-existing 'granddaugther' typo in relation_pair.
 */
class m260718_140000_add_step_and_extended_relations extends Migration
{
    /** [gender, relation_name, token] */
    private function relationNames()
    {
        return [
            // step-family
            ['m', 'step-father', 'step-parent'],
            ['f', 'step-mother', 'step-parent'],
            ['m', 'step-son', 'step-child'],
            ['f', 'step-daughter', 'step-child'],
            ['m', 'step-brother', 'step-sibling'],
            ['f', 'step-sister', 'step-sibling'],
            // half-siblings (share one biological parent)
            ['m', 'half-brother', 'half-sibling'],
            ['f', 'half-sister', 'half-sibling'],
            // godchildren (godfather/godmother already exist)
            ['m', 'godson', 'godchild'],
            ['f', 'goddaughter', 'godchild'],
            // ex-partners
            ['m', 'ex-husband', 'ex-partner'],
            ['f', 'ex-wife', 'ex-partner'],
            // engaged
            ['m', 'fiance', 'fiance'],
            ['f', 'fiancee', 'fiance'],
            // adoptive
            ['m', 'adoptive-father', 'adoptive-parent'],
            ['f', 'adoptive-mother', 'adoptive-parent'],
            ['m', 'adoptive-son', 'adoptive-child'],
            ['f', 'adoptive-daughter', 'adoptive-child'],
            // foster
            ['m', 'foster-father', 'foster-parent'],
            ['f', 'foster-mother', 'foster-parent'],
            ['m', 'foster-son', 'foster-child'],
            ['f', 'foster-daughter', 'foster-child'],
        ];
    }

    /** [gender_a, relation_ab, gender_b, relation_ba] — mirrors the son/father & brother/sister conventions */
    private function relationPairs()
    {
        return [
            // step-parent / step-child
            ['m', 'step-son', 'm', 'step-father'],
            ['m', 'step-son', 'f', 'step-mother'],
            ['f', 'step-daughter', 'm', 'step-father'],
            ['f', 'step-daughter', 'f', 'step-mother'],
            // step-siblings
            ['m', 'step-brother', 'm', 'step-brother'],
            ['m', 'step-brother', 'f', 'step-sister'],
            ['f', 'step-sister', 'f', 'step-sister'],
            // half-siblings
            ['m', 'half-brother', 'm', 'half-brother'],
            ['m', 'half-brother', 'f', 'half-sister'],
            ['f', 'half-sister', 'f', 'half-sister'],
            // god relations
            ['m', 'godson', 'm', 'godfather'],
            ['m', 'godson', 'f', 'godmother'],
            ['f', 'goddaughter', 'm', 'godfather'],
            ['f', 'goddaughter', 'f', 'godmother'],
            // ex-partners
            ['m', 'ex-husband', 'f', 'ex-wife'],
            // engaged
            ['m', 'fiance', 'f', 'fiancee'],
            // adoptive
            ['m', 'adoptive-son', 'm', 'adoptive-father'],
            ['m', 'adoptive-son', 'f', 'adoptive-mother'],
            ['f', 'adoptive-daughter', 'm', 'adoptive-father'],
            ['f', 'adoptive-daughter', 'f', 'adoptive-mother'],
            // foster
            ['m', 'foster-son', 'm', 'foster-father'],
            ['m', 'foster-son', 'f', 'foster-mother'],
            ['f', 'foster-daughter', 'm', 'foster-father'],
            ['f', 'foster-daughter', 'f', 'foster-mother'],
        ];
    }

    public function safeUp()
    {
        foreach ($this->relationNames() as $r) {
            $exists = (new \yii\db\Query())->from('relation_name')
                ->where(['gender' => $r[0], 'relation_name' => $r[1]])->exists();
            if (!$exists) {
                $this->insert('relation_name', ['gender' => $r[0], 'relation_name' => $r[1], 'token' => $r[2]]);
            }
        }
        foreach ($this->relationPairs() as $p) {
            $exists = (new \yii\db\Query())->from('relation_pair')
                ->where(['gender_a' => $p[0], 'relation_ab' => $p[1], 'gender_b' => $p[2], 'relation_ba' => $p[3]])->exists();
            if (!$exists) {
                $this->insert('relation_pair', [
                    'gender_a' => $p[0], 'relation_ab' => $p[1], 'gender_b' => $p[2], 'relation_ba' => $p[3],
                ]);
            }
        }
        // fix pre-existing typo so granddaughter <-> grandmother resolves
        $this->update('relation_pair', ['relation_ab' => 'granddaughter'], ['relation_ab' => 'granddaugther']);
    }

    public function safeDown()
    {
        foreach ($this->relationPairs() as $p) {
            $this->delete('relation_pair', [
                'gender_a' => $p[0], 'relation_ab' => $p[1], 'gender_b' => $p[2], 'relation_ba' => $p[3],
            ]);
        }
        foreach ($this->relationNames() as $r) {
            $this->delete('relation_name', ['gender' => $r[0], 'relation_name' => $r[1]]);
        }
        // the granddaugther->granddaughter typo fix is intentionally not reverted
    }
}
