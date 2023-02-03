<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\widgets\Alert;


Pjax::begin([
	'options' => ['id' => 'item-add',],
	'timeout' => 5000
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

echo GridView::widget([
	'id' => 'item-list',
	'dataProvider' => $itemsDataProvider,
	'filterModel' => $itemSearch,
	'columns' => [
		[
			'class' => '\yii\grid\ActionColumn',
			'header' => Yii::t('app', 'Action'),
			'template' => '{update} {delete}',
			'urlCreator' => function ($action, $model) {
				if ($action == 'delete') {
					return Url::toRoute(
						[
							'item/delete',
							'itemId' => $model->id,
							'personId' => $model->person_id,
						]

					);
				}
				if ($action == 'update') {
					return Url::toRoute(
						[
							'item/update-item',
							'itemId' => $model->id,
							'personId' => $model->person_id,
						]

					);
				}
			},
			'buttons' => [
				'delete' => function ($url, $model, $key) {
					return Html::a(
						(new \yii\grid\ActionColumn())->icons['trash'],
						$url,
						[
							'data' => [
								'confirm' => Yii::t('app', 'Really delete the record ?'),
								'method' => 'post',
								'params' => [
									'_csrf' => Yii::$app->request->getCsrfToken()
								]
							]
						]
					);
				}

			]
		],
		['attribute' => 'item'],

	]
]);
Pjax::end();
