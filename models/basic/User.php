<?php

namespace app\models\basic;

use yii\web\IdentityInterface;
use Yii;

class User implements IdentityInterface {


	private $_id;

	function __construct($id) {
		$this->_id = $id;
	}

	public static function findIdentity($id) {
		$identities = Yii::$app->params['identities'];
		return array_key_exists($id, $identities) ? new self($id) : null;
	}

	public function getId() {
		return $this->_id;
	}

	public static function findIdentityByAccessToken($token, $type = null) {
	}

	public function getAuthKey() {
		$identities = Yii::$app->params['identities'];
		return $identities[$this->_id];
	}

	public function validateAuthKey($authKey) {
		return $this->getAuthKey() == $authKey;
	}
}