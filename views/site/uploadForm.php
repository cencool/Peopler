<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;


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

<?php $form = ActiveForm::begin() ?>

<?= $form->field($model, 'imageFile')->fileInput() ?>
<button type='submit' class='btn btn-primary'>Submit</button>

<?php ActiveForm::end() ?>
