<?php

namespace app\controllers;

use yii\web\Controller;
use Yii;

class SiteController extends Controller {


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
		return $this->redirect(['person/index']);
	}
}
