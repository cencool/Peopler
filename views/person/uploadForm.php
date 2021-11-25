<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;


?>

<?php

if (Yii::$app->session->hasFlash('uploadSuccess')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-success'],
		'body' => Yii::$app->session->getFlash('uploadSuccess'),
	]);
}

if (Yii::$app->session->hasFlash('uploadError')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-danger'],
		'body' => Yii::$app->session->getFlash('uploadError'),
	]);
}

?>

<?php $form = ActiveForm::begin([
	'options' => ['autocomplete' => 'off','class'=>'form-inline'],
]) ?>

<?= $form->field($personFile, 'file_caption')->textInput() ?>
		<?= $form->field($uploadModel, 'imageFile')->fileInput() ?>
		<button type='submit' class='btn btn-primary pull-right'>Submit</button>
<p></p>
<?php ActiveForm::end() ?>

<?= $this->render('attachmentView', ['fileGallery' => $fileGallery, 'pages'=>$pages]) ?>
