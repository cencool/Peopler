<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ActiveTable extends ActiveRecord
{
	public static $tableName;

	public static function tableName()
	{
		return self::$tableName;
	}

	public static function getDb()
	{
		return Yii::$app->dba;
	}

	public static function table($tableName)
	{
		self::$tableName = $tableName;
		return self::class;
	}
}
