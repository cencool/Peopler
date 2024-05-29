<?php

namespace app\modules\v1\controllers;

use app\models\basic\Person;
use app\modules\v1\models\PersonDetail;
use yii\rest\Controller;
use yii\filters\auth\HttpBasicAuth;

class PersonDetailController extends Controller {


    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
        ];
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Headers' => ['X-Requested-With', 'Authorization'],
                'Access-Control-Expose-Headers' => [
                    'x-pagination-current-page',
                    'x-pagination-page-count',
                    'x-pagination-total-count',
                    'x-pagination-per-page'
                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }
    // public $modelClass = 'app\models\basic\PersonDetail';
    public function actions() {
        return [
            // this action is added to overcome CORS preflight check in browser
            // ActiveController does this automatically but we use rest Controller
            'options' => 'yii\rest\OptionsAction',
        ];
    }

    public function actionView($id) {
        $person = Person::findOne($id);
        if ($person) {
            $personDetails = PersonDetail::findOne(['person_id' => $id]);
            if ($personDetails == null) {
                throw new \yii\web\NotFoundHttpException('Person $id details not available');
            }
            return $personDetails;
        }
        throw new \yii\web\NotFoundHttpException('Person id:' . $id . ' not found');
    }

    public function actionCreate() {
        $r = \Yii::$app->request->post();
        $personDetail = new PersonDetail();
        $personDetail->load($r);
        if ($personDetail->save()) {
            return $personDetail;
        } else {
            throw new \yii\web\BadRequestHttpException();
        }
    }

    public function actionUpdate($id) {
        $r = \Yii::$app->request->post();
        $person = Person::findOne($id);
        if ($person) {
            $personDetail = PersonDetail::findOne(['person_id' => $id]);
            $personDetail->load($r);
            if ($personDetail->save()) {
                return $personDetail;
            } else {
                throw new \yii\web\BadRequestHttpException();
            }
        }
        throw new \yii\web\NotFoundHttpException('Person $id  not available');
    }
}
