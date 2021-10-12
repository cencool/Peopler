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

		$this->createTable('person', [
			'id' => $this->primaryKey(),
			'surname' => $this->string(20)->notNull(),
			'name' => $this->string(20),
			'place' => $this->string(20),
			'gender' => $this->char(1)->notNull(),
		]);

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

		$this->createTable(
			'person_relation',
			[
				'id' => $this->primaryKey(),
				'person_a_id' => $this->integer()->notNull(),
				'relation_ab' => $this->string(20)->notNull(),
				'person_b_id' => $this->integer()->notNull(),
			],

		);
		$this->execute(
			'ALTER TABLE `person_relation` ADD CONSTRAINT unique_index UNIQUE (person_a_id, relation_ab, person_b_id)'
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
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown() {
		echo "m211004_184234_create_db_tables cannot be reverted.\n";

		$this->dropTable('person_detail');
		$this->dropTable('person_relation');
		$this->dropTable('person');
		$this->dropTable('relation_pair');


	} /* // Use up()/down() to run migration code without a transaction.
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
