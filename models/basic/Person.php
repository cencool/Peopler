<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\basic\RelationName;
use phpDocumentor\Reflection\Types\Array_;
use Yii;

class Person extends ActiveRecord {
	public function rules() {
		return [
			[['name', 'surname', 'place', 'gender'], 'safe'],
			[['name', 'surname', 'place', 'gender'], 'trim'],
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

	public function getPersonAttachments() {
		return $this->hasMany(PersonAttachment::class, ['person_id' => 'id']);
	}
	/**
	 * @return array relations the person is involved in
	 */
	public function givenRelations() {

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
		$personFromGender = $this->gender;
		foreach ($relationsTo as $record) {
			$personToGender = $record->person_a->gender;
			$relationTo = $record->relationName->relation_name;
			$relationRow['relation_id'] = $record->id;
			$relationRow['to_whom_id'] = $record->person_a_id;
			$relationRow['relation_to_whom'] = $record->person_a->surname . ' ' . $record->person_a->name;
			$relationRow['relation'] = RelationPair::relationComplement($personFromGender, $personToGender, $relationTo);

			$relations[] = $relationRow;
		}

		return $relations;
	}

	public function computedRelations() {
		$thisPersonRelations = $this->givenRelations();
		$computedRelations = [];
		$tokenChains = [
			'child' => [
				'child' => 'grandchild',
				'sibling' => 'nephew',
				'partner' => 'child',
				'parent' => 'sibling'
			],
			'sibling' => [
				'child' => 'child',
				'sibling' => 'sibling',
				'partner' => 'sibling-in-law',
				'parent' => 'uncle',
			],
			'partner' => [
				'child' => 'child-in-law',
				'sibling' => 'sibling-in-law',
				'partner' => 'partner',
				'parent' => 'parent',
			],
			'parent' => [
				'child' => 'partner',
				'sibling' => 'parent',
				'partner' => 'parent-in-law',
				'parent' => 'grandparent',
			],
		];
		$currentTokenChain = [];
		$currentRelations = $thisPersonRelations;
		do {
			$newRelations = [];

			foreach ($currentRelations as $relationA) {
				$tokenA = RelationName::find()->where(
					[
						'and',
						['gender' => $this->gender],
						['relation_name' => $relationA['relation']]
					]
				)->one()->token;
				$currentTokenChain[] = $tokenA;

				$personBid = $relationA['to_whom_id'];
				$personB = Person::find()->where(['id' => $personBid])->one();
				$personBrelations = $personB->givenRelations();
				foreach ($personBrelations as $relationB) {
					if ($relationB['to_whom_id'] != $this->id) {
						$tokenB = (RelationName::find()->where([
							'and',
							['relation_name' => $relationB['relation']],
							['gender' => $personB->gender]
						])->one())->token;
						$currentTokenChain[] = $tokenB;
						$index = $currentTokenChain[0] . '.' . $currentTokenChain[1];
						if ($resultToken = ArrayHelper::getValue($tokenChains, $index)) {
							$relation = RelationName::find()->where([
								'and',
								['token' => $resultToken],
								['gender' => $this->gender]
							])->one()['relation_name'];
							$newRelations[] = [
								'relation_id' => -1,
								'to_whom_id' => $relationB['to_whom_id'],
								'relation_to_whom' => $relationB['relation_to_whom'],
								'relation' => $relation,
							];
							if ($this->checkRelationExists(end($newRelations), $thisPersonRelations)) {
								array_pop($newRelations);
							} else if ($this->checkRelationExists(end($newRelations), $computedRelations)) {
								array_pop($newRelations);
							} else {
								$computedRelations[] = end($newRelations);
							}
						}
						array_pop($currentTokenChain);
					}
				}
				$currentTokenChain = [];
			}
			$currentRelations = $newRelations;
		} while (count($newRelations) > 0);
		return $computedRelations;
	}

	public function relations() {
		$given =  $this->givenRelations();
		$computed = $this->computedRelations();
		$relations = ArrayHelper::merge($given, $computed);

		// translation of relations
		foreach ($relations as $key => $value) {
			$value['relation'] = $this->gender == 'm' ? Yii::t('app-m', $value['relation']) : Yii::t('app-f', $value['relation']);
			$relations[$key] = $value;
		}
		return $relations;
	}

	public function checkRelationExists(array $relationToCheck, array $relations) {
		foreach ($relations as $relation) {
			$a = $relationToCheck['to_whom_id'] == ArrayHelper::getValue($relation, 'to_whom_id');
			$b = $relationToCheck['relation'] == ArrayHelper::getValue($relation, 'relation');
			if ($a && $b) {
				return true;
			}
		}
		return false;
	}
}