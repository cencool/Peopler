<?php

use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->params['breadcrumbs'][] = ['label' => $model->surname . ', ' . $model->name];

?>

<div class='row'>
    <div class='col-xs-6'>
        <?php
        echo DetailView::widget([
            'model' => $model,
            'options' => [
                'class' => 'table table-bordered table-condensed detail-view',
                'style' => 'width:50%;',
            ],
            'attributes' => [
                [
                    'attribute' => 'surname',
                    'label' => Yii::t('app', 'Surname'),
                    'captionOptions' => ['style' => 'width:20%;'],
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Name'),
                ],
                [
                    'attribute' => 'gender',
                    'label' => Yii::t('app', 'Gender'),
                ],
                [
                    'attribute' => 'place',
                    'label' => Yii::t('app', 'Place'),
                ],

            ],

        ]);
        ?>
    </div>
    <div class='col-xs-6'>
        <?php

        if ($model->detail) {

            echo DetailView::widget([
                'model' => $model->detail,
                'options' => [
                 'class' => 'table table-bordered table-condensed detail-view',
                    'style' => 'width:100%;',
                ],
                'attributes' => [
                    [
                        'attribute' => 'marital_status',
                        'captionOptions' => ['style' => 'width:20%;']
                    ],
                    ['attribute' => 'note'],
                    ['attribute' => 'address'],
                ],

            ]);
        };

        ?>

    </div>
</div>

<h4><b><?= Yii::t('app', 'Relations') ?></b></h3>

    <?php

    echo GridView::widget([
        'dataProvider' => $provider,
        'filterModel' => $searchModel,
        'tableOptions' => [
            'class' => 'table table-hover table-condensed table-bordered',
        ],
        'columns' => [
            [
                'attribute' => 'relation_id',
                'label' => 'ID'
            ],
            [
                'attribute' => 'relation',
                'label' => Yii::t('app', 'Relation')
            ],
            [
                'attribute' => 'relation_to_whom',
                'label' => Yii::t('app', 'To Whom'),
                'content' => function ($model) {
                    return Html::a($model['relation_to_whom'], ['person/view', 'id' => $model['to_whom_id']]);
                }
            ],
        ]
    ]);
