<?php

namespace app\controllers;
//cvicny koment

use yii\web\Controller;

class MyDbController extends Controller {


	public function actionIndex() {
		return $this->redirect(['person/index']);
	}
}
