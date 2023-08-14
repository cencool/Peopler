<?php

namespace app\models\basic;

use yii\base\Model;
use Yii;

class PhotoUpload extends Model {

    public $imageFile;

    public function rules() {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions'=>'png,jpg,jpeg,bmp,svg' ],
        ];
    }

    public function upload($fileName) {
        if ($this->validate()) {

            $uploadDirAlias = '@app/uploads/person_photo/';
            $uploadDir = Yii::getAlias($uploadDirAlias);
            if (!file_exists($uploadDir) || !is_dir($uploadDir)) {
                mkdir($uploadDir);
            }
            $this->imageFile->saveAs($uploadDir . $fileName);
            return true;
        } else {
            return false;
        }
    }
}
