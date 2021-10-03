<?php 
use app\assets\CalcAsset;
CalcAsset::register($this);

$this->beginPage();
 ?>

<!DOCTYPE html>
<html lang='en'>

<head>
	<meta charset="UTF-8">
	<?php $this->head() ?>
</head>

<body>
	<?php $this->beginBody() ?>

	<?= $content ?>

	<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
