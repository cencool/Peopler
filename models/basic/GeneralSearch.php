<?php

namespace app\models\basic;

use yii\base\Model;

class GeneralSearch extends Model {
    public $name;
    public $surname;
    public $place;
    public $gender;
    public $marital_status;
    public $maiden_name;
    public $address;
    public $note;
    public $item;

    public function rules() {
        return [
            [['name', 'surname', 'place', 'gender', 'marital_status', 'maiden_name', 'address', 'note', 'item'], 'safe']
        ];
    }
}