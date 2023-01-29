<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use app\widgets\Alert;




?>

<?php

$this->params['breadcrumbs'][] = ['label' => $person->surname . ' ' . $person->name, 'url' => ['person/update', 'id' => $person->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Attachment upload')];

echo Alert::widget();

?>

<?php $form = ActiveForm::begin([
	'options' => ['autocomplete' => 'off', 'class' => 'form-inline'],
]) ?>
<?= $form->field($personFile, 'file_caption')->textInput()->label(Yii::t('app', 'Caption')) ?>
<?= $form->field($uploadModel, 'imageFile')->fileInput()->label(Yii::t('app', 'File')) ?>
<button type='submit' class='btn btn-primary pull-right'><?= Yii::t('app', 'Submit') ?></button>
<p></p>
<?php ActiveForm::end() ?>

<?= $this->render('attachmentView', ['fileGallery' => $fileGallery, 'pages' => $pages]) ?>
