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

			$uploadAlias = '@app/uploads/';
			$baseFileName = substr($fileName, strlen($uploadAlias) - 1);
			$this->uploadThumbnail($baseFileName);
			$this->imageFile->saveAs($fileName);
			return true;
		} else {
			return false;
		}
	}

	public function uploadThumbnail(string $baseFileName) {

		$thumbImageDirectory = Yii::getAlias('@app/uploads/thumbnails/');
		$thumbImageFileName = $thumbImageDirectory . $baseFileName;
		$thumbImage = Image::thumbnail($this->imageFile->tempName, 400, null);
		$thumbImage->save($thumbImageFileName);
	}
}