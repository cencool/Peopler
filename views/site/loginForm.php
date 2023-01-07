<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;

$form = ActiveForm::begin([
	'options' => ['class' => 'form-horizontal'],
	'action' => ['site/login'],
]);
$this->title = Yii::t('app','Login');
?>

<div class='row'>
    <?php if ($required) { ?>
    <div class='alert alert-info text-center'>
        <?= Yii::t('app', 'You have to login first!'); ?>
    </div>
    <?php } ?>
    <?php
	if (Yii::$app->session->hasFlash('loginIncorrect')) {
		echo Alert::widget([
			'options' => ['class' => 'alert-danger'],
			'body' => Yii::$app->session->getFlash('loginIncorrect'),
		]);
	}
	?>
</div>
<div class='row'>
    <div class='col-sm-3'>
        <?= $form->field($model, 'IdInput') ?>
        <?= $form->field($model, 'PwdInput')->passwordInput() ?>

        <div class='form-group'>
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary']) ?>

        </div>
    </div>
</div>

<?php ActiveForm::end() ?>
