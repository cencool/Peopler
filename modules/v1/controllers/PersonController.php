<?php

namespace app\modules\v1\controllers;

use app\models\basic\GeneralSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;

class PersonController extends ActiveController {


    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::class,
        ];
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Headers' => ['X-Requested-With', 'Authorization'],
                'Access-Control-Expose-Headers' => [
                    'x-pagination-current-page',
                    'x-pagination-page-count',
                    'x-pagination-total-count',
                    'x-pagination-per-page'
                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    public $modelClass = 'app\modules\v1\models\Person';

    /**
     * Configures the default REST actions for the Person controller.
     * Specifically, it adds data filtering capability to the 'index' action
     * using ActiveDataFilter. This allows API clients to filter person records
     * using query parameters based on the PersonSearch model attributes.
     * 
     * Note: The searchModel must have appropriate validation rules for filtering.
     * While Person model could be used, its strict validation rules (required fields,
     * pattern matching) would cause 422 errors during filtering. PersonSearch is
     * better suited as it only has 'safe' rules, allowing partial/optional search
     * criteria without validation errors.
     *
     * @return array The configured actions
     */
    public function actions() {
        $actions = parent::actions();
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => 'app\models\basic\PersonSearch',
        ];

        return $actions;
    }



    public function actionSearch() {
        $request = Yii::$app->request;
        if ($request->isPost) {
            // $_POST does not contain query as we don't use form but body
            $personAttributes = ["id", "name", "surname", "place", "gender", "owner"];
            $post = $request->post();
            $model = new GeneralSearch();
            if ($model->load($post['GeneralSearch'], $formName = '')) {
                $sort = [
                    "params" => ["sort" => $post["sort"]],
                    "attributes" => $personAttributes,
                ];

                $query = new \yii\db\Query();
                $userId = Yii::$app->user->id;
                if ($userId == 'admin') {
                    $query = $query->select(['person.id', 'name', 'surname', "place", "gender", "owner"])
                        ->distinct()
                        ->from('person')
                        ->join('LEFT JOIN', 'person_detail', 'person_detail.person_id = person.id')
                        ->join('LEFT JOIN', 'items', 'items.person_id = person.id')
                        ->join('LEFT JOIN', 'person_attachment', 'person_attachment.person_id = person.id')
                        ->andFilterWhere(['LIKE', 'name', $model->name])
                        ->andFilterWhere(['LIKE', 'surname', $model->surname])
                        ->andFilterWhere(['LIKE', 'place', $model->place])
                        ->andFilterWhere(['LIKE', 'gender', $model->gender])
                        ->andFilterWhere(['LIKE', 'marital_status', $model->marital_status])
                        ->andFilterWhere(['LIKE', 'maiden_name', $model->maiden_name])
                        ->andFilterWhere(['LIKE', 'address', $model->address])
                        ->andFilterWhere(['LIKE', 'item', $model->item])
                        ->andFilterWhere(['LIKE', 'file_caption', $model->caption])
                        ->andFilterWhere(['LIKE', 'note', $model->note]);
                } else {
                    $query = $query->select(['person.id', 'name', 'surname', "place", "gender", "owner"])
                        ->distinct()
                        ->from('person')
                        ->join('LEFT JOIN', 'person_detail', 'person_detail.person_id = person.id')
                        ->join('LEFT JOIN', 'items', 'items.person_id = person.id')
                        ->join('LEFT JOIN', 'person_attachment', 'person_attachment.person_id = person.id')
                        ->andFilterWhere(['LIKE', 'name', $model->name])
                        ->andFilterWhere(['LIKE', 'surname', $model->surname])
                        ->andFilterWhere(['LIKE', 'place', $model->place])
                        ->andFilterWhere(['LIKE', 'gender', $model->gender])
                        ->andFilterWhere(['LIKE', 'marital_status', $model->marital_status])
                        ->andFilterWhere(['LIKE', 'maiden_name', $model->maiden_name])
                        ->andFilterWhere(['LIKE', 'address', $model->address])
                        ->andFilterWhere(['LIKE', 'item', $model->item])
                        ->andFilterWhere(['LIKE', 'file_caption', $model->caption])
                        ->andFilterWhere(['LIKE', 'note', $model->note])
                        ->andFilterWhere(['=', 'owner', $userId]);
                }
                $dataProvider = Yii::createObject([
                    'class' => ActiveDataProvider::class,
                    'query' => $query,
                    'sort' => $sort,
                ]);
                return $dataProvider;
            }
        }
        // return empty but with pagination data
        $query = new \yii\db\Query();
        $query->select(['person.id', 'name', 'surname'])
            ->distinct()
            ->from('person');
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        // $dataProvider = new ArrayDataProvider([
        //     'allModels' => [],
        // ]);
        return $dataProvider;
    }
}
