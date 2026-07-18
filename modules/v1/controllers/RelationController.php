<?php

namespace app\modules\v1\controllers;

use yii\rest\Controller;;

use yii\filters\auth\HttpBasicAuth;
use app\models\basic\Person;
use app\models\basic\PersonRelation;
use app\models\basic\RelationName;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class RelationController extends Controller {
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
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];
        return $behaviors;
    }

    public function actions() {
        return [
            // this action is added to overcome CORS preflight check in browser
            // ActiveController does this automatically but we don't use it
            'options' => 'yii\rest\OptionsAction',
        ];
    }

    public function actionView($id) {
        $person = Person::findOne($id);
        if ($person) {
            $relations = $person->relations();
            $relations = $this->applyFilters($relations);
            return new ArrayDataProvider([
                'allModels' => $relations,
                'sort' => [
                    'attributes' => ['relation', 'relation_to_whom'],
                ]
            ]);
        }
        throw new \yii\web\NotFoundHttpException('Person id:' . $id . ' not found');
    }

    public function actionDelete($id) {
        $relationRecord = PersonRelation::findOne($id);
        // only the owner of both persons (or admin) may delete the relation
        if ($relationRecord && $relationRecord->checkOwnership() && $relationRecord->delete()) {
            return ["deleted_id" => $id];
        }
        throw new \yii\web\NotFoundHttpException('Relation id:' . $id . ' not found');
    }
    public function actionCreate() {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $personAid = ArrayHelper::getValue($params, 'person_a_id', $default = -1);
        $personBid = ArrayHelper::getValue($params, 'person_b_id', $default = -1);
        if (Person::findOne($personAid) && Person::findOne($personBid)) {
            $newRelation = new PersonRelation();
            $newRelation->load($params, '');
            if ($newRelation->save()) {
                return $newRelation;
            }
        }
        throw new \yii\web\BadRequestHttpException();
    }

    public function actionUpdate() {
        $params = \Yii::$app->getRequest()->getBodyParams();
        $relationId = ArrayHelper::getValue($params, 'id', $default = -1);
        $personAid = ArrayHelper::getValue($params, 'person_a_id', $default = -1);
        $personBid = ArrayHelper::getValue($params, 'person_b_id', $default = -1);
        $activeRelation = PersonRelation::findOne($relationId);
        if ($activeRelation && Person::findOne($personAid) && Person::findOne($personBid)) {
            $activeRelation->load($params, '');
            if ($activeRelation->save()) {
                return $activeRelation;
            }
        }
        throw new \yii\web\BadRequestHttpException();
    }

    public function actionViewRelation($relationId) {
        $relation = PersonRelation::findOne($relationId);
        // only the owner of both persons (or admin) may view the relation
        if ($relation && $relation->checkOwnership()) {
            return $relation;
        }
        throw new \yii\web\NotFoundHttpException('Relation id:' . $relationId . ' not found');
    }

    public function actionRelationNames() {
        $relationNames = RelationName::find()->where(['gender' => ['m', 'f', '?']])->all();
        return $relationNames;
    }

    private function applyFilters(array $relationsToFilter) {
        $filteredRelations = [];
        $filters = \Yii::$app->request->getQueryParam('filter');
        if ($filters != null) {
            foreach ($relationsToFilter as $relation) {
                $found = true;
                foreach ($filters as $field => $condition) {
                    foreach ($condition as $methodName => $filterValue) {
                        foreach ($filterValue as $value) {
                            switch ($methodName) {
                                case ('like'):
                                    if (stripos($relation[$field], $value) !== false) {
                                        // $found = true;
                                    } else {
                                        $found = false;
                                    }
                                    break;
                                default:
                                    $found = true;
                            }
                        }
                    }
                }
                if ($found) {
                    $filteredRelations[] = $relation;
                    $found = false;
                }
            }
        } else {
            return $relationsToFilter;
        }
        return $filteredRelations;
    }
}
