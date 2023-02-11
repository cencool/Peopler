<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use app\widgets\Alert;
use app\models\basic\ItemSearch;


Pjax::begin([
	'options' => ['id' => 'item-update-list',],
	'timeout' => 5000,
	'enablePushState' => false,
	'enableReplaceState' => false,

]);

echo Alert::widget();

if (!isset($itemModel)) {

$itemSearchModel = new ItemSearch();
$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $person->id, 10);

echo GridView::widget([
	'id' => 'item-update-list',
	'dataProvider' => $itemsDataProvider,
	'filterModel' => $itemSearchModel,
	'filterUrl' => ['item/update-list','personId'=>$person->id],
	'pager'=> ['linkOptions'=>['data-pjax'=>'']],
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
							'id' => $model->person_id,
						]

					);
				}
				if ($action == 'update') {
					return Url::toRoute(
						[
							'item/edit',
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
								'pjax' => '',
								'params' => [
									'_csrf' => Yii::$app->request->getCsrfToken()
								]
							]
						]
					);
				},

				'update' => function ($url, $model, $key) {
					return Html::a(
						(new \yii\grid\ActionColumn())->icons['pencil'],
						$url,
						[
							'data' => [
								'pjax' => '',
							]
						]
					);
				}

			]
		],
		['attribute' => 'item'],

	]
]);

} else {

	$itemForm = ActiveForm::begin([
		'action' => ['item/update'],
		'id' => 'update-item',
		'options' => ['data-pjax' => ''], //needed attribute to include form into pjax
	]);
?>
<?= $itemForm->field($itemModel, 'item',['options'=>['placeholder'=>$itemModel->item]]) ?>
<?=Html::activeHiddenInput($itemModel, 'person_id',['value'=>$person->id,'label'=>false])?>
<?=Html::activeHiddenInput($itemModel, 'id',['value'=>$itemModel->id,'label'=>false])?>
<?= Html::submitButton('Update Item', ['class' => 'btn btn-primary']) ?>
<?php
	ActiveForm::end();

}


Pjax::end();


