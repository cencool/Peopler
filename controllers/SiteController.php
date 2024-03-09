<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Login;
use app\models\basic\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\base\InvalidArgumentException;

class SiteController extends Controller {


	public function beforeAction($action) {

		if ($action->id == 'get-token') {
			$this->enableCsrfValidation = false;
		}
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

	public function actionLogin($required = false) {
		$model = new  Login;
		$post = Yii::$app->request->post();
		if ($model->load($post)) {
			if (($identity = User::findIdentity($model->IdInput)) && ($identity->validateAuthKey($model->PwdInput))) {
				Yii::$app->user->login($identity, 600);
				return $this->redirect(Yii::$app->user->returnUrl);
			} else {
				$session = Yii::$app->session;
				$session->setFlash('warning', Yii::t('app', 'Incorrect credentials'));
			}
		}
		return $this->render('loginForm', ['model' => $model, 'required' => $required]);
	}

	public function actionLogout() {
		Yii::$app->user->logout();
		return $this->redirect(['site/login']);
	}
	public function actionGetToken() {
		$request = Yii::$app->request;
		Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		if ($request->isPost) {
			$user = $request->getBodyParam('user');
			$password = $request->getBodyParam('password');
			$identity = User::findIdentity($user);
			if ($identity && $identity->validateAuthKey($password)) {
				return ['token' => $identity->getAccessToken()];
			}
		}
		return ['token' => null];
	}
}
