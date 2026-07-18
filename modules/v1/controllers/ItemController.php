<?php

namespace app\modules\v1\controllers;

use app\models\basic\Items;
use app\models\basic\ItemSearch;
use yii\rest\Controller;;

use yii\filters\auth\HttpBasicAuth;
use app\models\basic\Person;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\BadRequestHttpException;

class ItemController extends Controller {

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
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        return $behaviors;
    }

    public function actions() {
        return [
            // this action is added to overcome CORS preflight check in browser
            // ActiveController does this automatically but we don't use it
            'options' => 'yii\rest\OptionsAction',
        ];
    }
    public function actionList($id) {
        $person = Person::findOne($id);
        if ($person) {
            $searchModel = new ItemSearch();
            $itemsDataProvider = $searchModel->searchView(Yii::$app->request->get(), $id, 10);
            return $itemsDataProvider;
        } else {
            throw new \yii\web\NotFoundHttpException('Person id:' . $id . ' not found');
        }
    }

    public function actionAdd() {
        $request = Yii::$app->request;
        if ($request->isPost) {

            $itemModel = new Items();

            if ($itemModel->load($_POST, '')) {
                $person = Person::findOne($itemModel->person_id);
                if ($person && $itemModel->save()) {
                    return $itemModel;
                } else {
                    throw new BadRequestHttpException();
                }
            } else {
                throw new BadRequestHttpException();
            }
        }
        throw new BadRequestHttpException();
    }

    public function actionUpdate() {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post();
            $itemModel = Items::findOne($post['id']);
            if ($itemModel && $itemModel->load($_POST, '')) {
                $person = Person::findOne($itemModel->person_id);
                if ($person && $itemModel->save()) {
                    return $itemModel;
                } else {
                    throw new BadRequestHttpException();
                }
            } else {
                throw new BadRequestHttpException();
            }
        }
        throw new BadRequestHttpException();
    }
    public function actionDelete() {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post();
            $itemModel = Items::findOne($post['id']);
            if ($itemModel && $itemModel->load($_POST, '')) {
                $person = Person::findOne($itemModel->person_id);
                if ($person && $itemModel->delete()) {
                    return new Items();
                } else {
                    throw new BadRequestHttpException();
                }
            } else {
                throw new BadRequestHttpException();
            }
        }
        throw new BadRequestHttpException();
    }
}