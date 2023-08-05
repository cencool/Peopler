<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%photo}}`.
 */
class m230714_084331_create_photo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		if (Yii::$app->db->getTableSchema('person_photo', true) === null) {
			$this->createTable('person_photo', [
				'id' => $this->primaryKey(),
				'person_id' => $this->integer()->notNull(),
				'file_name' => $this->string(100)->notNull(),
			]);

			$this->addForeignKey(
				'person_photo_fk1',
				'person_photo',
				'person_id',
				'person',
				'id',
				'CASCADE',
				'CASCADE',
			);
		}
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		if (Yii::$app->db->getTableSchema('person_photo', true)) {
			$this->dropTable('person_photo');
		}
    }
}
