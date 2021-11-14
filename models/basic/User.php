<?php

namespace app\models\basic;

use yii\web\IdentityInterface;

class User implements IdentityInterface {

	public static $identities = [
		'milhar' => 'aaaaaa',
		'demo' => 'demo',
	];

	private $_id;

	function __construct($id) {
		$this->_id = $id;
	}

	public static function findIdentity($id) {
		return array_key_exists($id, self::$identities) ? new self($id) : null;
	}

	public function getId() {
		return $this->_id;
	}

	public static function findIdentityByAccessToken($token, $type = null) {
	}

	public function getAuthKey() {
		return self::$identities[$this->_id];
	}

	public function validateAuthKey($authKey) {
		return $this->getAuthKey() == $authKey;
	}
}
