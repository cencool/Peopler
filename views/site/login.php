<?php

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin([
	'id' => 'login-form',
]);
?>
<div class='container'>
	<p><?= $message ?></p>
	<div class='row'>
		<div class='col col-xs-3'>
			<div class='form-group'>
				<?= $form->field($model, 'user') ?>
				<?= $form->field($model, 'password')->passwordInput() ?>
				<?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
			</div>
		</div>
	</div>
</div>

<?php ActiveForm::end() ?>
