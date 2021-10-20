<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use Yii;

class Person extends ActiveRecord {
	public function rules() {
		return [
			[['name', 'surname', 'place', 'gender'], 'safe'],
			[['surname', 'gender'], 'required'],
			[['name', 'surname', 'place'], 'string', 'max' => 20],
			['gender', 'string', 'max' => 1],
			['gender', 'match', 'pattern' => '@\bm\b|\bf\b|\?@', 'message' => Yii::t('app', 'Gender undefined')],
		];
	}
	public function attributeLabels() {
		return [
			'name' => Yii::t('app', 'Name'),
			'surname' => Yii::t('app', 'Surname'),
			'gender' => Yii::t('app', 'Gender'),
			'place' => Yii::t('app', 'Place'),
		];
	}

	public static function tableName() {
		return 'person';
	}

	public function getDetail() {
		return $this->hasOne(PersonDetail::class, ['person_id' => 'id']);
	}

	public function getRelationsFromPerson() {
		return $this->hasMany(PersonRelation::class, ['person_a_id' => 'id']);
	}

	public function getRelationsToPerson() {
		return $this->hasMany(PersonRelation::class, ['person_b_id' => 'id']);
	}
	/**
	 * @return array relations the person is involved in
	 */
	public function relations() {

		$relationsFrom = $this->relationsFromPerson;
		// $relations fields: relation_id, relation, to_whom, to_whom_id
		$relations = [];

		foreach ($relationsFrom as $record) {
			$relationRow['relation_id'] = $record->id;
			$relationRow['relation'] = $record->relationName->relation_name;

			$relationRow['to_whom_id'] = $record->person_b_id;
			$relationRow['relation_to_whom'] = $record->person_b->surname . ' ' . $record->person_b->name;
			$relations[] = $relationRow;
		}

		unset($relationRow);
		$relationsTo = $this->relationsToPerson;
		$relationPairQuery = RelationPair::find();
		$personFromGender = $this->gender;
		foreach ($relationsTo as $record) {
			$personToGender = $record->person_a->gender;
			$relationTo = $record->relationName->relation_name;
			$relationRow['relation_id'] = $record->id;
			$relationRow['to_whom_id'] = $record->person_a_id;
			$relationRow['relation_to_whom'] = $record->person_a->surname . ' ' . $record->person_a->name;
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

			$relationRow['relation'] = ($relationPairs[0]['relation_ab'] == $relationTo) ?
				$relationPairs[0]['relation_ba'] : $relationPairs[0]['relation_ab'];

			$duplicate = false;
			foreach ($relations as $relation) {
				if ($relation['to_whom_id'] == $relationRow['to_whom_id'] && $relation['relation'] == $relationRow['relation']) {
					$duplicate = true;
				}
			}
			if (!$duplicate) {
				$relations[] = $relationRow;
			}
		}
		// translation of relations
		foreach ($relations as $key => $value) {
			$value['relation'] = $this->gender == 'm' ? Yii::t('app-m', $value['relation']) : Yii::t('app-f', $value['relation']);
			$relations[$key] = $value;
		}
		return $relations;
	}
}
