<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\basic\Person;
use app\models\basic\Items;
use yii\data\ActiveDataProvider;

class ItemController extends Controller {

    public function behaviors() {

        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]

                ]
            ]
        ];
    }

    public function beforeAction($action) {

        $session = Yii::$app->session;
        $request = Yii::$app->request;
        if ($request->get('lng') == 'sk') {
            Yii::$app->language = 'sk-SK';
            $session['language'] = 'sk-SK';
        } else if ($request->get('lng') == 'en') {
            Yii::$app->language = 'en-US';
            $session['language'] = 'en-US';
        } else if ($session['language']) {
            Yii::$app->language = $session['language'];
        } else {
            Yii::$app->language = 'en-US';
        }


        if (!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }

    public function actionShowItems($id) {
        $userId = Yii::$app->user->id;
        $personOwner = Person::findOne($id)->owner;
        if (($userId == $personOwner) || ($userId == 'admin')) {
            $dataProvider = new ActiveDataProvider([
                'query'=>Items::find()->where(['person_id'=>$id]),
            ]);
            return $this->render('itemView',['dataProvider'=>$dataProvider]);
        } else $this->redirect(['person/index']);
    }
}
