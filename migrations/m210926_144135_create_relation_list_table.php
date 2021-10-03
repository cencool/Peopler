<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%relation_list}}`.
 */
class m210926_144135_create_relation_list_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%relation_list}}', [
            'id' => $this->primaryKey(),
			'relation_name' => Schema::TYPE_CHAR.'(20)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%relation_list}}');
    }
}
