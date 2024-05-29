<?php

namespace app\modules\v1\controllers;

use yii\rest\Controller;;

use app\models\basic\UploadFile;
use app\models\basic\PersonAttachment;
use yii\web\UploadedFile;
use yii\data\Pagination;
use yii\filters\auth\HttpBasicAuth;
use app\models\basic\Person;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

class AttachmentController extends Controller {

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


    public function actionUpdateCaption($id) {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $post = $request->post();
            $attachment = PersonAttachment::findOne($id);
            $person = null;
            if ($attachment) {
                $person = Person::findOne($attachment->person_id);
            }
            if ($person) {
                $attachment->file_caption = $post['caption'];
                if (!$attachment->save()) {
                    throw new \yii\web\ServerErrorHttpException('Attachment id:' . $id . 'error on save');
                }
            } else {
                throw new \yii\web\BadRequestHttpException();
            }
        } else {
            throw new \yii\web\BadRequestHttpException();
        }
    }

    public function actionReplace($id) {
        $attachment = PersonAttachment::findOne($id);
        $person = null;
        if ($attachment) {
            $person = Person::findOne($attachment->person_id);
        }
        if ($person) {
            $request = Yii::$app->request;
            $uploadModel = new UploadFile();
            if ($request->isPost && UploadedFile::getInstanceByName('attachmentFile')) {
                $post = $request->post();
                $uploadModel->imageFile = UploadedFile::getInstanceByName('attachmentFile');
                $timeStamp = (new \DateTime())->getTimestamp();
                $oldBaseFileName = $attachment->file_name;
                $fileBaseName = $person->surname .
                    '@@' .
                    $person->name .
                    '@@' .
                    $timeStamp .
                    '.' .
                    $uploadModel->imageFile->extension;
                $fileName = '@app/uploads/' . $fileBaseName;
                $oldFileName = '@app/uploads/' . $oldBaseFileName;
                $attachment->file_name = $fileBaseName;
                if (isset($post['caption'])) {
                    $attachment->file_caption = $post['caption'];
                }

                if ($uploadModel->replace($oldFileName, $fileName) && $attachment->save()) {
                    return $attachment;
                } else {
                    $uploadError = $uploadModel->getFirstError('imageFile');
                    throw new \yii\web\ServerErrorHttpException('Attachment id:' . $id . 'error: ' . $uploadError);
                }
            } else {
                throw new \yii\web\BadRequestHttpException();
            }
        } else {
            throw new \yii\web\BadRequestHttpException();
        }
    }

    public function actionAdd($id) {
        $person = Person::findOne($id);
        if ($person) {
            $attachment = new PersonAttachment();
            $attachment->person_id = $id;
            $request = Yii::$app->request;
            $uploadModel = new UploadFile();
            if ($request->isPost && UploadedFile::getInstanceByName('attachmentFile')) {
                $post = $request->post();
                $uploadModel->imageFile = UploadedFile::getInstanceByName('attachmentFile');
                $timeStamp = (new \DateTime())->getTimestamp();
                $fileBaseName = $person->surname .
                    '@@' .
                    $person->name .
                    '@@' .
                    $timeStamp .
                    '.' .
                    $uploadModel->imageFile->extension;
                $fileName = '@app/uploads/' . $fileBaseName;
                $attachment->file_name = $fileBaseName;
                if (isset($post['caption'])) {
                    $attachment->file_caption = $post['caption'];
                }

                if ($uploadModel->upload($fileName) && $attachment->save()) {
                    return $attachment;
                } else {
                    $uploadError = $uploadModel->getFirstError('imageFile');
                    throw new \yii\web\ServerErrorHttpException('Attachment id:' . $id . 'error: ' . $uploadError);
                }
            } else {
                throw new \yii\web\BadRequestHttpException();
            }
        } else {
            throw new \yii\web\BadRequestHttpException();
        }
    }

    public function actionSendThumbnail($fileId) {

        $fileRecord = PersonAttachment::findOne($fileId);
        if ($fileRecord && Person::findOne($fileRecord->person_id)) {
            $fileName = $fileRecord->file_name;
            $thumbnailPrefix = Yii::getAlias('@app/uploads/thumbnails/');
            $uploadPrefix = Yii::getAlias('@app/uploads/');
            if (file_exists($thumbnailPrefix . $fileName)) {
                // $response =  Yii::$app->response->sendFile($thumbnailPrefix . $fileName);
                Yii::$app->response->sendFile($thumbnailPrefix . $fileName);
            } else {
                // $response =  Yii::$app->response->sendFile($uploadPrefix . $fileName);
                Yii::$app->response->sendFile($uploadPrefix . $fileName);
            }
            // return $response;
        } else {
            throw new \yii\web\NotFoundHttpException('File id:' . $fileId . ' not found');
        }
    }

    public function actionShowAttachment($id) {
        $person = Person::findOne($id);
        if ($person) {

            $fileQuery = PersonAttachment::find()->where(['person_id' => $id]);
            $countQuery = clone $fileQuery;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => 8]);
            $fileGallery = $fileQuery->offset($pages->offset)->limit($pages->limit)->all();
            return $this->render('attachmentView', ['fileGallery' => $fileGallery, 'pages' => $pages, 'id' => $id]);
        } else $this->redirect(['person/index']); /// toto treba zmenit!!!
    }

    public function actionSendFile($fileId) {

        $fileRecord = PersonAttachment::findOne($fileId);
        if ($fileRecord && Person::findOne($fileRecord->person_id)) {
            $fileName = $fileRecord->file_name;
            $thumbnailPrefix = Yii::getAlias('@app/uploads/thumbnails/');
            $uploadPrefix = Yii::getAlias('@app/uploads/');
            if (file_exists($uploadPrefix . $fileName)) {
                $response =  Yii::$app->response->sendFile($uploadPrefix . $fileName);
            } else {
                $response =  Yii::$app->response->sendFile($thumbnailPrefix . $fileName);
            }
            return $response;
        } else {
            throw new \yii\web\NotFoundHttpException('File id:' . $fileId . ' not found');
        }
    }
    public function actionDelete($id) {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $attachmentRecord = PersonAttachment::findOne($id);
            $person = Person::findOne($attachmentRecord->person_id);
            if ($person) {
                $thumbnailPrefix = Yii::getAlias('@app/uploads/thumbnails/');
                $uploadPrefix = Yii::getAlias('@app/uploads/');
                $fileNameUpload = $uploadPrefix . $attachmentRecord->file_name;
                $fileNameThumbnail = $thumbnailPrefix . $attachmentRecord->file_name;
                if (file_exists($fileNameThumbnail)) {
                    unlink($fileNameThumbnail);
                }
                if (file_exists($fileNameUpload)) {
                    unlink($fileNameUpload);
                }
                $attachmentRecord->delete();
            } else {
                throw new \yii\web\NotFoundHttpException('File id:' . $id . ' not found');
            }
        }

        throw new \yii\web\BadRequestHttpException();
    }

    public function actionList($personId) {
        $person = Person::findOne($personId);
        if ($person) {
            $provider = new ActiveDataProvider(
                [
                    'query' => PersonAttachment::find()->where(["person_id" => $personId]),
                    'pagination' => [
                        'pageSize' => 0,
                    ],
                ]
            );
            return $provider;
        }
        return new ActiveDataProvider(['query' => new Query()]);
    }
}
