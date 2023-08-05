<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

 $form = ActiveForm::begin(); 
?>
<?= $form->field($photoUpload,'imageFile')->fileInput()?>

<?= Html::submitButton('Submit',['class'=>'btn btn-primary']) ?>
<?php ActiveForm::end() ?>

<?ph?>
