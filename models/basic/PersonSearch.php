<?php

namespace app\models\basic;

use yii\data\ActiveDataProvider;
use yii\base\Model;
use Yii;



class PersonSearch  extends Model {
    public $id;
    public $name;
    public $surname;
    public $place;
    public $gender;

    public function rules() {
        return [
            [['name', 'surname', 'place', 'gender'], 'safe'],
        ];
    }


    public function search($params, $pageLines) {

        $query = Person::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageLines,
            ]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'surname', $this->surname])
            ->andFilterWhere(['like', 'place', $this->place])
            ->andFilterWhere(['like', 'gender', $this->gender]);

        return $dataProvider;
    }
}
