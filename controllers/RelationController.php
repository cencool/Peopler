<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\PersonDetail;
use app\models\basic\RelationPair;
use app\models\basic\RelationSearch;
use app\models\basic\PersonRelation;
use app\models\basic\RelationName;
use Yii;

class RelationController extends Controller {

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

	public function actionIndex() {
		$searchModel = new PersonSearch();
		$provider = $searchModel->search(Yii::$app->request->get(), 10);


		return $this->render('index', ['provider' => $provider, 'searchModel' => $searchModel]);
	}

	public function actionAddRelation($id) {

		$person = Person::findOne($id);
		$session = Yii::$app->session;

		//$relationsList = RelationPair::relationsList($person->gender);
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

		try {
			if ($model->load(Yii::$app->request->post()) && $model->save()) {
				$success = true;
				$session->setFlash('relationAdded', Yii::t('app', 'Relation was added'));
			}
		} catch (\Exception $ex) {

			$session->setFlash('relationAddError', $ex->getMessage());

			return $this->render('relationAdd', [
				'person' => $person,
				'model' => $model,
				'relationsList' => $relationsList,
				'provider' => $provider,
				'searchModel' => $searchModel,
				'success' => $success,
			]);
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

		$model = PersonRelation::findOne($relation_id);
		$person = Person::findOne($id);

		// if it is direct relation
		if ($model->person_a_id == $id) {

			// create list of relations to choose from
			$relationsListAr = RelationName::find()
				->where(['gender' => $person->gender])
				->all();
			$relationsList = [];
			foreach ($relationsListAr as $row) {
				$relationsList[$row['id']] = $person->gender == 'm' ?
					Yii::t('app-m', $row['relation_name']) : Yii::t('app-f', $row['relation_name']);
			}
			$session = Yii::$app->session;

			try {
				if (
					$model->load(Yii::$app->request->post())
					&& $model->validate()
					&& $model->getDirtyAttributes()
					&& $model->save()
				) {
					$session->setFlash('relationUpdated', Yii::t('app', 'Relation updated'));
				}
			} catch (\Exception $ex) {
				$session->setFlash('relationUpdateError', $ex->getMessage());
			}

			return $this->render('relationUpdate', [
				'person' => $person,
				'model' => $model,
				'relationsList' => $relationsList,
			]);
			// redirect to direct relation of person b then
		} else {
			return $this->redirect(['relation/update', 'id' => $model->person_a_id, 'relation_id' => $relation_id]);
		}
	}

	public function actionDelete($id, $relation_id) {
		$personRelation = PersonRelation::findOne($relation_id);
		$relation = $personRelation->relation_ab;
		$name = $personRelation->person_b->name . ' ' . $personRelation->person_b->surname;
		if ($personRelation->delete()) {
			Yii::$app->session->setFlash('relationDeleted', 'Relation:' . $relation . ' to ' . $name . ' deleted');
		}
		return $this->redirect(['person/update', 'id' => $id]);
	}
}
