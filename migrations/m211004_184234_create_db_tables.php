<?php

use yii\db\Migration;

/**
 * Class m211004_184234_create_db_tables
 */
class m211004_184234_create_db_tables extends Migration {
	/**
	 * {@inheritdoc}
	 */

	public function safeUp() {

		if (Yii::$app->db->getTableSchema('person', true) === null) {
			$this->createTable('person', [
				'id' => $this->primaryKey(),
				'surname' => $this->string(20)->notNull(),
				'name' => $this->string(20),
				'place' => $this->string(20),
				'gender' => $this->char(1)->notNull(),
			]);
		}

		if (Yii::$app->db->getTableSchema('person_detail', true) === null) {
			$this->createTable(
				'person_detail',
				[
					'id' => $this->primaryKey(),
					'person_id' => $this->integer()->unique(),
					'marital_status' => $this->char(1)->notNull(),
					'maiden_name' => $this->string(20),
					'note' => 'tinytext',
					'address' => 'tinytext',
				],
			);

			$this->addForeignKey(
				'person_detail_fk',
				'person_detail',
				'person_id',
				'person',
				'id',
				'CASCADE',
				'CASCADE'
			);
		}

		if (Yii::$app->db->getTableSchema('relation_name', true) === null) {
			$this->createTable('relation_name', [
				'id' => $this->primaryKey(),
				'gender' => $this->char(1)->notNull(),
				'relation_name' => $this->string(20)->notNull(),
			]);

			$this->execute(
				'ALTER TABLE `relation_name` ADD CONSTRAINT unique_index UNIQUE (gender, relation_name)'
			);
		}

		if (Yii::$app->db->getTableSchema('person_relation', true) === null) {
			$this->createTable(
				'person_relation',
				[
					'id' => $this->primaryKey(),
					'person_a_id' => $this->integer()->notNull(),
					'relation_ab_id' => $this->integer()->notNull(),
					'person_b_id' => $this->integer()->notNull(),
				],

			);

			$this->execute(
				'ALTER TABLE `person_relation` ADD CONSTRAINT unique_index UNIQUE (person_a_id, relation_ab_id,person_b_id)'
			);

			$this->addForeignKey(
				'person_relation_fk',
				'person_relation',
				'relation_ab_id',
				'relation_name',
				'id',
				'CASCADE',
				'CASCADE'
			);


			$this->addForeignKey(
				'person_relation_fk1',
				'person_relation',
				'person_a_id',
				'person',
				'id',
				'CASCADE',
				'CASCADE',
			);

			$this->addForeignKey(
				'person_relation_fk2',
				'person_relation',
				'person_b_id',
				'person',
				'id',
				'CASCADE',
				'CASCADE',
			);

			$this->createIndex(
				'personB',
				'person_relation',
				'person_b_id'
			);
		}

		if (Yii::$app->db->getTableSchema('relation_pair', true) === null) {
			$this->createTable(
				'relation_pair',
				[
					'id' => $this->primaryKey(),
					'gender_a' => $this->char(1)->notNull(),
					'relation_ab' => $this->string(20)->notNull(),
					'gender_b' => $this->char(1)->notNull(),
					'relation_ba' => $this->string(20)->notNull(),
				],
			);

			$this->execute(
				'ALTER TABLE `relation_pair` ADD CONSTRAINT unique_index UNIQUE (gender_a, relation_ab, gender_b, relation_ba)'
			);



			$this->batchInsert(
				'relation_pair',
				['gender_a', 'relation_ab', 'gender_b', 'relation_ba'],
				[
					['f', 'classmate', 'f', 'classmate'],
					['f', 'daughter', 'f', 'mother'],
					['f', 'daughter', 'm', 'father'],
					['f', 'granddaughter', 'm', 'grandfather'],
					['f', 'granddaugther', 'f', 'grandmother'],
					['f', 'mother', 'f', 'daughter'],
					['m', 'acquaintance', 'f', 'acquaintance'],
					['m', 'acquaintance', 'm', 'acquaintance'],
					['f', 'acquaintance', 'f', 'acquaintance'],
					['m', 'brother', 'f', 'sister'],
					['m', 'brother', 'm', 'brother'],
					['m', 'classmate', 'f', 'classmate'],
					['m', 'classmate', 'm', 'classmate'],
					['m', 'colleague', 'f', 'colleague'],
					['m', 'colleague', 'm', 'colleague'],
					['m', 'cousin', 'f', 'cousin'],
					['m', 'cousin', 'm', 'cousin'],
					['m', 'friend', 'f', 'friend'],
					['m', 'friend', 'm', 'friend'],
					['m', 'grandson', 'f', 'grandmother'],
					['m', 'grandson', 'm', 'grandfather'],
					['m', 'husband', 'f', 'wife'],
					['m', 'son', 'f', 'mother'],
					['m', 'son', 'm', 'father'],
					['m', 'son-in-law', 'f', 'mother-in-law'],
					['m', 'son-in-law', 'm', 'father-in-law'],
					['f', 'daughter-in-law', 'm', 'father-in-law'],
					['f', 'daughter-in-law', 'f', 'mother-in-law'],
					['m', 'brother-in-law', 'm', 'brother-in-law'],
					['m', 'brother-in-law', 'f', 'sister-in-law'],
					['f', 'siter-in-law', 'f', 'sister-in-law'],
					['f', 'niece', 'm', 'uncle'],
					['f', 'niece', 'f', 'aunt'],
					['m', 'nephew', 'm', 'uncle'],
					['m', 'nephew', 'f', 'aunt'],
				]

			);
		}

		if (Yii::$app->db->getTableSchema('person_attachment', true) === null) {
			$this->createTable('person_attachment', [
				'id' => $this->primaryKey(),
				'person_id' => $this->integer()->notNull(),
				'file_caption' => $this->string(),
				'file_name' => $this->string(100)->notNull(),
			]);

			$this->addForeignKey(
				'person_file_fk1',
				'person_attachment',
				'person_id',
				'person',
				'id',
				'CASCADE',
				'CASCADE',
			);
		}

		if (Yii::$app->db->getTableSchema('relation_name', true) === null) {
		$this->batchInsert(
			'relation_name',
			['gender', 'relation_name'],
			[
				['f', 'acquaintance'],
				['f', 'aunt'],
				['f', 'classmate'],
				['f', 'colleague'],
				['f', 'daughter'],
				['f', 'daughter-in-law'],
				['f', 'friend'],
				['f', 'godmother'],
				['f', 'granddaughter'],
				['f', 'grandmother'],
				['f', 'mother'],
				['f', 'niece'],
				['f', 'sister'],
				['f', 'sister-in-law'],
				['f', 'wife'],
				['f', 'mother-in-law'],
				['m', 'acquaintance'],
				['m', 'brother'],
				['m', 'brother-in-law'],
				['m', 'classmate'],
				['m', 'colleague'],
				['m', 'cousin'],
				['m', 'father'],
				['m', 'father-in-law'],
				['m', 'friend'],
				['m', 'godfather'],
				['m', 'grandfather'],
				['m', 'grandson'],
				['m', 'husband'],
				['m', 'nephew'],
				['m', 'son'],
				['m', 'son-in-law'],
				['m', 'uncle'],
			]
			);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function safeDown() {
		//echo "m211004_184234_create_db_tables cannot be reverted.\n";
		if (Yii::$app->db->getTableSchema('person_relation', true)) {
			$this->dropTable('person_relation');
		}
		if (Yii::$app->db->getTableSchema('person_detail', true)) {
			$this->dropTable('person_detail');
		}
		if (Yii::$app->db->getTableSchema('relation_pair', true)) {
			$this->dropTable('relation_pair');
		}
		if (Yii::$app->db->getTableSchema('relation_name', true)) {
			$this->dropTable('relation_name');
		}
		if (Yii::$app->db->getTableSchema('person', true)) {
			$this->dropTable('person');
		}

		return true;
	}

	/* // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211004_184234_create_db_tables cannot be reverted.\n";

        return false;
    }
 */
}
