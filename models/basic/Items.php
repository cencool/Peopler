<?php
declare(strict_types=1);

namespace app\models\basic;

use yii\db\ActiveRecord;

class Items extends ActiveRecord {

    public function rules() {
        return [
            [['person_id','item'],'safe'],
        ];
    }

}

?>

