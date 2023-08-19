<?php

declare(strict_types=1);
/**
 * This controller is related to actions performed on Person Photo
 */


namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use yii\filters\AccessControl;
use app\models\basic\PersonPhoto;
use app\models\basic\PhotoUpload;
use app\models\basic\UploadFile;
use yii\web\UploadedFile;
use Yii;



class PhotoController extends Controller {

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
    /**
     * Managing correct language selection
     */
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
    public function actionUpload($id = null) {
        $person = Person::findOne($id);
        if ($person) {
            $photoUpload = new PhotoUpload();
            if (Yii::$app->request->isPost) {
                $personPhoto = PersonPhoto::find()->where(['person_id' => $id])->one();
                if (!$personPhoto) {
                    $personPhoto = new PersonPhoto();
                    $personPhoto->person_id = $person->id;
                    $personPhoto->file_name = 'default';
                    $personPhoto->save();
                }
                $photoUpload->imageFile = UploadedFile::getInstance($photoUpload, 'imageFile');
                $timeStamp = (new \DateTime())->getTimestamp();
                $fileName = $person->name . '@@' . $person->surname . '@@' . $timeStamp . '.' . $photoUpload->imageFile->extension;
                if ($photoUpload->upload($fileName)) {
                    $uploadDirAlias = '@app/uploads/person_photo/';
                    $uploadDir = Yii::getAlias($uploadDirAlias);
                    if (file_exists($uploadDir . $personPhoto->file_name)) {
                        unlink($uploadDir . $personPhoto->file_name);
                    }
                    $personPhoto->file_name = $fileName;
                    $personPhoto->save();
                    $this->redirect(['person/update', 'id' => $id]);
                }
            } else {
                return $this->render('photoUpload', ['id' => $id, 'photoUpload' => $photoUpload]);
            }
        } else {
            return $this->redirect(['site/index']);
        }
    }
    public function actionSendPhoto($personId) {
        $person = Person::findOne($personId);
        if ($person) {
            $personPhoto = PersonPhoto::find()->where(['person_id' => $personId])->one();
            if ($personPhoto) {
                $photoName = $personPhoto->file_name;
                $pathPrefix = Yii::getAlias('@app/uploads/person_photo/');
                $photoFileName = $pathPrefix . $photoName;
                $response =  Yii::$app->response->sendFile($photoFileName);
            } else {
                $defaultPhoto = Yii::getAlias('@app/web/') . 'avatar.svg';
                $response =  Yii::$app->response->sendFile($defaultPhoto);
            }
        } else {
            $defaultPhoto = Yii::getAlias('@app/web/') . 'avatar.svg';
            $response =  Yii::$app->response->sendFile($defaultPhoto);
        }
    }

    public function actionReceive() {

        $model = new PhotoUpload();
        $id = Yii::$app->request->post('id');
        $person = Person::findOne($id);
        if ($person) {
            $personPhoto = PersonPhoto::find()->where(['person_id' => $id])->one();
            if (!$personPhoto) {
                $personPhoto = new PersonPhoto();
                $personPhoto->person_id = $person->id;
                $personPhoto->file_name = 'default';
                $personPhoto->save();
            }
            $file = UploadedFile::getInstanceByName('subor');
            $timeStamp = (new \DateTime())->getTimestamp();
            $fileName = $person->name . '@@' . $person->surname . '@@' . $timeStamp . '.' . $file->extension;
            $model->imageFile = $file;
            if ($model->upload($fileName)) {
                $uploadDirAlias = '@app/uploads/person_photo/';
                $uploadDir = Yii::getAlias($uploadDirAlias);
                if (file_exists($uploadDir . $personPhoto->file_name)) {
                    unlink($uploadDir . $personPhoto->file_name);
                }
                $personPhoto->file_name = $fileName;
                $personPhoto->save();
                //Yii::$app->response->content = 'ulozene';
                Yii::$app->session->setFlash('info', 'Person photo updated!');
                Yii::$app->response->content = 'success';

                //return $this->render(['person/update', 'id' => $id]);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Incorrect person ID provided!');
            Yii::$app->response->content = 'failure';
            //return $this->render(['person/index']);
        }
    }
}
