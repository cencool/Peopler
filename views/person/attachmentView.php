<?php

use yii\helpers\Html;

?>
<?php if ($fileGallery) {
	foreach ($fileGallery as $person_file) {
		$file_name = $person_file->file_name;
		echo Html::img(['person/show-file', 'fileName' => $file_name], ['width' => 200, 'height' => 200]);
		echo Html::tag('p',$person_file->file_caption);
		echo '<hr>';
	}
}
?>
