<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Login;
use app\models\basic\User;
use app\models\basic\UploadFile;
use app\models\basic\PersonFile;
use yii\web\UploadedFile;
use app\models\basic\Person;
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

	public function actionLogin($required = false) {
		$model = new  Login;
		$post = Yii::$app->request->post();
		if ($model->load($post)) {
			if (($identity = User::findIdentity($model->IdInput)) && ($identity->validateAuthKey($model->PwdInput))) {
				Yii::$app->user->login($identity);
				return $this->redirect(Yii::$app->user->returnUrl);
			} else {
				$session = Yii::$app->session;
				$session->setFlash('loginIncorrect', Yii::t('app', 'Incorrect credentials'));
			}
		}
		return $this->render('loginForm', ['model' => $model, 'required' => $required]);
	}

	public function actionLogout() {
		Yii::$app->user->logout();
		return $this->redirect(['site/login']);
	}

	public function actionUpload($id) {
		$uploadModel = new UploadFile();
		$personFile = new PersonFile;
		$person = Person::findOne($id);
		$session = Yii::$app->session;

		if (Yii::$app->request->isPost) {
			$uploadModel->imageFile = UploadedFile::getInstance($uploadModel, 'imageFile');
			$timeStamp = (new \DateTime())->getTimestamp();
			$fileBaseName = $person->surname.
							'@@' .
							$timeStamp .
							'.' .
							$uploadModel->imageFile->extension;
			$fileName = '@app/uploads/' . $fileBaseName;

			$personFile->person_id = $id;
			$personFile->file_name = $fileBaseName;

			if ($uploadModel->upload($fileName) && $personFile->save()) {
				$session->setFlash('uploadSuccess', Yii::t('app', 'Upload Successful'));
			} else {
				$session->setFlash('uploadError', Yii::t('app', 'Upload Failed'));
			}
		}

		return $this->render('uploadForm', ['model' => $uploadModel]);
	}
}
