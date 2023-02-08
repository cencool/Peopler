<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\widgets\Alert;


Pjax::begin([
	'options' => ['id' => 'item-add',],
	'timeout' => 5000,
]);

echo Alert::widget();

if (isset($itemModel)) {
	$itemForm = ActiveForm::begin([
		'action' => ['item/update'],
		'id' => 'add-item',
		'options' => ['data-pjax' => ''], //needed attribute to include form into pjax
	]);
?>
<?= $itemForm->field($itemModel, 'item') ?>
<?= $itemForm->field($itemModel, 'person_id',['inputOptions' =>['value'=>$person->id]])->hiddenInput() ?>
<?= Html::submitButton('Add Item', ['class' => 'btn btn-primary', 'disabled' => isset($person->id) ? false : true]) ?>
<?php
	ActiveForm::end();
}
Pjax::end();

