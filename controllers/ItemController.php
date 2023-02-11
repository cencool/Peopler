<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\basic\Person;
use app\models\basic\Items;
use app\models\basic\ItemSearch;
use yii\web\BadRequestHttpException;

class ItemController extends Controller {

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

	public function actionViewItems($id) {
		$person = Person::findOne($id);
		if ($person) {
			$searchModel = new ItemSearch();
			$itemsDataProvider = $searchModel->searchView(Yii::$app->request->get(), $id, 10);
			return $this->render('itemView', ['person'=>$person,'itemsDataProvider' => $itemsDataProvider, 'itemSearch' => $searchModel]);
		} else $this->redirect(['person/index']);
	}

	public function actionDelete($id, $itemId) {
		$person = Person::findOne($id);
		$model = Items::findOne($itemId);
		$session = Yii::$app->session;

		if ($person && $model) {

			$itemSearchModel = new ItemSearch();
			$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $id, 10);
			$itemModel = new Items();

			try {
				$model->delete();
				$session->setFlash('info', Yii::t('app', 'Item') . ' #' . $itemId . ' ' . Yii::t('app', 'deleted'));
			} catch (\Exception $ex) {

				$session->setFlash('danger', $ex->getMessage());
			}
			return $this->renderPartial('//item/itemUpdateList', [
				'person' => $person,
			]);
		}
		throw new BadRequestHttpException();
	}

	public function actionAdd() {
		$itemModel = new Items();

		// taking data from item add form

		if ($itemModel->load($_POST)) {
			$person = Person::findOne($itemModel->person_id);
			if ($person && $itemModel->save()) {
				Yii::$app->session->setFlash('success', 'Added item #' . $itemModel->id . ': ' . $itemModel->item);

				$itemSearchModel = new ItemSearch();
				$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $person->id, 10);

				return $this->renderPartial('//item/itemAdd', [
					'person' => $person,
				]);
			}
		}

		throw new BadRequestHttpException();
	}

	public function actionEdit($itemId, $personId) {

		$person = Person::findOne($personId);
		$itemModel = $itemId == null ? null : Items::findOne($itemId);

		if ($person && $itemModel) {

			return $this->renderPartial('//item/itemUpdateList', [
				'person' => $person,
				'itemModel' => $itemModel,
			]);
		}
	}

	public function actionUpdate() {

		if (isset($_POST['Items'])) {
			$personId = $_POST['Items']['person_id'];
			$itemId = $_POST['Items']['id'];
			$person = Person::findOne($personId);
			$itemModel =  Items::findOne($itemId);


			if ($person && $itemModel) {
				if ($itemModel->load($_POST)) {
					if ($itemModel->save()) {
						Yii::$app->session->setFlash('success', 'Updated item #' . $itemModel->id . ': ' . $itemModel->item);
					}
				}
				return $this->renderPartial('//item/itemUpdateList', [
					'person' => $person,
				]);
			}

		}
			throw new BadRequestHttpException();
	}

	public function actionUpdateList($personId=null) {

		if ($person = Person::findOne($personId)) {

			return $this->renderPartial('//item/itemUpdateList', [
				'person' => $person,
			]);
		}
		throw new BadRequestHttpException();
	}
}
