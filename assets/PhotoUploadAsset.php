<?php

namespace app\assets;

use yii\web\AssetBundle;

class PhotoUploadAsset  extends AssetBundle {
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [];
	public $js = [
		'js/photoUpload.js',
	];
	public $depends = [
		'app\assets\CropperAsset',
	];
}
