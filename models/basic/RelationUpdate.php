<?php

namespace app\models\basic;

use  yii\base\Model;

class RelationUpdate extends Model {

	public $person_a_id;
	public $relation_ab_id;
	public $person_b_id;
	public $surname;
	public $name;

	public function rules() {
		return [
			[['person_a_id', 'person_b_id', 'relation_ab_id', 'surname', 'name'], 'safe'],
			[['person_a_id', 'person_b_id', 'relation_ab_id'], 'filter', 'filter' => 'intval'],
		];
	}  	

}
