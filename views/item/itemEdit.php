<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use app\widgets\Alert;
use app\models\basicPerson;
use app\models\basic\Items;


Pjax::begin([
	'options' => ['id' => 'item-edit',],
	'timeout' => 5000,
	'enablePushState' => false,
	'enableReplaceState' => false,
]);

echo Alert::widget();

$itemModel = new Items();

	$itemForm = ActiveForm::begin([
		'action' => ['item/add'],
		'id' => 'add-item',
		'options' => ['data-pjax' => ''], //needed attribute to include form into pjax
	]);
?>
<?= $itemForm->field($itemModel, 'item',['options'=>['placeholder'=>'']]) ?>
<?=Html::activeHiddenInput($itemModel, 'person_id',['value'=>$person->id,'label'=>false])?>
<?= Html::submitButton('Add Item', ['class' => 'btn btn-primary', 'disabled' => isset($person->id) ? false : true]) ?>
<?php
	ActiveForm::end();
Pjax::end();
