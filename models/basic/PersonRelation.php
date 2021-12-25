<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use Yii;

class PersonRelation extends ActiveRecord {
    public static function tableName() {
        return 'person_relation';
    }

    public function rules() {
        return [
            [['person_a_id', 'person_b_id', 'relation_ab_id'], 'safe'],
            [['person_a_id', 'person_b_id', 'relation_ab_id'], 'required', 'message' => Yii::t('app', 'Second person missing')],
            [['person_a_id', 'person_b_id'], 'filter', 'filter' => 'intval'],
        ];
    }

    public function attributeLabels() {
        return [
            'relation_ab_id' => Yii::t('app', 'Relation'),
        ];
    }


    public function getPerson_a() {
        return $this->hasOne(Person::class, ['id' => 'person_a_id']);
    }

    public function getPerson_b() {
        return $this->hasOne(Person::class, ['id' => 'person_b_id']);
    }

    public function getRelationName() {
        return $this->hasOne(RelationName::class, ['id' => 'relation_ab_id']);
    }
}