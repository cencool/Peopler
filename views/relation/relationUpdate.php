<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Alert;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->params['breadcrumbs'][] = ['label' => $person->surname . ', ' . $person->name, 'url' => ['person/update', 'id' => $person->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Update Relation')];

if (Yii::$app->session->hasFlash('relationUpdated')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-success'],
		'body' => Yii::$app->session->getFlash('relationUpdated'),
	]);
}
if (Yii::$app->session->hasFlash('relationUpdateError')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-danger'],
		'body' => Yii::$app->session->getFlash('relationUpdateError'),
	]);
}
?>

<h3><?= $person->name . ' ' . $person->surname .' '.Yii::t('app','is').' :' ?> </h3>

<?php
$form = ActiveForm::begin([
	'id' => 'relationUpdate-form',
]);


echo $form->field($model, 'relation_ab_id')->dropDownList($relationsList, ['prompt' => Yii::t('app', 'Select')])->label(false);
echo Html::tag('h3', Yii::t('app','To Whom').': '.$model->surname . ' ' . $model->name);

?>

<div class='form-group'>
	<?= Html::submitButton(Yii::t('app','Update'),['class'=>'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
