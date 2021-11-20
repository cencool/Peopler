<?php

namespace app\models\basic;

use yii\db\ActiveRecord;

class PersonFile extends ActiveRecord {

	public static function tableName() {
	return 'person_file';
}
}
