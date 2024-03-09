<?php

use yii\db\Migration;
use yii\db\Query;
use yii\db\QueryBuilder;

/**
 * Class m240309_080025_add_access_token
 */
class m240309_080025_add_access_token extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->addColumn('user', 'access_token', 'string unique');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropColumn('user', 'access_token');
        echo "m240309_080025_add_access_token is reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240309_080025_add_access_token cannot be reverted.\n";

        return false;
    }
    */
}
