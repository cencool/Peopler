<?php

namespace app\models\basic;

use yii\base\Model;

class UploadFile extends Model {

	public $imageFile;

	public function rules() {
		return [
			[['imageFile'],'file','skipOnEmpty' => false, 'extensions' => 'png, jpg, bmp'],
		];
	}

	public function upload(string $fileName) {
		if ($this->validate()) {
			$this->imageFile->saveAs($fileName);
			return true;
		} else {
			return false;
		}
	}
}
