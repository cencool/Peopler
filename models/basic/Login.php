<?php

namespace app\models\basic;

use yii\base\Model;
use Yii;

class Login extends Model {

	public $IdInput;
	public $PwdInput;

	public function rules() {
		return [
			[['IdInput','PwdInput'],'required'],
		];
	}

	public function attributeLabels() {
		return [
			'IdInput' => Yii::t('app','ID'),
			'PwdInput' => Yii::t('app','Password'),

		];
	}
}
