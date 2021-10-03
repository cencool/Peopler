<?php

namespace app\controllers;

use yii\web\Controller;
use yii\db\Connection;

class CalcController extends Controller {

	//public $layout = 'calc';

	public function actionIndex() {
		return $this->render('keypad');

	}

	public function actionAjax() {
		if (\Yii::$app->request->post('a') =='1') {
		echo 'Zadal si 1';
		} else {
		echo 'Nezadal si 1';
		}
	}
}
?>
