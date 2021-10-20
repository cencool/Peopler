<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use Yii;

class PersonDetail extends ActiveRecord {

    public function rules()
    {
        return [
            [['marital_status', 'maidenName','note','address'],'safe'],
            ['marital_status','string','max'=>1],
            ['marital_status', 'match', 'pattern' => '@\bm\b|\bs\b|\bd\b|\?@', 'message' => Yii::t('app', 'Unknown status')],
        ];
    }
    public function attributeLabels()
    {
        return [
            'marital_status' => Yii::t('app', 'Marital Status'),
            'maiden_name' => Yii::t('app', 'Maiden Name'),
            'note' => Yii::t('app','Note'),
            'address' => Yii::t('app', 'Address'),
        ];
    }

    public static function tableName() {
        return 'person_detail';
    }

    public function getPerson(){
        return $this->hasOne(Person::class,['id' => 'person_id']);
    }
}
