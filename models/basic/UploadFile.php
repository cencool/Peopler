<?php

namespace app\models\basic;

use yii\base\Model;
use yii\imagine\Image;
use Yii;

class UploadFile extends Model {

	public $imageFile;

	public function rules() {
		return [
			[['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, bmp'],
		];
	}

	public function upload(string $fileName) {
		if ($this->validate()) {

			$tempPath = $this->imageFile->tempName;
			$uploadAlias = '@app/uploads/';
			$baseFileName = substr($fileName, strlen($uploadAlias) - 1);
			$thumbImageDirectory = Yii::getAlias('@app/uploads/thumbnails/');
			$thumbImageFileName = $thumbImageDirectory . $baseFileName;

			$thumbImage = Image::thumbnail($tempPath, 400, null);
			$thumbImage->save($thumbImageFileName);
			$this->imageFile->saveAs($fileName);
			return true;
		} else {
			return false;
		}
	}
}