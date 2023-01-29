<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

if (isset($itemModel)) {
	$itemForm = ActiveForm::begin();
?>
<?= $itemForm->field($itemModel, 'item') ?>
<?= Html::submitButton('Add Item', ['class' => 'btn btn-primary', 'disabled' => isset($person->id) ? false : true]) ?>
<?php
	ActiveForm::end();
	$a = (new \yii\grid\ActionColumn())->icons['trash'];
}

echo GridView::widget([
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
							'item/delete-item',
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
