<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\assets\AddRelationAsset;
use app\widgets\Alert;


AddRelationAsset::register($this);


$this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
$this->params['breadcrumbs'][] = ['label' => $person->surname . ', ' . $person->name, 'url' => ['person/update', 'id' => $person->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Add Relation')];
$this->title = Yii::t('app','Add Relation');


echo Alert::widget();

?>

<h3><?= $person->name . ' ' . $person->surname ?> </h3>


<?php
$form = ActiveForm::begin([
	'id' => 'addRelation-form',
]);

?>
<div class='row'>
    <div class='col-sm-3'>
        <?php
		echo $form->field($model, 'relation_ab_id')->dropDownList($relationsList, ['prompt' => Yii::t('app', 'Select')]);
		echo Html::activeHiddenInput($model, 'person_b_id');
		echo Html::activeHiddenInput($model, 'person_a_id', ['value' => strval($person->id)]);
		?>
    </div>
    <div class='col-sm-4'>
        <p><b><?= Yii::t('app', 'To Whom') . ':' ?></b></p>
        <h3 id='selected-name'></h3>
    </div>
</div>


<div class='form-group'>
    <?= Html::submitButton(Yii::t('app', 'Add Relation'), ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>


<?php

echo GridView::widget([
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
			'content' => function ($data) {
				return Html::a('<b>' . $data->id . '</b>', Url::to('#'), ['name' => 'personBid', 'id' => 'personBid-' . $data->id]);
			}
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
			'urlCreator' => function ($action, $model) {
				switch ($action) {
					case 'view':
						return Url::to(['person/view', 'id' => $model->id]);
						break;
					case 'update':
						return Url::to(['person/update', 'id' => $model->id]);
						break;
					case 'delete':
						return Url::to(['person/delete', 'id' => $model->id]);
						break;
				}
			}
		],



	],
]);
