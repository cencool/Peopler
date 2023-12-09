<?php

use \yii\widgets\ActiveForm;
use \yii\helpers\Html;

$form = ActiveForm::begin([
    'id' => 'general-search-form',
]);
?>


<div class="row">
    <div class="col-sm-6">
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'surname') ?>
        <?= $form->field($model, 'place') ?>
        <?= $form->field($model, 'gender') ?>
    </div>
    <div class="col-sm-6">
        <?= $form->field($model, 'marital_status') ?>
        <?= $form->field($model, 'maiden_name') ?>
        <?= $form->field($model, 'address') ?>
        <?= $form->field($model, 'item') ?>
        <?= $form->field($model, 'note')->textarea() ?>
    </div>

</div>

<div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end() ?>