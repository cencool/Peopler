<?php

namespace app\models\basic;

use yii\db\ActiveRecord;

class PersonAttachment extends ActiveRecord {

	public static function tableName() {
	return 'person_attachment';
}

public function rules() {
	return [
		[['file_caption','file_name'],'safe'],
	];
}
}
