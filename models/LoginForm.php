<?php


namespace app\models;

use Yii;
use yii\base\Model;
use app\models\UserIdentity;

class LoginForm extends Model {

	public $user;
	public $password;
	private $_identity;

	public function rules() {
		return [
			[['user', 'password'], 'required'],
			['password', 'validatePassword']
		];
	}

	public function login() {
		if($this->validate()) {
			return Yii::$app->user->login($this->_identity,60);
		}
		return false;
	}

	public function validatePassword($attribute ) {
		if (!$this->hasErrors()) {
			$this->_identity = UserIdentity::findIdentity($this->user);
			if ( !$this->_identity || !$this->_identity->validatePassword($this->password)) {
				$this->addError($attribute, 'Wrong username or password!');
			}
		}

	}

}



