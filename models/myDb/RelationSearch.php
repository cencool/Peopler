<?php

namespace app\models\myDb;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use app\models\myDb\Person;

class RelationSearch extends Model {

	public $relation_id;
	public $relation;
	public $relation_to_whom;

	public function rules() {

		return [
			[['relation_id', 'relation', 'relation_to_whom'], 'safe'],
		];
	}

	public function search($params = null) {
		$relations_out=[];
		if ($params) {
			$personId = $params['id'];
			$person = Person::findOne($personId);
			$relations = $person->relations();
			$relations_out = [];
			$this->load($params);
			// here the $relations should be filtered according to filter parameters
			// only row matching all criteria will be added to $relations_out
			foreach ($relations as  $relation) {
				$found = true;
				foreach ($this as $key => $value) {
					$needle = strtolower($value);
					$haystack = strtolower($relation[$key]);
					if (!str_contains($haystack, $needle)) {
						$found = false;
					}
				}
				if ($found) {
					$relations_out[] = $relation;
				}
			}
		}

		$provider = new ArrayDataProvider([
			'allModels' => $relations_out,
			'sort' => [
				'attributes' => ['relation_id', 'relation', 'relation_to_whom'],
			],
			'pagination' => [
				'pageSize' => 5,
			],
		]);

		return $provider;
	}
}
