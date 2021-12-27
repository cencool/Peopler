<?php

declare(strict_types=1);

namespace app\models\basic;

use yii\db\ActiveRecord;
use app\models\basic\Person;
use app\models\basic\PersonRelation;
use app\models\basic\PersonDetail;
use yii\imagine\Image;
use Yii;

class Undelete {

	public static function addUndeleteRecord(ActiveRecord $data): void {


		$undeleteRecord = [];
		$dataAttributes = [];

		// extract and store person	attributes
		foreach ($data->getAttributes() as $attr => $val) {

			if ($attr != 'id') {
				$dataAttributes[$attr] = $val;
			}
		}

		$undeleteRecord[] = ['person' => $dataAttributes];

		// extract and store person_detail	attributes
		$dataAttributes = [];

		foreach ($data->detail as $attr => $val) {
			if ($attr != 'id' && $attr != 'person_id') {
				$dataAttributes[$attr] = $val;
			}
		}

		$undeleteRecord[] = ['detail' => $dataAttributes];

		// extract and store person's relations From attributes
		$relations = $data->relationsFromPerson;

		foreach ($relations as $relation) {
			$dataAttributes = [];
			foreach ($relation as $attr => $val) {
				if ($attr != 'id' && $attr != 'person_a_id') {
					$dataAttributes[$attr] = $val;
				}
			}
			$undeleteRecord[] = ['relationFrom' => $dataAttributes];
		}


		// extract and store person's relations from attributes
		$relations = $data->relationsToPerson;

		foreach ($relations as $relation) {
			$dataAttributes = [];
			foreach ($relation as $attr => $val) {
				if ($attr != 'id' && $attr != 'person_b_id') {
					$dataAttributes[$attr] = $val;
				}
			}
			$undeleteRecord[] = ['relationTo' => $dataAttributes];
		}

		$attachments = $data->personAttachments;

		foreach ($attachments as $attachment) {
			$dataAttributes = [];
			foreach ($attachment as $attr => $val) {
				if ($attr != 'id' && $attr != 'person_id') {
					$dataAttributes[$attr] = $val;
				}
			}
			$pathFrom = Yii::getAlias('@app/uploads/');
			$pathTo = Yii::getAlias('@app/uploads/delete/');
			if (!file_exists($pathTo) || !is_dir($pathTo)) {
				mkdir($pathTo);
			}
			rename($pathFrom . $dataAttributes['file_name'], $pathTo . $dataAttributes['file_name']);
			if (file_exists($pathFrom . 'thumbnails/' . $dataAttributes['file_name'])) {
				unlink($pathFrom . 'thumbnails/' . $dataAttributes['file_name']);
			}

			$undeleteRecord[] = ['attachment' => $dataAttributes];
		}


		// this exercise with session is due fact that 
		// can't insert array directly to session var when using session component
		$session =  Yii::$app->session;
		$session['undelete'] = $undeleteRecord;
	}

	public static function undeletePerson() {

		$session = Yii::$app->session;
		if ($undeleteRecord = $session['undelete']) {

			$session->remove('undelete');

			foreach ($undeleteRecord as $dataBlock) {
				switch (key($dataBlock)) {
					case ('person'):
						// recover Person
						$person = new Person;
						$person->attributes = $dataBlock['person'];
						$person->save();
						$personId = $person->id;
						break;
					case ('detail'):
						// recover Detail
						$personDetail = new PersonDetail;
						$personDetail->attributes = $dataBlock['detail'];
						$personDetail->person_id = $personId;
						$personDetail->save();
						break;
					case ('relationFrom'):
						// recover relationFrom
						$personRelation = new PersonRelation;
						$personRelation->attributes = $dataBlock['relationFrom'];
						$personRelation->person_a_id = $personId;
						$personRelation->save();
						break;
					case ('relationTo'):
						// recover relationTo
						$personRelation = new PersonRelation;
						$personRelation->attributes = $dataBlock['relationTo'];
						$personRelation->person_b_id = $personId;
						$personRelation->save();
						break;
					case ('attachment'):
						// recover attachment
						$personAttachment = new PersonAttachment();
						$personAttachment->attributes = $dataBlock['attachment'];
						$personAttachment->person_id = $personId;
						$personAttachment->save();

						$fileName = $personAttachment['file_name'];
						$pathTo = Yii::getAlias('@app/uploads/');
						$pathFrom = Yii::getAlias('@app/uploads/delete/');
						rename($pathFrom . $fileName, $pathTo . $fileName);

						$thumbnailImage = Image::thumbnail($pathTo . $fileName, 400, null);
						$thumbnailImage->save($pathTo . 'thumbnails/' . $fileName);
						break;
				}
			}
		}
	}
}