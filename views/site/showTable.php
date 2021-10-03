<?php

use Yii;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\assets\SelectColumnAsset;


SelectColumnAsset::register($this);

$this->params['tableName'] = $model->tableName;
$this->params['dbName'] = $model->dbName;
$this->title = 'TableView';
$this->params['breadcrumbs'][] = ['label' => 'Select Db', 'url' => ['site/select-db']];
$this->params['breadcrumbs'][] = ['label' => 'Select Table', 'url' => ['site/select-table']];
$this->params['breadcrumbs'][] = $this->title;



$colNames = array_keys($model->tableRows[0]);

?>

<div class='container'>
	<form id='colKeep' method='POST' action='?r=site/show-table'>
		<?php foreach ($colNames as $col) { ?>
			<input type='hidden' name='columns[]' value='<?= $col ?>'>
		<?php } ?>
	</form>

	<form id='colHide' method='POST' action='?r=site/show-table'>
		<?php foreach ($colNames as $col) { ?>
			<input type='hidden' name='columns[]' value='<?= $col ?>'>
		<?php } ?>
	</form>

	<form id='colReset' method='POST' action='?r=site/show-table'>
		<input type='hidden' name='columns[]' value='*'>
	</form>

	<div class='btn-group'>
		<button type='submit' class='btn ' form='colKeep'>KEEP</button>
		<button type='submit' class='btn ' form='colHide'>HIDE</button>
		<button type='submit' class='btn ' form='colReset'>RESET</button>
	</div>
</div>

<div class='container'>
	<div class='table-responsive'>
		<table class='table table-bordered table-hover'>
			<tr>
				<?php
				foreach ($colNames as $col) {
					echo Html::tag('th', $col, ['class' => ['colName', $col]]);
				} ?>
			</tr>
			<?php foreach ($model->tableRows as $row) { ?>
				<tr>
					<?php foreach ($row as $key => $val) {
						echo Html::tag('td', $val, ['class' => $key]);
					} ?>
				</tr>
			<?php } ?>
		</table>
	</div>
	<?= LinkPager::widget(['pagination' => $pages]) ?>
</div>
