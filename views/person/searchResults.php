<?php

use yii\grid\GridView;
use yii\helpers\Html;

echo '<h1>Search Results</h1>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'content' => function ($model, $key, $index, $coumn) {
                return Html::a('<b>' . $model['id'] . '</b>', ['person/view', 'id' => $model['id']]);
            }
        ],
        ['attribute' => 'name'],
        ['attribute' => 'surname'],
    ]
]);
