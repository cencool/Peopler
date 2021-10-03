<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\{LoginForm, Db, Table, ActiveTable};
use yii\filters\AccessControl;

class SiteController extends Controller {

	public function actionIndex() {
		return $this->render('index');
	}

	public function actionLogin(string $message = '') {
		$model = new LoginForm();

		if ($model->load(Yii::$app->request->post())) {

			if (($model->load(Yii::$app->request->post())) && $model->login()) {
				return $this->render('index');
			} else {
				return $this->render('login', ['model' => $model, 'message' => $message]);
			}
		}

		return $this->render('login', ['model' => $model, 'message' => $message]);
	}

	public function actionLogout() {
		Yii::$app->user->logout();
		return $this->render('index');
	}

	/* needs only Identity as state parameter */
	/* creates dbName state parameter */
	public function actionSelectDb() {
		$model = new Db();

		if ($model->load(Yii::$app->request->post())) {
			$session = Yii::$app->session;
			$session->open();
			$session['dbName'] = $model->dbName;
			return  $this->redirect(['site/select-table', 'dbName' => $model->dbName]);
		} else {
			if ($this->connectDb($model)) {
				$model->getDbList();
				return $this->render('selectDb', ['model' => $model]);
			}
		}
	}

	/* needs dbName as state parameter - if not set redirects to actionSelectDb */
	/* creates tableName state parameter */
	public function actionSelectTable() {
		$model = new Table();
		$session = Yii::$app->session;
		$session->open();
		$session->remove('colNames');

		if ($model->load(Yii::$app->request->post())) {
			$session['tableName'] = $model->tableName;
			$session['colNames'] = $model->colNames;
			return $this->redirect(['site/show-table']);
		} elseif (isset($session['dbName']) &&  $this->connectDb($model)) {
			$model->dbName = $session['dbName'];
			$model->getTableList($session['dbName']);
			return $this->render('selectTable', [
				'model' => $model,
			]);
		} else {
			return $this->redirect(['site/select-db']);
		}
	}

	public function actionShowTable() {
		$model = new Table();
		$session = Yii::$app->session;
		$session->open();
		$dbName = $session['dbName'];
		$model->dbName = $dbName;
		$tableName = $session['tableName'];
		$model->tableName = $tableName;
		Yii::$app->params['currentTable'] = $tableName;
		Yii::$app->params['currentDb'] = $dbName;


		if (($selectedColumns = Yii::$app->request->post('columns'))) {
			$colNames = $selectedColumns;
			$session['colNames'] = $colNames;
			$model->colNames = $colNames;
		} else {
			$colNames = $session['colNames'];
			$model->colNames = $colNames;
		}

		$this->connectDb($model);
		$result = $model->getTableRows($dbName, $tableName, $colNames);
		$rows = $result['tableRows'];
		$model->tableRows = $rows;
		$pages = $result['pages'];
		return $this->render(
			'showTable',
			[
				'model' => $model,
				'pages' => $pages
			]
		);
	}

	public function actionShowActiveTable() {
		$model = new Table();
		$session = Yii::$app->session;
		$session->open();
		$dbName = $session['dbName'];
		$model->dbName = $dbName;
		$tableName = $session['tableName'];
		$model->tableName = $tableName;

		if ($dbName && $tableName) {
			$this->connectDb($model);
			$model->selectDb();
			$query = ActiveTable::table($tableName)::find();

			return $this->render('grid', ['query' => $query, 'model' => $model]);
		} elseif(!$dbName) {
			return $this->redirect(['site/select-db']);
		} else {
			return $this->redirect(['site/select-table']);
		}
	}

	public function connectDb($model) {
		$identity  =  Yii::$app->user->identity;
		if ($model->dbConnect($identity->user, $identity->password)) {
			return true;
		} else {
			echo $this->render('error', ['error' => 'User: \'' . $identity->user . '\' has not access to db server!']);
			exit();
		}
	}
}
