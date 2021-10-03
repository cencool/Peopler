<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Db Select';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php
$form = ActiveForm::begin([
	'id' => 'select-db-form',
	'action' => '?r=site/select-db'
]) ?>
<div class='container'>
	<div class='form-group'>
		<div class='row'>
			<div class='col col-xs-2'>
				<?= $form->field($model, 'dbName')->dropDownList($model->dbList); ?>
				<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
			</div>
		</div>
	</div>
</div>

<?php ActiveForm::end() ?>
