<?php

use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->params['breadcrumbs'][] = ['label' => $model->surname . ', ' . $model->name];
$this->title = Yii::t('app', 'Person');

?>

<div class='row'>
    <div class='col-sm-4'>
        <?php
        echo DetailView::widget([
            'model' => $model,
            'options' => [
                'class' => 'table table-bordered table-condensed detail-view',
                'style' => 'width:100%;',
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
    <div id='personPhoto' class='col-sm-2' style="border:solid 1px blue;height:9em;overflow:scroll;">
        <?= Html::img(['photo/send-photo', 'personId' => $model->id], ['style' => 'height:100%;width:auto;']) ?>
    </div>
    <div class='col-sm-6'>
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
                    [
                        'attribute' => 'maiden_name',
                        'visible' => $model->gender == 'f' ? true : false
                    ],
                    ['attribute' => 'note'],
                    ['attribute' => 'address'],
                ],

            ]);
        };

        ?>
        <?= Html::a(Yii::t('app', 'Modify'), ['person/update', 'id' => $model->id], ['class' => 'btn btn-primary pull-left']) ?>
        <?= Html::a(Yii::t('app', 'Items') . ' (' . $itemsCount . ')', ['item/view-items', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']) ?>
        <?= Html::a(Yii::t('app', 'Attachments') . ' (' . $attachmentCount . ')', ['attachment/show-attachment', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']) ?>
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
                'attribute' => 'relation',
                'label' => Yii::t('app', 'Relation'),
                'content' => function ($model) {
                    return $model['relation_id'] != -1 ? $model['relation'] : '<code>' . $model['relation'] . '</code>';
                }
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
