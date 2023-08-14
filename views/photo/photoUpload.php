<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\assets\PhotoUploadAsset;
use Yii;

PhotoUploadAsset::register($this);
$this->registerJsVar('_csrf', Yii::$app->request->getCsrfToken());

$form = ActiveForm::begin();
?>

<?= $form->field($photoUpload, 'imageFile')->fileInput() ?>

<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>

<div id='forMessage' class='alert alert-info alert-dismissible' hidden role='alert'>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <p id='message'></p>
</div>
<div class='row'>
    <div class='col-sm-6'><img id='cropPreview' src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" style='max-width:200px; max-height:200px;width:auto; height:auto;display:block;'>
    </div>
    <div class='col-sm-2'><button class='btn btn-primary' id='uploadBtn'>Upload</button></div>
</div>

<input type='file' id='photoFile' />
<input type="button" id='resetBtn' value='Reset Cropper'>

<div> <?= Html::img("data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==", ['id' => 'image', 'style' => 'display:block; max-width:100%']) ?></div>
