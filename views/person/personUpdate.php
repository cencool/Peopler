<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;
use app\widgets\Alert;

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
$this->title = Yii::t('app', 'Edit');

?>

<ul class='nav nav-tabs'>
	<li class='active'><a data-toggle='tab' href='#Basic'><?= Yii::t('app', 'Basic') ?></a></li>
	<li><a data-toggle='tab' href='#Detail'><?= Yii::t('app', 'Details') ?></a></li>
</ul>

<div class='tab-content'>
	<div id='Basic' class='tab-pane fade in active'>

		<?php
		$form = ActiveForm::begin([
			'layout' => 'horizontal',
			'fieldConfig' => [
				'template' => "
        <div class='col-sm-3 text-left'>
        {label}</div><div class='col-sm-8'>{input}</div>\n{error}{hint}",
				'labelOptions' => ['class' => 'control-label'],
			],
		]);
		?>
		<div class='row'>
			<div class='col-xs-3'>
				<p><b><?= Yii::t('app', 'Basic Information') ?></b></p>
			</div>
			<div class=' col-xs-9'>
				<?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn  btn-primary pull-left']) ?>
			</div>
		</div>
		<hr>

		<div class='row'>
			<div class='col-xs-6'>
				<div class='form-group'>

					<?= $form->field($person, 'surname') ?>
					<?= $form->field($person, 'name') ?>
					<?= $form->field($person, 'gender') ?>
					<?= $form->field($person, 'place') ?>
					<?= Html::activeHiddenInput(
						$person,
						'owner',
						['value' => Yii::$app->user->id != $person->owner ? $person->owner : Yii::$app->user->id]
					) ?>
				</div>
			</div>
		</div>
		<hr>

		<?php
		echo Alert::widget();

		$addRelationClass = ($person->id != null) ? 'btn btn-primary pull-left' : 'disabled btn btn-primary pull-left';
		?>
		<div class='row'>
			<div class='col-xs-3'>
				<h4><b><?= Yii::t('app', 'Relations') ?></b></h3>
			</div>
			<div class='col-xs-3'>
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
						'class' => '\yii\grid\ActionColumn',
						'header' => Yii::t('app', 'Action'),
						'template' => '{update} {delete}',
						'visibleButtons' => [
							'update' => function ($model, $key, $index) {
								return    $model['relation_id'] != -1 ? true : false;
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
						},
						'buttons' => [
							'delete' => function ($url, $model, $key) {
								return Html::a(
									( new \yii\grid\ActionColumn())->icons['trash'],
									$url,
									[
										'data' => [
											'confirm' => Yii::t('app', 'Really delete the record ?'),
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
			?>
		</div>
	</div>

	<div id='Detail' class='tab-pane fade'>

		<div class='row'>
			<div class='col-xs-3'>
				<p><b><?= Yii::t('app', 'Detail Information') ?></b></p>
			</div>
			<div class='col-xs-9'>
				<div class='form-group'>
					<?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn  btn-primary pull-left']) ?>
				</div>
			</div>
		</div>
		<hr>
		<div class='row'>
			<div class='col-xs-8'>
				<div class='form-group'>

					<?= $form->field($personDetail, 'marital_status') ?>

					<?php
					if ($person->gender == null || $person->gender === 'f') {
						echo $form->field($personDetail, 'maiden_name');
					}
					?>
					<?= $form->field($personDetail, 'address') ?>
					<?= $form->field($personDetail, 'note')->textarea(['rows' => 4,]) ?>

				</div>


				<?php ActiveForm::end() ?>
			</div>
		</div>
		<?= Html::a(Yii::t('app', 'Attachments') . '(' . $attachmentCount . ')', [
			'attachment/upload',
			'id' => $person->id],
		   	['class' => 'btn btn-primary']) ?>
		<?=Html::tag('hr')?>

		<?= $this->render('//item/itemAdd', [
			'person' => $person
		]); ?>
		<?=Html::tag('hr')?>

		<?= $this->render('//item/itemUpdateList', [
			'person' => $person
		]); ?>



	</div>
</div>
