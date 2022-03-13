<?php

namespace app\controllers;

use app\models\DnsChange;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class DnsController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $query = DnsChange::find()->orderBy(['id' => SORT_DESC]);
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => array('pageSize' => 20),
                'sort' => false
            ]),
        ]);
    }
}