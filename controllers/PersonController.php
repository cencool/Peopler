<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\PersonDetail;
use app\models\basic\RelationSearch;
use Yii;

class PersonController extends Controller {

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

	public function actionView($id) {

		$person = Person::findOne($id);
		$searchModel = new RelationSearch();
		$provider = $searchModel->search(Yii::$app->request->get());
		return $this->render('personView', ['model' => $person, 'provider' => $provider, 'searchModel' => $searchModel]);
	}

	public function actionNewPerson() {
		$person = new Person;
		$personDetail = new PersonDetail;
		if ($person->load($_POST) && $person->getDirtyAttributes() && $person->save()) {
			$personDetail->load($_POST);
			$personDetail->link('person', $person);
			Yii::$app->session->setFlash('personAdded',Yii::t('app','Person').' ' . $person->name . ' ' . $person->surname . ' '.Yii::t('app','added'));
			return $this->redirect(['update', 'id' => $person->id]);
		};

		$searchModel = new RelationSearch();
		$provider = $searchModel->search(Yii::$app->request->get());

		return $this->render('personUpdate', [
			'person' => $person,
			'personDetail' => $personDetail,
			'provider' => $provider,
			'searchModel' => $searchModel,
		]);
	}

	public function actionUpdate($id = null) {
		if (($id != null) && ($person = Person::findOne($id))) {

			if (!$personDetail = $person->detail) {
				$personDetail = new PersonDetail;
			}

			// if change in person and save successfull
			if ($person->load($_POST) && $person->getDirtyAttributes()) {
				/*
				$pClass = get_class($person);
				$pAtt = $person->getAttributes();
				$pDet = $person->detail;
				$pFrom = $person->relationsFromPerson();
				$pTo = $person->relationsToPerson();
				 */

				if ($person->save()) {
					Yii::$app->session->setFlash('personUpdated', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				}
			};

			// if change in detail save it using 'link' method for relation
			if ($personDetail->load($_POST) && $personDetail->getDirtyAttributes()) {
				Yii::$app->session->setFlash('personUpdated', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				$personDetail->link('person', $person);
			}

			$searchModel = new RelationSearch();
			$provider = $searchModel->search(Yii::$app->request->get());

			return $this->render('personUpdate', [
				'person' => $person,
				'personDetail' => $personDetail,
				'searchModel' => $searchModel,
				'provider' => $provider,
			]);
		} else {

			return $this->redirect(['index']);
		}
	}
	/**
	 * deletes person from database,  related records deleted as cascade
	 * @param integer $id person id to be deleted
	 */ 

	public function actionDelete($id) {
		$person = Person::findOne($id);
		$session = Yii::$app->session;
		try {
			$person->delete();
			Yii::$app->session->setFlash('personDeleted', Yii::t('app', 'Person') . ' ' . $person->name . ' ' . $person->surname . ' ' . Yii::t('app', 'deleted'));
		} catch (\Exception $ex) {

			$session->setFlash('personDeleteError', $ex->getMessage());
		}
			return $this->redirect(['index']);
	}
}

