<?php

declare(strict_types=1);

namespace app\models\basic;

use yii\db\ActiveRecord;
use Yii;

class Undelete {

	public static function addUndeleteRecord(string $undeleteType,ActiveRecord $data): void {

		$session =  Yii::$app->session;

		$undeleteStack = $session['undeleteStack'];

		$undeleteRecord = [];
		$dataAttributes = [];

		switch ($undeleteType) {
			case ('person'):
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

				break;
			case ('relation'):
				foreach ($data as $attr => $val) {
					if ($attr != 'id') {
						$dataAttributes[$attr] = $val;
					}
				}
				$undeleteRecord[] = ['relation' => $dataAttributes];
				break;
		}

		$undeleteStack[] = $undeleteRecord;
		$session['undeleteStack'] = $undeleteStack;
	}
}

