<?php

namespace app\models\basic;

use yii\db\ActiveRecord;

class PersonPhoto extends ActiveRecord {

    public static function tableName() {
        return 'person_photo';
    }

    public function rules() {
        return [
            [['file_name'], 'safe'],
        ];
    }
}
