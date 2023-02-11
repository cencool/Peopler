<?php

use yii\grid\GridView;
use yii\widgets\Pjax;

Pjax::begin([
	'options' => ['id' => 'item-view-list',],
	'timeout' => 5000,

]);
echo GridView::widget([
	'dataProvider' => $itemsDataProvider,
	'filterModel' => $itemSearch,
	'columns' => [
		['attribute' => 'item'],

	]
]);

Pjax::end();
