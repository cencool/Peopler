<?php

declare(strict_types=1);
/**
 * This controller is related to actions performed on Person.
 */


namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\PersonDetail;
use app\models\basic\RelationSearch;
use app\models\basic\PersonAttachment;
use app\models\basic\Items;
use app\models\basic\Undelete;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use app\models\basic\ItemSearch;
use Yii;

class PersonController extends Controller {

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
	/**
	 * Managing correct language selection
	 */
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
	/**
	 * Starting page of application.
	 * Displays table of persons visible for particular user (after login)
	 */
	public function actionIndex() {
		$searchModel = new PersonSearch();
		$provider = $searchModel->search(Yii::$app->request->get(), 10);
		if (Yii::$app->request->isPjax) {
			return $this->renderPartial('_index', ['provider' => $provider, 'searchModel' => $searchModel]);
		}


		return $this->render('index', ['provider' => $provider, 'searchModel' => $searchModel]);
	}

	public function actionView($id) {

		$person = Person::findOne($id);
		if ($person) {
			$searchModel = new RelationSearch();
			$AttachmentCount = count(PersonAttachment::find()->where(['person_id' => $id])->all());
			$provider = $searchModel->search(Yii::$app->request->get());
			return $this->render('personView', [
				'model' => $person,
				'provider' => $provider,
				'attachmentCount' => $AttachmentCount,
				'searchModel' => $searchModel
			]);
		} else $this->redirect(['index']);
	}

	public function actionNewPerson() {
		$person = new Person;
		$person->owner = Yii::$app->user->id;
		$personDetail = new PersonDetail;
		$AttachmentCount = 0;
		$itemModel = new Items;

		if ($person->load($_POST) && $person->getDirtyAttributes() && $person->save()) {
			if ($personDetail->load($_POST) && $personDetail->getDirtyAttributes()) {
				$personDetail->link('person', $person);
			}

			Yii::$app->session->setFlash('success', Yii::t('app', 'Person') . ' ' . $person->name . ' ' . $person->surname . ' ' . Yii::t('app', 'added'));
			return $this->redirect(['update', 'id' => $person->id]);
		};

		$searchModel = new RelationSearch();
		$provider = $searchModel->search(Yii::$app->request->get());
		$itemSearchModel = new ItemSearch();
		$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), isset($person->id) ? $person->id : -1, 20);

		return $this->render('personUpdate', [
			'person' => $person,
			'personDetail' => $personDetail,
			'provider' => $provider,
			'searchModel' => $searchModel,
			'attachmentCount' => $AttachmentCount,
			'itemModel' => $itemModel,
			'itemsDataProvider' => $itemsDataProvider,
			'itemSearch' => $itemSearchModel,
		]);
	}

	public function actionUpdate($id = null) {
		$AttachmentCount = count(PersonAttachment::find()->where(['person_id' => $id])->all());

		if (($id != null) && ($person = Person::findOne($id))) {

			if (!$personDetail = $person->detail) {
				$personDetail = new PersonDetail;
			}

			// if change in person and save successfull
			if ($person->load($_POST) && $person->getDirtyAttributes()) {

				if ($person->save()) {
					Yii::$app->session->setFlash('success', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				}
			};

			// if change in detail save it using 'link' method for relation
			if ($personDetail->load($_POST) && $personDetail->getDirtyAttributes()) {
				Yii::$app->session->setFlash('success', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				$personDetail->link('person', $person);
			}

			$searchModel = new RelationSearch();
			$provider = $searchModel->search(Yii::$app->request->get());



			return $this->render('personUpdate', [
				'person' => $person,
				'personDetail' => $personDetail,
				'searchModel' => $searchModel,
				'provider' => $provider,
				'attachmentCount' => $AttachmentCount,
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
		if ($person) {
			$session = Yii::$app->session;

			Undelete::addUndeleteRecord($person);

			try {
				$person->delete();
				Yii::$app->session->setFlash('info', Yii::t('app', 'Person') . ' ' . $person->name . ' ' . $person->surname . ' ' . Yii::t('app', 'deleted'));
			} catch (\Exception $ex) {

				$session->setFlash('danger', $ex->getMessage());
			}
			return $this->redirect(['index']);
		} else {
			return $this->redirect(['index']);
		}
	}

	public function actionUndelete() {
		Undelete::undeletePerson();
		return $this->redirect(['index']);
	}
}
