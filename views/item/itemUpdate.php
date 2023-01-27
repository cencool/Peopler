<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

if (isset($itemModel)) {
	$itemForm = ActiveForm::begin();
?>
<?= $itemForm->field($itemModel, 'item') ?>
<?= Html::submitButton('Add Item', ['class' => 'btn btn-primary']) ?>
<?php
	ActiveForm::end();
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
						'<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"/></svg>',
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
