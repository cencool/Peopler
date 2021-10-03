<?php

namespace app\models\myDb;

use yii\db\ActiveRecord;
use Yii;

class PersonRelation extends ActiveRecord
{
    public static function tableName()
    {
        return 'person_relation';
    }

    public function rules()
    {
        return [
            [['person_a_id', 'person_b_id', 'relation_ab'], 'safe'],
            [['person_a_id', 'person_b_id', 'relation_ab'], 'required'],
            [['person_a_id', 'person_b_id'], 'filter','filter'=>'intval'],
        ];
    }

	public function attributeLabels() {
		return [
			'relation_ab' => Yii::t('app','Relation'),
		];
	}


    public function getPerson_a()
    {
        return $this->hasOne(Person::class, ['id' => 'person_a_id']);
    }

    public function getPerson_b()
    {
        return $this->hasOne(Person::class, ['id' => 'person_b_id']);
    }
}
