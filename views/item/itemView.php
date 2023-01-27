<?php

use yii\grid\GridView;

echo GridView::widget([
	'dataProvider' => $itemsDataProvider,
	'filterModel' => $itemSearch,
	'columns' => [
		['attribute' => 'item'],

	]
]);
