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

	public function actionDelete($personId, $itemId) {
		$person = Person::findOne($personId);
		if ($person) {
			$session = Yii::$app->session;
			$model = Items::findOne($itemId);

			$itemSearchModel = new ItemSearch();
			$itemsDataProvider = $itemSearchModel->search(Yii::$app->request->get(), $personId, 10);
			$itemModel = new Items();

			try {
				$model->delete();
				$session->setFlash('info', Yii::t('app', 'Item') . ' #' . $itemId . ' ' . Yii::t('app', 'deleted'));
				return $this->renderPartial('//item/itemUpdate', [
					'person' => $person,
					'itemsDataProvider' => $itemsDataProvider,
					'itemSearch' => $itemSearchModel,
					'itemModel' => $itemModel,
				]);
				//	$this->redirect(['person/update','id'=>$personId]); 
			} catch (\Exception $ex) {

				$session->setFlash('danger', $ex->getMessage());
			}
		}
	}
}
