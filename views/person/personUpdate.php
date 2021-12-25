<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\bootstrap\Alert;

/** 
 *
 * @var $this yii\web\view
 * @var $person app\models\Person
 * @var $personDetail app\models\PersonDetail
 * @var $searchModel app\models\RelationSearch
 * @var $provider yii\data\ArrayDataProvider 
 * 
 */


$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->params['breadcrumbs'][] = ['label' => $person->surname . ', ' . $person->name];

if (Yii::$app->session->hasFlash('personUpdated')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-success'],
		'body' => Yii::$app->session->getFlash('personUpdated'),
	]);
}
if (Yii::$app->session->hasFlash('personAdded')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-success'],
		'body' => Yii::$app->session->getFlash('personAdded'),
	]);
}

?>


<div class='row'>
    <?php

	$form = ActiveForm::begin([
		'layout' => 'horizontal',
		'fieldConfig' => [
			'template' => "
        <div class='col-sm-3 text-right'>
        {label}</div><div class='col-sm-8'>{input}</div>\n{error}{hint}",
			'labelOptions' => ['class' => 'control-label'],
		],
	]);
	?>
    <div class='col-xs-4'>
        <p><b><?= Yii::t('app', 'Basic Information') ?></b></p>
        <hr>

        <div class='form-group'>

            <?= $form->field($person, 'surname') ?>
            <?= $form->field($person, 'name') ?>
            <?= $form->field($person, 'gender') ?>
            <?= $form->field($person, 'place') ?>
        </div>



    </div>
    <div class='col-xs-8'>
        <div class='row'>
            <div class='col-xs-10'>
                <p><b><?= Yii::t('app', 'Detail Information') ?></b></p>
                <hr>
            </div>
            <div class='col-xs-2'>
                <div class='form-group'>
                    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn  btn-primary pull-right']) ?>
                </div>
            </div>
        </div>

        <div class='form-group'>

            <?= $form->field($personDetail, 'marital_status') ?>

            <?php
			if ($person->gender == null || $person->gender === 'f') {
				echo $form->field($personDetail, 'maiden_name');
			}
			?>
            <?= $form->field($personDetail, 'address') ?>
            <?= $form->field($personDetail, 'note')->textarea(['rows' => 4,]) ?>

            <?= Html::a(Yii::t('app', 'Attachments') . '(' . $attachmentCount . ')', ['person/upload', 'id' => $person->id], ['class' => 'btn btn-primary pull-right']) ?>
        </div>


        <?php ActiveForm::end() ?>
    </div>
</div>

<?php
if (Yii::$app->session->hasFlash('relationDeleted')) {
	echo Alert::widget([
		'options' => ['class' => 'alert-success'],
		'body' => Yii::$app->session->getFlash('relationDeleted'),
	]);
}

$addRelationClass = ($person->id != null) ? 'btn btn-primary pull-left' : 'disabled btn btn-primary pull-left';
?>
<div class='row'>
    <div class='col-xs-2'>
        <h4><b><?= Yii::t('app', 'Relations') ?></b></h3>
    </div>
    <div class='col-xs-2'>
        <?= Html::a(Yii::t('app', 'Add Relation'), ['relation/add-relation', 'id' => $person->id], ['class' => $addRelationClass])  ?>
    </div>
</div>
<div class='row'>
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
				'label' => Yii::t('app', 'Relation ID'),
			],
			[
				'class' => '\yii\grid\ActionColumn',
				'template' => '{update} {delete}',
				'visibleButtons' => [
					'update' => function ($model, $key, $index) {
						return	$model['relation_id'] != -1 ? true : false;
					},
					'delete' => function ($model, $key, $index) {
						return $model['relation_id'] != -1 ? true : false;
					}
				],
				'urlCreator' => function ($action, $model) {
					if ($action == 'update') {
						return Url::toRoute(
							[
								'relation/update',
								'id' => Yii::$app->request->get('id'),
								'relation_id' => $model['relation_id'],
							]
						);
					}
					if ($action == 'delete') {
						return Url::toRoute(
							[
								'relation/delete',
								'id' => Yii::$app->request->get('id'),
								'relation_id' => $model['relation_id'],
							]
						);
					}
				}
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
	?>
</div>