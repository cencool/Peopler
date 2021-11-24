<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<div class='row'>
	<?php if ($fileGallery) {
		foreach ($fileGallery as $person_file) { ?>
			<div class='col-xs-3  h-25'>
				<?php $file_name = $person_file->file_name;
				echo Html::img(['person/send-thumbnail', 'fileName' => $file_name], ['class' => 'img-responsive img-thumbnail']);
				echo $person_file->file_caption == "" ? Html::tag('p', "_") : Html::tag('p', $person_file->file_caption);
				echo '<i class="glyphicon glyphicon-fullscreen"></i>';
				?>
			</div>
	<?php	}
	}
	?>
</div>

<?= LinkPager::widget([
	'pagination' => $pages,
]) ?>
