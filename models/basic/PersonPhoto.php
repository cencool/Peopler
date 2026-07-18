<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use app\models\basic\Person;

class PersonPhoto extends ActiveRecord {

    public static function tableName() {
        return 'person_photo';
    }

    public function rules() {
        return [
            [['file_name'], 'safe'],
        ];
    }
    public static function sendPersonPhoto($personId) {
        $person = Person::findOne($personId);
        if ($person) {
            $personPhoto = PersonPhoto::find()->where(['person_id' => $personId])->one();
            if ($personPhoto !== null && $personPhoto->file_name != 'default') {
                $photoName = $personPhoto->file_name;
                $pathPrefix = \Yii::getAlias('@app/uploads/person_photo/');
                $photoFileName = $pathPrefix . $photoName;
                \Yii::$app->response->sendFile($photoFileName);
            } else {
                $defaultPhoto = \Yii::getAlias('@app/web/') . 'mavatar.jpg';
                \Yii::$app->response->sendFile($defaultPhoto);
            }
        } else {
            $defaultPhoto = \Yii::getAlias('@app/web/') . 'mavatar.jpg';
            \Yii::$app->response->sendFile($defaultPhoto);
        }
    }
}
