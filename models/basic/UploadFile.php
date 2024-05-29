<?php

namespace app\models\basic;

use yii\base\Model;
use yii\imagine\Image;
use Yii;

class UploadFile extends Model {

	public $imageFile;

	public function rules() {
		return [
			[['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, bmp,jpeg'],
		];
	}

	public function upload(string $fileName): bool {
		if ($this->validate()) {

			$uploadAlias = '@app/uploads/';
			$uploadDir = Yii::getAlias($uploadAlias);
			if (!file_exists($uploadDir) || !is_dir($uploadDir)) {
				mkdir($uploadDir);
			}
			$baseFileName = substr($fileName, strlen($uploadAlias) - 1);
			$this->uploadThumbnail($baseFileName);
			$this->imageFile->saveAs($fileName);
			return true;
		} else {
			return false;
		}
	}

	public function replace(string $oldFileName, string $fileName): bool {
		if ($this->validate()) {
			$uploadAlias = '@app/uploads/';
			$uploadDir = Yii::getAlias($uploadAlias);
			if (!file_exists($uploadDir) || !is_dir($uploadDir)) {
				mkdir($uploadDir);
			}
			$oldBaseFileName = substr($oldFileName, strlen($uploadAlias));
			$this->deleteThumbnail($oldBaseFileName);
			if (file_exists($oldFileName)) {
				unlink($oldFileName);
			}
			$newBaseFileName = substr($fileName, strlen($uploadAlias));
			$this->uploadThumbnail($newBaseFileName);
			$this->imageFile->saveAs($fileName);
			return true;
		} else {
			return false;
		}
	}

	public function uploadThumbnail(string $baseFileName) {

		$thumbAlias = '@app/uploads/thumbnails/';
		$thumbImageDirectory = Yii::getAlias($thumbAlias);
		if (!file_exists($thumbImageDirectory) || !is_dir($thumbImageDirectory)) {
			mkdir($thumbImageDirectory);
		}
		$thumbImageFileName = $thumbImageDirectory . $baseFileName;
		$thumbImage = Image::thumbnail($this->imageFile->tempName, 400, null);
		$thumbImage->save($thumbImageFileName);
	}

	private function deleteThumbnail(string $baseFileName) {
		$thumbAlias = '@app/uploads/thumbnails/';
		$thumbImageDirectory = Yii::getAlias($thumbAlias);
		if (!file_exists($thumbImageDirectory) || !is_dir($thumbImageDirectory)) {
			mkdir($thumbImageDirectory);
		}
		$thumbImageFileName = $thumbImageDirectory . $baseFileName;
		if (file_exists($thumbImageFileName)) {
			unlink($thumbImageFileName);
		}
	}
}
