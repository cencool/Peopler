<?php

use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$this->params['tableName'] = $model->tableName;
$this->params['dbName'] = $model->dbName;

$dataProvider = new ActiveDataProvider([
	'query' => $query,
	'pagination' => [
		'pageSize' => 10,
	],
]);

?>
<div class='container'>
	<?php
	echo GridView::widget([
		'dataProvider' => $dataProvider,
	]);

	?>

</div>
