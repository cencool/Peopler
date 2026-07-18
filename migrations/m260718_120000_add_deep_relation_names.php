<?php

use yii\db\Migration;

/**
 * Adds relation_name vocabulary for the deeper implicit relations produced by
 * KinshipResolver / KinshipCalculus (great-grandparents, great-great-, grand-
 * uncles/nephews) plus a generic `relative` fallback for valid blood relations
 * that have no specific name. Slovak translations live in messages/sk-SK/.
 */
class m260718_120000_add_deep_relation_names extends Migration
{
    private function rows()
    {
        // [gender, relation_name, token]
        return [
            ['m', 'great-grandfather', 'great-grandparent'],
            ['f', 'great-grandmother', 'great-grandparent'],
            ['m', 'great-grandson', 'great-grandchild'],
            ['f', 'great-granddaughter', 'great-grandchild'],
            ['m', 'great-great-grandfather', 'great-great-grandparent'],
            ['f', 'great-great-grandmother', 'great-great-grandparent'],
            ['m', 'great-great-grandson', 'great-great-grandchild'],
            ['f', 'great-great-granddaughter', 'great-great-grandchild'],
            ['m', 'great-uncle', 'grand-uncle'],
            ['f', 'great-aunt', 'grand-uncle'],
            ['m', 'grand-nephew', 'grand-nephew'],
            ['f', 'grand-niece', 'grand-nephew'],
            ['m', 'relative', 'relative'],
            ['f', 'relative', 'relative'],
            ['?', 'relative', 'relative'],
        ];
    }

    public function safeUp()
    {
        foreach ($this->rows() as $row) {
            // Skip if the (gender, relation_name) pair already exists.
            $exists = (new \yii\db\Query())
                ->from('relation_name')
                ->where(['gender' => $row[0], 'relation_name' => $row[1]])
                ->exists();
            if (!$exists) {
                $this->insert('relation_name', [
                    'gender' => $row[0],
                    'relation_name' => $row[1],
                    'token' => $row[2],
                ]);
            }
        }
    }

    public function safeDown()
    {
        foreach ($this->rows() as $row) {
            $this->delete('relation_name', [
                'gender' => $row[0],
                'relation_name' => $row[1],
            ]);
        }
    }
}
