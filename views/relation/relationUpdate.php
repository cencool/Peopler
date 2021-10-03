<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Alert;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['my-db/index']];
$this->params['breadcrumbs'][] = ['label' => $person->surname . ', ' . $person->name, 'url' => ['person/update', 'id' => $person->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Update Relation')];

if (Yii::$app->session->hasFlash('relationUpdated')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-info'],
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

<h3><?= $person->name . ' ' . $person->surname ?> </h3>

<?php
$form = ActiveForm::begin([
	'id' => 'relationUpdate-form',
]);


echo $form->field($model, 'relation_ab')->dropDownList($relationsList, ['prompt' => Yii::t('app', 'Select')]);
echo Html::tag('h3', Yii::t('app','To Whom').': '.$model->person_b->surname . ' ' . $model->person_b->name);
echo $form->field($model, 'person_b_id')->hiddenInput()->label(false);
echo $form->field($model, 'person_a_id')->hiddenInput()->label(false);

?>

<div class='form-group'>
	<?= Html::submitButton('Submit') ?>
</div>

<?php ActiveForm::end(); ?>
