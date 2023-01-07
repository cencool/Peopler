<?php

namespace app\models\basic;

use yii\web\IdentityInterface;
use yii\db\ActiveRecord;

class User extends ActiveRecord implements IdentityInterface {

    public $authKey = '';


    public static function findIdentity($id) {
        $user = User::find()->where(['user_id' => $id])->one();
        return $user;
    }

    public function getId() {
        return $this->user_id;
    }

    public static function findIdentityByAccessToken($token, $type = null) {
    }

    public function getAuthKey() {
        return $this->authKey;
    }

    public function validateAuthKey($authKey) {
        if (\Yii::$app->getSecurity()->validatePassword($authKey, $this->pwd_hash)) {
            $this->authKey = $authKey; // password has to be stored for auth. check, cookie ?
            return true;
        } else return false;
    }
}
