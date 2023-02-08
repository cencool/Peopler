<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\basic\Person;
use app\models\basic\Items;
use yii\data\ActiveDataProvider;
use app\models\basic\ItemSearch;

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
			$itemsDataProvider = $searchModel->search(Yii::$app->request->get(), $id, 10);
			return $this->render('itemView', ['itemsDataProvider' => $itemsDataProvider, 'itemSearch' => $searchModel]);
		} else $this->redirect(['person/index']);
	}

	public function actionDelete($id, $itemId) {
		$person = Person::findOne($id);
		$model = Items::findOne($itemId);
		if ($person && $model) {
			$session = Yii::$app->session;

			$itemSearchModel = new ItemSearch();
			$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $id, 10);
			$itemModel = new Items();

			try {
				$model->delete();
				$session->setFlash('info', Yii::t('app', 'Item') . ' #' . $itemId . ' ' . Yii::t('app', 'deleted'));
				//	$this->redirect(['person/update','id'=>$personId]); 
			} catch (\Exception $ex) {

				$session->setFlash('danger', $ex->getMessage());
			}
			return $this->renderPartial('//item/itemActionList', [
				'person' => $person,
				'itemsDataProvider' => $itemsDataProvider,
				'itemSearch' => $itemSearchModel,
				'itemModel' => $itemModel,
			]);
		}
	}

	public function actionAdd() {
		$itemModel = new Items();

		// taking data from item add form

		if ($itemModel->load($_POST)) {
			if ($itemModel->save()) {
				Yii::$app->session->setFlash('success', 'Added item #' . $itemModel->id . ': ' . $itemModel->item);
			}
			$person = Person::findOne($itemModel->person_id);
		}

		$itemSearchModel = new ItemSearch();
		$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $person->id, 10);

		return $this->renderPartial('//item/itemAdd', [
			'person' => $person,
			'itemsDataProvider' => $itemsDataProvider,
			'itemSearch' => $itemSearchModel,
			'itemModel' => $itemModel,
		]);
	}

	public function actionEditItems($id) {

		$person = Person::findOne($id);

		$itemSearchModel = new ItemSearch();
		$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $id, 10);

		return $this->renderPartial('//item/itemActionList', [
			'person' => $person,
			'itemsDataProvider' => $itemsDataProvider,
			'itemSearch' => $itemSearchModel,
		]);
	}
}
