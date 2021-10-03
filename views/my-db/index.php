<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['my-db/index']];

?>
<?= Html::a(Yii::t('app', 'New Item'), ['my-db/new-person', 'id' => null], ['class' => 'btn btn-primary']) ?>
<p></p>

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
			'class' => 'yii\grid\SerialColumn',
		],
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
?>
