<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\widgets\Alert;

Pjax::begin([
	'options' => ['id' => 'item-action-list',],
	'timeout' => 5000,
	'enablePushState' => false,
	'enableReplaceState' => false,

]);

echo Alert::widget();
echo GridView::widget([
	'id' => 'item-action-list',
	'dataProvider' => $itemsDataProvider,
	'filterModel' => $itemSearch,
	'filterUrl' => ['item/edit-items','id'=>$person->id],
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
								'pjax' => '',
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
?>
