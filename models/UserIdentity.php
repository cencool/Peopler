<?php

namespace app\models;

use yii\base\BaseObject;
use yii\web\IdentityInterface;

class UserIdentity extends BaseObject implements IdentityInterface {

	protected $user;
	protected $password;


	public static function findIdentity($id) {
		$users = \Yii::$app->params['users'];
		return isset($users[$id]) ? new static(['user' => $id, 'password' => $users[$id]]) : null;
	}

	public static function findIdentityByAccessToken($token, $type = null) {
	}

	public  function getId() {
		return $this->user;
	}

	public function getAuthKey() {
		return 'key';
	}

	public function validateAuthKey($authKey) {
		return true;
	}

	public function validatePassword($password) {
		return $this->password === $password;
	}

	public function setUser($user) {
		$this->user = $user;
	}

	public function getUser() {
		return $this->user;
	}

	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function getPassword() {
		return $this->password;
	}

}

