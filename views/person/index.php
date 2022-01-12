<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Alert;
use yii\helpers\BaseHtml;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];

if (Yii::$app->session->hasFlash('personDeleted')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-info'],
		'body' => Yii::$app->session->getFlash('personDeleted'),
	]);
}
if (Yii::$app->session->hasFlash('personDeleteError')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-danger'],
		'body' => Yii::$app->session->getFlash('personDeleteError'),
	]);
}

$undeleteButtonClass = Yii::$app->session['undelete'] != null ?   'btn btn-primary' : 'btn btn-primary disabled';

?>
<div class='row'>
    <div class='col-sm-6 text-left'>
        <?= Html::a(Yii::t('app', 'New Item'), ['person/new-person', 'id' => null], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class='col-sm-6 text-right'>
        <?= Html::a(Yii::t('app', 'Undelete'), ['person/undelete'], ['class' => $undeleteButtonClass]) ?>
    </div>
</div>


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
			'attribute' => 'name',
			'label' => Yii::t('app', 'Name'),
		],
		[
			'attribute' => 'surname',
			'label' => Yii::t('app', 'Surname'),
		],
		[
			'attribute' => 'place',
			'label' => Yii::t('app', 'Place')
		],
		[
			'attribute' => 'gender',
			'label' => Yii::t('app', 'Gender'),
		],
		[
			'class' => 'yii\grid\ActionColumn',
			'header' => Yii::t('app', 'Action'),

			'buttons' => [
				'delete' => function ($url, $model, $key) {
					return Html::a(
						"<span class='glyphicon glyphicon-trash'></span>",
						$url,
						[
							'data' => [
								'confirm' => 'Are you sure ?',
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