<?php

namespace app\modules\v1\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\basic\RelationName;
use app\models\basic\PersonPhoto;
use app\models\basic\PersonDetail;
use app\models\basic\PersonRelation;
use app\models\basic\PersonAttachment;
use app\models\basic\KinshipResolver;
use app\models\basic\Items;
use Yii;

class Person extends ActiveRecord {
    public function rules() {
        return [
            [['name', 'surname', 'place', 'gender', 'owner',], 'safe'],
            [['name', 'surname', 'place', 'gender'], 'trim'],
            [['surname', 'gender'], 'required'],
            [['name', 'surname', 'place'], 'string', 'max' => 50],
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

    /*
	 * overriding find() to check ownership
	 * find() is also used by findOne()
	 */
    public static function find() {
        $userId = Yii::$app->user->id;
        if ($userId == 'admin')
            return parent::find();
        else
            return parent::find()->andWhere(['owner' => $userId,]);
    }

    public function beforeSave($insert) {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->owner = Yii::$app->user->id;
        } else {
            $originalOwner = $this->getOldAttribute('owner');
            $this->owner = $originalOwner;
        }
        return true;
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
        $params = [':id' => $this->id];
        if (Yii::$app->user->id !== 'admin') {
            // non-admin users only see relations to persons they also own
            $sql .= ' and pb.owner = :owner';
            $params[':owner'] = $this->owner;
        }
        $relationsDirect = Yii::$app->db->createCommand($sql, $params)->queryAll();

        return $relationsDirect;
    }

    public function getRelationsToPerson() {
        $genderedSql = <<<SQL
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
        // unknown-gender ('?') persons have no gendered inverse in relation_pair,
        // so fall back to the neutral complement token (child, sibling, godchild…).
        $neutralSql = <<<SQL
		select distinct
		pa.owner as a_owner, pb.owner as b_owner, pr.id as relation_id,
		rn_inv.token as relation,
		pb.id as to_whom_id,
		concat(pb.surname,' ',pb.name) as relation_to_whom
		from person pa
		join person_relation pr on pa.id = pr.person_b_id
		join person pb on pb.id = pr.person_a_id
		join relation_name rn on rn.id = pr.relation_ab_id
		join relation_pair rp on (rp.relation_ab = rn.relation_name or rp.relation_ba = rn.relation_name)
		join relation_name rn_inv on rn_inv.relation_name = case when rp.relation_ab = rn.relation_name then rp.relation_ba else rp.relation_ab end
		where pa.id = :id
		SQL;
        $sql = $this->gender === '?' ? $neutralSql : $genderedSql;
        $params = [':id' => $this->id];
        if (Yii::$app->user->id !== 'admin') {
            // non-admin users only see relations to persons they also own
            $sql .= ' and pb.owner = :owner';
            $params[':owner'] = $this->owner;
        }
        return Yii::$app->db->createCommand($sql, $params)->queryAll();
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

    /**
     * Implicit relations inferred from the graph of explicit relations.
     * Delegates to {@see KinshipResolver} (BFS over the owner's relation graph).
     * Computed rows are flagged with relation_id = -1.
     */
    public function computedRelations() {
        return (new KinshipResolver($this->id, $this->owner, $this->gender))->compute();
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
}
