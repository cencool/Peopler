<?php

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\PersonDetail;
use app\models\basic\RelationSearch;
use app\models\basic\UploadFile;
use app\models\basic\PersonAttachment;
use yii\web\UploadedFile;
use app\models\basic\Undelete;
use yii\filters\AccessControl;
use yii\data\Pagination;
use Yii;

class AttachmentController extends Controller {

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

    public function actionUpload($id) {
        $fileQuery = PersonAttachment::find()->where(['person_id' => $id]);
        $countQuery = clone $fileQuery;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 8]);
        $fileGallery = $fileQuery->offset($pages->offset)->limit($pages->limit)->all();

        $uploadModel = new UploadFile();
        $personFile = new PersonAttachment();
        $person = Person::findOne($id);
        $session = Yii::$app->session;

        if (Yii::$app->request->isPost) {
            $uploadModel->imageFile = UploadedFile::getInstance($uploadModel, 'imageFile');
            $timeStamp = (new \DateTime())->getTimestamp();
            $fileBaseName = $person->surname .
                '@@' .
                $person->name .
                '@@' .
                $timeStamp .
                '.' .
                $uploadModel->imageFile->extension;
            $fileName = '@app/uploads/' . $fileBaseName;
            $personFile->load($_POST); // to load image caption
            $personFile->person_id = $id;
            $personFile->file_name = $fileBaseName;

            if ($uploadModel->upload($fileName) && $personFile->save()) {
                $session->setFlash('uploadSuccess', Yii::t('app', 'Upload Successful'));
                $this->redirect(['upload', 'id' => $id]); // redirected to avoid re-submission on page reload (PostRedirectGet)
                Yii::$app->end();
            } else {
                $uploadError = $uploadModel->getFirstError('imageFile');
                $session->setFlash('uploadError', Yii::t('app', 'Upload Failed' . ':' . $uploadError));
                $this->redirect(['upload', 'id' => $id]); // redirected to avoid re-submission on page reload (PostRedirectGet)
                Yii::$app->end();
            }
        }

        return $this->render('uploadForm', [
            'uploadModel' => $uploadModel,
            'personFile' => $personFile,
            'fileGallery' => $fileGallery,
            'pages' => $pages,
            'person' => $person,
        ]);
    }
    public function actionSendThumbnail($fileId) {

        $fileName = PersonAttachment::findOne($fileId)->file_name;
        $thumbnailPrefix = Yii::getAlias('@app/uploads/thumbnails/');
        $uploadPrefix = Yii::getAlias('@app/uploads/');
        if (file_exists($thumbnailPrefix . $fileName)) {
            $response =  Yii::$app->response->sendFile($thumbnailPrefix . $fileName);
        } else {
            $response =  Yii::$app->response->sendFile($uploadPrefix . $fileName);
        }
        return $response;
    }

    public function actionShowAttachment($id) {

        $fileQuery = PersonAttachment::find()->where(['person_id' => $id]);
        $countQuery = clone $fileQuery;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 8]);
        $fileGallery = $fileQuery->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('attachmentView', ['fileGallery' => $fileGallery, 'pages' => $pages, 'id' => $id]);
    }

    public function actionSendFile($fileId) {

        $fileName = PersonAttachment::findOne($fileId)->file_name;
        $thumbnailPrefix = Yii::getAlias('@app/uploads/thumbnails/');
        $uploadPrefix = Yii::getAlias('@app/uploads/');
        if (file_exists($uploadPrefix . $fileName)) {
            $response =  Yii::$app->response->sendFile($uploadPrefix . $fileName);
        } else {
            $response =  Yii::$app->response->sendFile($thumbnailPrefix . $fileName);
        }
        return $response;
    }
}