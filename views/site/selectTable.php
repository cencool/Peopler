<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->params['dbName'] = $model->dbName;
$this->title = 'Table Select';
$this->params['breadcrumbs'][] = ['label' => 'Select Db', 'url'=>['site/select-db']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php

$form = ActiveForm::begin([
	'id' => 'select-table-form',
	'action' => '?r=site/select-table'
]) ?>
<div class='container'>
	<div class='form-group'>
		<div class='row'>
			<div class='col col-xs-2'>
				<?= $form->field($model, 'tableName')->dropDownList($model->tableList); ?>
				<?= $form->field($model, 'colNames[]')->hiddenInput(['value'=>'*'])->label(false); ?>
				<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
			</div>
		</div>
	</div>
</div>
<?php ActiveForm::end() ?>
