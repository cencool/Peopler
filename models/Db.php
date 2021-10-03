<?php

namespace app\models;

use Yii;
use yii\base\Model;

class Db extends Model
{
	public $dbList;
	public $dbName;

	public function rules() {
		return [
			[['dbList', 'dbName'], 'safe'],
		];
	}

	public function attributeLabels() {
		return [
			'dbName' =>'Database Name', 	
		];
	}

	public function dbConnect(String $user, String $password)
	{
		Yii::$app->dba->username = $user;
		Yii::$app->dba->password = $password;
		try {
			Yii::$app->dba->open();
			return true;
		} catch (\yii\db\Exception $e) {
			Yii::error($e->getMessage());
			return false;
		}
	}

	public function getDbList()
	{
		$rows = Yii::$app->dba->createCommand('SHOW DATABASES')->queryAll();
		$this->dbList = [];
		// convert returned rows to format suitable for dropDownList
		foreach ($rows as $value) {
			$this->dbList[$value['Database']] =  $value['Database'];
		}
		return $this->dbList;
	}


	public function selectDb()
	{
		Yii::$app->dba->createCommand('USE ' . $this->dbName)->execute();
	}


}
