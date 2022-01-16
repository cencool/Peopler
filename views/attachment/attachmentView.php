<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use app\assets\ShowAttachmentAsset;
use app\models\basic\Person;
use Yii;

ShowAttachmentAsset::register($this);
if (isset($id)) {
    $person = Person::findOne($id);
    $this->params['breadcrumbs'][] = ['label' => 'Index', 'url' => ['person/index']];
    $this->params['breadcrumbs'][] = ['label' => $person->surname . ' ' . $person->name, 'url' => ['person/view', 'id' => $id]];
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Attachments')];
}
$this->registerJsVar('deleteMessage', Yii::t('app', 'Really delete the file ?'));
?>
<div class='row'>
    <?php if ($fileGallery) {
        foreach ($fileGallery as $person_file) { ?>
    <div class='col-sm-3  h-25'>
        <?php $fileId = $person_file->id;
                $id = $person_file->person_id;
                echo Html::img(
                    ['attachment/send-thumbnail', 'fileId' => $fileId],
                    [
                        'class' => 'img-responsive img-thumbnail',
                        'data' => ['toggle' => 'modal', 'target' => '#imgModal'],
                        'name' => $fileId,
                    ]
                );
                echo $person_file->file_caption == "" ? Html::tag('p', " ") : Html::tag('p', $person_file->file_caption);
                echo "<button name=$fileId class='btn btn-sm btn-primary' data-toggle='modal' data-target='#imgModal'><span class='glyphicon glyphicon-fullscreen'></span></button>";
                echo Html::a("<span class='glyphicon glyphicon-trash'></span>", ['attachment/delete-attachment', 'fileId' => $fileId, 'id' => $id], ['class' => 'delete', 'id' => $fileId]);
                ?>
    </div>
    <?php    }
    }
    ?>
</div>

<?= LinkPager::widget([
    'pagination' => $pages,
]) ?>

<!-- Modal -->
<div id="imgModal" class="modal " role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <img class='img-responsive' id='modal-image' src='#'>
            </div>
        </div>

    </div>
</div>