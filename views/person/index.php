<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\widgets\Alert;
use yii\widgets\Pjax;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->title = Yii::t('app', 'List');

echo Alert::widget();

$undeleteButtonClass = Yii::$app->session['undelete'] != null ?   'btn btn-primary' : 'btn btn-primary disabled';

?>
<div class='row'>
	<div class='col-sm-6 text-left'>
		<?= Html::a(Yii::t('app', 'New Record'), ['person/new-person', 'id' => null], ['class' => 'btn btn-primary']) ?>
	</div>
	<div class='col-sm-6 text-right'>
		<?= Html::a(Yii::t('app', 'Undelete'), ['person/undelete'], ['class' => $undeleteButtonClass]) ?>
	</div>
</div>

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
			'attribute' => 'personItem',
			'label' => Yii::t('app', 'Items'),
			'content' => function ($model, $key, $index, $coumn) {
				$count = count($model->personItems);
				return Html::a('<b>' . $count . '</b>', ['item/view-items', 'id' => $model->id]);
			}
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => Yii::t('app', 'Action'),

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



	],
]);

?>
<?php Pjax::end() ?>
