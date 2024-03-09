<?php

namespace app\controllers;

use app\models\basic\PersonRest;
use yii\rest\ActiveController;

class RestController extends ActiveController {

    public $modelClass = 'app\models\basic\PersonRest';

    public function actionSearch($id) {
        return (new PersonRest())->findOne($id);
    }
}
