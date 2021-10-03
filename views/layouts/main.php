<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\SiteAsset;
use yii\widgets\Breadcrumbs;


SiteAsset::register($this);
$this->beginPage();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>

<body>
	<?php $this->beginBody() ?>
	<header>
		<nav class='navbar navbar-default navbar-fixed-top '>
			<div class='navbar-header'>
				<a href=<?= Url::to(['index']) ?> class='navbar-brand'><img src='/icicle.png' width='25' height='25'></a>
			</div>
			<ul class='nav navbar-nav'>
				<li><a href='/index.php'><?= Yii::t('app', 'Home') ?></a></li>
				<li>
					<?php echo (Yii::$app->user->isGuest) == true ?
						Html::a(\Yii::t('app', 'Login'), ['site/login']) :
						Html::a(\Yii::t('app', 'Logout ') . '('
							. Yii::$app->user->id . ')', ['site/logout']);
					?>
				</li>
				<li>
					<?php if (Yii::$app->user->isGuest) {
						echo '';
					} elseif (isset($this->params['dbName'])) {
						echo Html::a('<b>Db:</b> ' . $this->params['dbName'], ['site/select-db']);
					} else {
						echo Html::a(Yii::t('app', 'Select Db'), ['site/select-db']);
					} ?>

				</li>
				<li>
					<?php if (isset($this->params['dbName'])) {
						if (isset($this->params['tableName'])) {
							echo Html::a('<b>Table:</b> ' .
								$this->params['tableName'], ['site/select-table']);
						} else {
							echo Html::a(Yii::t('app', 'Select Table'), ['site/select-table']);
						}
					}
					?>
				</li>
				<li>
					<?= Html::a('Calculator', ['calc/']) ?>
				</li>
				<li>
					<?= Html::a('GridView', ['site/show-active-table']) ?>
				</li>
				<li>
					<?= Html::a('MyDb', ['my-db/index']) ?>
			</ul>
		</nav>


	</header>
	<div class='container'>
		<?= Breadcrumbs::widget([
			'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
			'homeLink' => false,
		]) ?>

		<div class='content'>
			<?= $content ?>
		</div>
	</div>

	<footer>
		<p style='text-align:center;'><b>&copy; 2021 Cencul </b></p>
	</footer>
	<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
