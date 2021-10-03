<?php

namespace app\models;

use Yii;
use app\models\Db;
use yii\db\Query;
use yii\data\Pagination;

class Table extends Db  {
	public $tableList;
	public $tableName;
	public $tableRows;
	public $colNames;

	public function rules() {
		$rules =parent::rules();
		$rules[] =  [['tableList','tableName','colNames','tableRows'],'safe'];
		return $rules;
	}

	public function getTableList()
	{

		Yii::$app->dba->createCommand('USE ' . $this->dbName)->execute();
		$rows = Yii::$app->dba->createCommand('SHOW TABLES')->queryAll(\PDO::FETCH_NUM);
		$this->tableList = [];
		// convert returned rows to format suitable for dropDownList
		foreach ($rows as $value) {
			$this->tableList[$value[0]] =  $value[0];
		}
		return $this->tableList;
	}

	public function getTableRows()
	{

		$this->selectDb();
		$db = Yii::$app->dba;
		$cols = implode(',', $this->colNames);
		$rowsQuery = (new Query())->select($cols)->from($this->tableName);
		$countQuery = clone $rowsQuery;
		$rowCount = $countQuery->count('*',$db);
		$pages = new Pagination(['totalCount' => $rowCount, 'defaultPageSize' => 10]);
		$tableRows = $rowsQuery->offset($pages->offset)->limit($pages->limit)->all($db);
		return ['tableRows' => $tableRows, 'pages' => $pages];
	}

}
