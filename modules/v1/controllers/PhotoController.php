<?php

namespace app\modules\v1\controllers;

use yii\rest\Controller;
use yii\filters\auth\HttpBasicAuth;
use app\models\basic\PersonPhoto;
use app\models\basic\PhotoUpload;
use app\modules\v1\models\Person;
use Yii;
use yii\web\HttpException;
use yii\web\UploadedFile;

class PhotoController extends Controller {
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
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    public function actionSendPhoto($id) {
        PersonPhoto::sendPersonPhoto($id);
    }

    public function actionReceivePhoto() {
        // $id = Yii::$app->request->post('id');
        // $receivedImage = UploadedFile::getInstanceByName('personPhoto');
        // return 'Photo received';
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
            $file = UploadedFile::getInstanceByName('personPhoto');
            if ($file->error > 0) {
                throw new \yii\web\UnprocessableEntityHttpException();
            }
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
                return ["photo_upload" => "succes"];
            }
        } else {

            throw new \yii\web\BadRequestHttpException();
        }
    }
}
