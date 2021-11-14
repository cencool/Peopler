<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
	'options' => ['class' => 'form-horizontal'],
	'action' => ['site/login'],
])
?>


<div class='row'>
	<div class='col-xs-3'>
		<?php if ($required) { ?>
			<div class='alert alert-info text-center'>
				You have to login !
			</div>
		<?php } ?>
		<?= $form->field($model, 'IdInput') ?>
		<?= $form->field($model, 'PwdInput')->passwordInput() ?>

		<div class='form-group'>
			<?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary']) ?>

		</div>
	</div>
</div>

<?php ActiveForm::end() ?>
