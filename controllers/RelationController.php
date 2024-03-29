<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\RelationUpdate;
use app\models\basic\PersonRelation;
use app\models\basic\RelationName;
use app\models\basic\RelationPair;
use yii\filters\AccessControl;
use Yii;
use yii\web\NotFoundHttpException;

class RelationController extends Controller {

	public function behaviors() {

		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					]

				]
			]
		];
	}
	public function beforeAction($action) {

		$session = Yii::$app->session;
		$request = Yii::$app->request;
		if ($request->get('lng') == 'sk') {
			Yii::$app->language = 'sk-SK';
			$session['language'] = 'sk-SK';
		} else if ($request->get('lng') == 'en') {
			Yii::$app->language = 'en-US';
			$session['language'] = 'en-US';
		} else if ($session['language']) {
			Yii::$app->language = $session['language'];
		} else {
			Yii::$app->language = 'en-US';
		}

		if (!parent::beforeAction($action)) {
			return false;
		}
		return true;
	}


	public function actionAddRelation($id) {

		$person = Person::findOne($id);
		if (!$person) {
			throw new NotFoundHttpException();
		}

		$session = Yii::$app->session;

		$relationsListAr = RelationName::find()
			->where(['gender' => $person->gender])
			->all();
		$relationsList = [];
		foreach ($relationsListAr as $row) {
			$relationsList[$row['id']] = $person->gender == 'm' ?
				Yii::t('app-m', $row['relation_name']) : Yii::t('app-f', $row['relation_name']);
		}


		// model for selecting 'b' person of relation
		$searchModel = new PersonSearch();
		$provider = $searchModel->search(Yii::$app->request->get(), 5);

		// model to be filled-in by form
		$model = new PersonRelation;
		$success = false;

		if ($model->load(Yii::$app->request->post()) && $model->validate()) {

			$relationFromName = $model->relationName->relation_name;
			$personBgender = $model->person_b->gender;
			$relationToName = RelationPair::relationComplement($person->gender, $personBgender, $relationFromName);
			$relationToId = RelationName::find()->where(['relation_name' => $relationToName, 'gender' => $personBgender])->all()[0]->id;
			$duplicate = PersonRelation::find()
				->where(['person_a_id' => $model->person_b_id, 'relation_ab_id' => $relationToId, 'person_b_id' => $id])->all();

			if (!$duplicate && ($model->person_a_id != $model->person_b_id)) {

				try {
					if ($model->save()) {
						$success = true;
						$session->setFlash('success', Yii::t('app', 'Relation was added'));
					}
				} catch (\Exception $ex) {

					$session->setFlash('danger', $ex->getMessage());

					return $this->render('relationAdd', [
						'person' => $person,
						'model' => $model,
						'relationsList' => $relationsList,
						'provider' => $provider,
						'searchModel' => $searchModel,
						'success' => $success,
					]);
				}
			} elseif ($model->person_a_id == $model->person_b_id) {
				$success = false;
				$session->setFlash('warning', Yii::t('app', "Can't define relation to same person!"));
			} else {
				$success = false;
				$session->setFlash('warning', Yii::t('app', 'Relation already exists!'));
			}
		}

		if ($e_msg = $model->getFirstError('person_b_id')) {
			$session->setFlash('danger', $e_msg);
		}

		return $this->render('relationAdd', [
			'person' => $person,
			'model' => $model,
			'relationsList' => $relationsList,
			'provider' => $provider,
			'searchModel' => $searchModel,
			'success' => $success,
		]);
	}

	public function actionUpdate($id, $relation_id) {

		$model = new RelationUpdate;
		$personRelation = PersonRelation::findOne($relation_id);
		$ownershipCheck = $personRelation->checkOwnership();
		$person = Person::findOne($id);

		if ($person && $ownershipCheck) {

			// select proper name, surname of 'TO' person
			if ($personRelation->person_a_id != $id) {
				$model->surname = $personRelation->person_a->surname;
				$model->name = $personRelation->person_a->name;
			} else {
				$model->surname = $personRelation->person_b->surname;
				$model->name = $personRelation->person_b->name;
			}


			// create list of relations to choose from
			$relationsListAr = RelationName::find()
				->where(['gender' => $person->gender])
				->all();
			$relationsList = [];
			foreach ($relationsListAr as $row) {
				$relationsList[$row['id']] = $person->gender == 'm' ?
					Yii::t('app-m', $row['relation_name']) : Yii::t('app-f', $row['relation_name']);
			}

			if ($model->load(Yii::$app->request->post()) && $model->validate()) {

				if ($personRelation->person_a_id == $id) {
					$personRelation->relation_ab_id = $model->relation_ab_id;
				} else {
					$relationName = RelationName::find()->where(['id' => $model->relation_ab_id])->all()[0]->relation_name;
					$genderFrom = $person->gender;
					$genderTo = $personRelation->person_a->gender;
					$relationName = RelationPair::relationComplement($genderFrom, $genderTo, $relationName);
					$relationId = RelationName::find()->where(['relation_name' => $relationName])->all()[0]->id;
					$personRelation->relation_ab_id = $relationId;
				}

				$session = Yii::$app->session;
				try {
					if (
						$personRelation->validate()
						&& $personRelation->getDirtyAttributes()
						&& $personRelation->save()
					) {
						$session->setFlash('success', Yii::t('app', 'Relation updated'));
					}
				} catch (\Exception $ex) {
					$session->setFlash('danger', $ex->getMessage());
				}
			}


			return $this->render('relationUpdate', [
				'person' => $person,
				'model' => $model,
				'relationsList' => $relationsList,
			]);
		} else {
			return $this->redirect(['person/index']);
		}
	}

	public function actionDelete($id, $relation_id) {


		$personRelation = PersonRelation::findOne($relation_id);
		$ownershipCheck = $personRelation->checkOwnership();
		if ($ownershipCheck) {

			$relation = $personRelation->relationName->relation_name;
			$relation = $personRelation->person_a->gender == 'm' ?
				Yii::t('app-m', $relation) : Yii::t('app-f', $relation);
			$nameTo = $personRelation->person_b->name . ' ' . $personRelation->person_b->surname;
			$nameFrom = $personRelation->person_a->name . ' ' . $personRelation->person_a->surname;
			if ($personRelation->delete()) {
				Yii::$app->session->setFlash(
					'info',
					Yii::t('app', 'Relation') . ': "' . $relation . '" '
						. ' ' . $nameFrom . ' ' . Yii::t('app', 'to') . ' ' . $nameTo . ' ' . Yii::t('app', 'deleted')
				);
			}
		}
		return $this->redirect(['person/update', 'id' => $id]);
	}
}
