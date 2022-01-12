<?php

namespace app\models\basic;

use yii\web\IdentityInterface;
use yii\db\ActiveRecord;
use Yii;

class User extends ActiveRecord implements IdentityInterface {


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
		return $this->password;
	}

	public function validateAuthKey($authKey) {
		return $this->getAuthKey() == $authKey;
	}
}