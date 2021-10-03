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
				<li>
					<?= Html::a(Yii::t('app','List'), ['my-db/index']) ?>
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
