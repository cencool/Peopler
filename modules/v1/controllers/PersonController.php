<?php

namespace app\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;

class PersonController extends ActiveController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
        ];
        return $behaviors;
    }
    public $modelClass = 'app\modules\v1\models\Person';

    public function actions() {
        $actions = parent::actions();
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => 'app\models\basic\PersonSearch',
        ];

        return $actions;
    }
}
