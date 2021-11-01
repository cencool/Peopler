<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Alert;

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
?>
<?= Html::a(Yii::t('app', 'New Item'), ['person/new-person', 'id' => null], ['class' => 'btn btn-primary']) ?>


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
		],



	],
]);
