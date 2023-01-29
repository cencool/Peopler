<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\widgets\Alert;

$form = ActiveForm::begin([
	'options' => ['class' => 'form-horizontal'],
	'action' => ['site/login'],
]);
$this->title = Yii::t('app', 'Login');
?>

<div class='row'>
	<?php if ($required) { ?>
		<div class='alert alert-info text-center'>
			<?= Yii::t('app', 'You have to login first!'); ?>
		</div>
	<?php } ?>
	<?php
	echo Alert::widget()
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
