<?php

namespace app\models\basic;

use yii\db\ActiveRecord;

class RelationPair extends ActiveRecord
{
    public static function tableName()
    {
        return 'relation_pair';
    }

    public function rules() {
        return [
            [['gender_a', 'gender_b', 'relation_ab','relation_ba'],'safe'],
        ];
    }

	public static function relationsList($gender) {

		// prepare list of available relations for dropdownlist
		$relations  = RelationPair::find()->select(['relation_ab'])->where(['gender_a' => $gender])->asArray()->all();
		$relationsList = [];
		// exclude duplicates from list
		foreach ($relations as $relation) {
			$duplicate = false;
			foreach ($relationsList as $relationOut) {
				if ($relationOut == $relation['relation_ab']) {
					$duplicate = true;
				}
			}
			if (!$duplicate) {
				$relationsList[$relation['relation_ab']] =  $relation['relation_ab'];
			}
		}
		$relations  = RelationPair::find()->select(['relation_ba'])->where(['gender_b' => $gender])->asArray()->all();
		// exclude duplicates from list
		foreach ($relations as $relation) {
			$duplicate = false;
			foreach ($relationsList as $relationOut) {
				if ($relationOut == $relation['relation_ba']) {
					$duplicate = true;
				}
			}
			if (!$duplicate) {
				$relationsList[$relation['relation_ba']] = $relation['relation_ba'];
			}
		}

		return $relationsList;
	}

	public static function relationFromComplement($personFromGender, $personToGender, $relationFrom) {

			$relationPairQuery = RelationPair::find();
			$relationPairs = $relationPairQuery->where(
				[
					'or',
					['gender_a' => $personToGender, 'gender_b' => $personFromGender],
					['gender_a' => $personFromGender, 'gender_b' => $personToGender]
				]
			)->andWhere(
				[
					'or',
					['gender_a' => $personToGender, 'relation_ba' => $relationFrom],
					['gender_b' => $personToGender, 'relation_ab' => $relationFrom]
				]
			)
				->asArray()->all();

			$relationFromComplement= ($relationPairs[0]['relation_ab'] == $relationFrom) ?
				$relationPairs[0]['relation_ba'] : $relationPairs[0]['relation_ab'];

			return $relationFromComplement;
	}

	public static function relationToComplement($personFromGender, $personToGender, $relationTo) {

			$relationPairQuery = RelationPair::find();
			$relationPairs = $relationPairQuery->where(
				[
					'or',
					['gender_a' => $personToGender, 'gender_b' => $personFromGender],
					['gender_a' => $personFromGender, 'gender_b' => $personToGender]
				]
			)->andWhere(
				[
					'or',
					['gender_a' => $personToGender, 'relation_ab' => $relationTo],
					['gender_b' => $personToGender, 'relation_ba' => $relationTo]
				]
			)
				->asArray()->all();

			$relationToComplement= ($relationPairs[0]['relation_ab'] == $relationTo) ?
				$relationPairs[0]['relation_ba'] : $relationPairs[0]['relation_ab'];

			return $relationToComplement;
	}

}
