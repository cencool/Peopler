<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

if (isset($itemModel)) {
    $itemForm = ActiveForm::begin();
?>
<?= $itemForm->field($itemModel, 'item') ?>
<?= Html::submitButton('Add Item', ['class' => 'btn btn-primary']) ?>
<?php
    ActiveForm::end();
}
?>


<?php
echo GridView::widget([
    'dataProvider' => $itemsDataProvider,
])
?>

