<?php

namespace app\assets;

use yii\web\AssetBundle;

class CropperAsset  extends AssetBundle
{
    public $sourcePath = '@npm/cropperjs/dist';
    public $js = [
        'cropper.min.js',
    ];
    public $css = [
        'cropper.min.css'
    ];
    public $depends = [
        'app\assets\SiteAsset',
    ];
}
