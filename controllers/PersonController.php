<?php

declare(strict_types=1);

namespace app\controllers;

use yii\web\Controller;
use app\models\basic\Person;
use app\models\basic\PersonSearch;
use app\models\basic\PersonDetail;
use app\models\basic\RelationSearch;
use app\models\basic\UploadFile;
use app\models\basic\PersonFile;
use yii\web\UploadedFile;
use app\models\basic\Undelete;
use yii\filters\AccessControl;
use yii\data\Pagination;
use Yii;

class PersonController extends Controller {

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

	public function actionIndex() {
		$searchModel = new PersonSearch();
		$provider = $searchModel->search(Yii::$app->request->get(), 10);


		return $this->render('index', ['provider' => $provider, 'searchModel' => $searchModel]);
	}

	public function actionView($id) {

		$person = Person::findOne($id);
		$searchModel = new RelationSearch();
		$AttachmentCount = count(PersonFile::find()->where(['person_id' => $id])->all());
		$provider = $searchModel->search(Yii::$app->request->get());
		return $this->render('personView', [
			'model' => $person,
			'provider' => $provider,
			'attachmentCount' => $AttachmentCount,
			'searchModel' => $searchModel
		]);
	}

	public function actionNewPerson() {
		$person = new Person;
		$personDetail = new PersonDetail;
		if ($person->load($_POST) && $person->getDirtyAttributes() && $person->save()) {
			if ($personDetail->load($_POST) && $personDetail->getDirtyAttributes()) {
				$personDetail->link('person', $person);
			}

			Yii::$app->session->setFlash('personAdded', Yii::t('app', 'Person') . ' ' . $person->name . ' ' . $person->surname . ' ' . Yii::t('app', 'added'));
			return $this->redirect(['update', 'id' => $person->id]);
		};

		$searchModel = new RelationSearch();
		$provider = $searchModel->search(Yii::$app->request->get());

		return $this->render('personUpdate', [
			'person' => $person,
			'personDetail' => $personDetail,
			'provider' => $provider,
			'searchModel' => $searchModel,
		]);
	}

	public function actionUpdate($id = null) {
		$AttachmentCount = count(PersonFile::find()->where(['person_id' => $id])->all());

		if (($id != null) && ($person = Person::findOne($id))) {

			if (!$personDetail = $person->detail) {
				$personDetail = new PersonDetail;
			}

			// if change in person and save successfull
			if ($person->load($_POST) && $person->getDirtyAttributes()) {

				if ($person->save()) {
					Yii::$app->session->setFlash('personUpdated', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				}
			};

			// if change in detail save it using 'link' method for relation
			if ($personDetail->load($_POST) && $personDetail->getDirtyAttributes()) {
				Yii::$app->session->setFlash('personUpdated', 'Person ' . $person->name . ',' . $person->surname . ' updated');
				$personDetail->link('person', $person);
			}

			$searchModel = new RelationSearch();
			$provider = $searchModel->search(Yii::$app->request->get());


			return $this->render('personUpdate', [
				'person' => $person,
				'personDetail' => $personDetail,
				'searchModel' => $searchModel,
				'provider' => $provider,
				'attachmentCount'=>$AttachmentCount,
			]);
		} else {

			return $this->redirect(['index']);
		}
	}

	/**
	 * deletes person from database,  related records deleted as cascade
	 * @param integer $id person id to be deleted
	 */

	public function actionDelete($id) {

		$person = Person::findOne($id);
		$session = Yii::$app->session;

		Undelete::addUndeleteRecord($person);

		try {
			$person->delete();
			Yii::$app->session->setFlash('personDeleted', Yii::t('app', 'Person') . ' ' . $person->name . ' ' . $person->surname . ' ' . Yii::t('app', 'deleted'));
		} catch (\Exception $ex) {

			$session->setFlash('personDeleteError', $ex->getMessage());
		}
		return $this->redirect(['index']);
	}

	public function actionUndelete() {
		Undelete::undeletePerson();
		return $this->redirect(['index']);
	}

	public function actionUpload($id) {
		$fileQuery = PersonFile::find()->where(['person_id' => $id]);
		$countQuery = clone $fileQuery;
		$pages = new Pagination(['totalCount'=>$countQuery->count(),'pageSize'=>8]);
		$fileGallery = $fileQuery->offset($pages->offset)->limit($pages->limit)->all();

		$uploadModel = new UploadFile();
		$personFile = new PersonFile();
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
				$session->setFlash('uploadError', Yii::t('app', 'Upload Failed'));
				$this->redirect(['upload', 'id' => $id]); // redirected to avoid re-submission on page reload (PostRedirectGet)
				Yii::$app->end();
			}
		}

		return $this->render('uploadForm', [
			'uploadModel' => $uploadModel,
			'personFile' => $personFile,
			'fileGallery' => $fileGallery,
			'pages' => $pages
		]);
	}

	public function actionSendThumbnail($fileName) {
		$prefix = Yii::getAlias('@app/uploads/');
		$response =  Yii::$app->response->sendFile($prefix . $fileName);
		return $response;
	}

	public function actionShowAttachment($id) {
		$fileQuery = PersonFile::find()->where(['person_id' => $id]);
		$countQuery = clone $fileQuery;
		$pages = new Pagination(['totalCount'=>$countQuery->count(),'pageSize'=>8]);
		$fileGallery = $fileQuery->offset($pages->offset)->limit($pages->limit)->all();
		return $this->render('attachmentView', ['fileGallery' => $fileGallery, 'pages' => $pages]);
	}
}

