<?php

namespace app\models\basic;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\basic\RelationName;
use app\models\basic\PersonPhoto;
use Yii;

class Person extends ActiveRecord {
	public function rules() {
		return [
			[['name', 'surname', 'place', 'gender', 'owner'], 'safe'],
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

	/* 
	 * overriding find() to check ownership
	 * find() is also used by findOne()
	 */
	public static function find() {
		$userId = Yii::$app->user->id;
		if ($userId == 'admin')
			return parent::find();
		else
			return parent::find()->andWhere(['owner' => $userId]);
	}

	public static function tableName() {
		return 'person';
	}

	public function getDetail() {
		return $this->hasOne(PersonDetail::class, ['person_id' => 'id']);
	}

	public function getRelationsFromPersonRaw() {
		return $this->hasMany(PersonRelation::class, ['person_a_id' => 'id']);
	}

	public function getRelationsToPersonRaw() {
		return $this->hasMany(PersonRelation::class, ['person_b_id' => 'id']);
	}

	public function getPersonAttachments() {
		return $this->hasMany(PersonAttachment::class, ['person_id' => 'id']);
	}

	public function getPersonItems() {
		return $this->hasMany(Items::class, ['person_id' => 'id']);
	}

	public function getPersonPhoto() {
		return $this->hasOne(PersonPhoto::class, ['person_id' => 'id']);
	}

	public function getRelationsFromPerson() {
		$sql = <<<SQL
		select pa.owner as a_owner,pb.owner as b_owner,pr.id as relation_id,
		rn.relation_name as relation,pb.id as to_whom_id,
		concat(pb.surname,' ',pb.name) as relation_to_whom
		from person pa
		left join person_relation pr on pr.person_a_id = pa.id
		left join person pb on pb.id = pr.person_b_id
		left join relation_name rn on rn.id = pr.relation_ab_id
		where pr.person_a_id = :id
		SQL;
		$relationsDirect = Yii::$app->db->createCommand($sql, [':id' => $this->id])->queryAll();

		return $relationsDirect;
	}

	public function getRelationsToPerson() {
		$sql = <<<SQL
		select
		pa.owner as a_owner, pb.owner as b_owner, pr.id as relation_id, 
		case when rp.relation_ab = rn.relation_name then rp.relation_ba else rp.relation_ab end as relation,
		pb.id as to_whom_id, 
		concat(pb.surname,' ',pb.name) as relation_to_whom
		from person pa
		left join person_relation pr on pa.id = pr.person_b_id
		left join person pb on pb.id = pr.person_a_id
		left join relation_name rn on rn.id = pr.relation_ab_id
		left join relation_pair rp on (rp.relation_ab = rn.relation_name or rp.relation_ba = rn.relation_name)
		where (pa.id = :id 
		and ((pa.gender = rp.gender_a and pb.gender = rp.gender_b) or (pa.gender = rp.gender_b and pb.gender = rp.gender_a)))
		SQL;
		$relationsIndirect = Yii::$app->db->createCommand($sql, [':id' => $this->id])->queryAll();

		return $relationsIndirect;
	}

	/**
	 * @return array relations the person is involved in
	 */
	public function givenRelations() {

		$relationsFrom = $this->relationsFromPerson;
		$relationsTo = $this->relationsToPerson;
		// $relations keys: a_owner, b_owner, relation_id, relation, to_whom_id, relation_to_whom

		return ArrayHelper::merge($relationsFrom, $relationsTo);
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
				$personB = Person::findOne($personBid);
				if (!$personB) continue; // if person B has different owner or not exist skip
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
