<?php


use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

?>
<?php Pjax::begin([
	'id' => 'person-list',
	'timeout' => 5000,
]) ?>
<?= GridView::widget([
	'dataProvider' => $provider,
	'id' => 'indexView',
	'filterModel' => $searchModel,
	'headerRowOptions' => [
		'style' => 'background-color:yellow;',
	],
	'tableOptions' => [
		'class' => 'table table-hover table-condensed table-bordered',
	],
	'columns' => [
		[
			'attribute' => 'id',
		],
		[
			'attribute' => 'surname',
			'label' => Yii::t('app', 'Surname'),
		],
		[
			'attribute' => 'name',
			'label' => Yii::t('app', 'Name'),
		],
		[
			'attribute' => 'place',
			'label' => Yii::t('app', 'Place')
		],
		[
			'attribute' => 'gender',
			'label' => Yii::t('app', 'Gender'),
			'filterInputOptions' => ['class' => 'form-control', 'size' => 1],
		],
		[
			'attribute' => 'personAttachment',
			'label' => Yii::t('app', 'Attachments'),
			'content' => function ($model, $key, $index, $coumn) {
				$count = count($model->personAttachments);
				return Html::a('<b>' . $count . '</b>', ['attachment/show-attachment', 'id' => $model->id]);
			}
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => Yii::t('app', 'Action'),

			'buttons' => [
				'delete' => function ($url, $model, $key) {
					return Html::a((new \yii\grid\ActionColumn())->icons['trash'],
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



	],
]);
Pjax::end();
