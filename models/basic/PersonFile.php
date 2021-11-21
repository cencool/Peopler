<?php

namespace app\models\basic;

use yii\db\ActiveRecord;

class PersonFile extends ActiveRecord {

	public static function tableName() {
	return 'person_file';
}

public function rules() {
	return [
		[['file_caption','file_name'],'safe'],
	];
}
}
