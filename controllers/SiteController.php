<?php

namespace app\controllers;

use yii\web\Controller;
use yii\helpers\Url;

class SiteController extends Controller {


	public function actionIndex() {
		$a = Url::current([],true);
		return $this->redirect(['person/index']);
	}
}
