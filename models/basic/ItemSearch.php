<?php

namespace app\models\basic;

use yii\data\ActiveDataProvider;
use yii\base\Model;
use app\models\basic\Items;

class ItemSearch extends Model {
	public $item;

	public function rules() {
		return [
			[['item'], 'safe'],
		];
	}


	public function search($params, $personId, $pageLines) {

		$query = Items::find()->andWhere(['person_id' => $personId]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => $pageLines,
				'route' => 'item/edit-items',
			],
			'sort'=>['route'=>'item/edit-items'],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query->andFilterWhere(['like', 'item', $this->item]);

		return $dataProvider;
	}
}
