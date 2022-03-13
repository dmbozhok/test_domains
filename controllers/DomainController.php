<?php

namespace app\controllers;

use app\components\APIHelper;
use app\models\Domain;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

/**
 * Class DomainController
 * @package app\controllers
 */
class DomainController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $query = Domain::find()->orderBy(['id' => SORT_DESC]);
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => array('pageSize' => 20),
                'sort' => false
            ]),
        ]);
    }

    /**
     * Форма добавления домена
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionAdd(): string
    {
        if (\Yii::$app->request->isPost) {
            $result = Domain::add(\Yii::$app->request->post('Domain'));
            $model = $result['model'];
        } else {
            $result = [];
            $model = new Domain();
        }
        return $this->render('add', ['model' => $model, 'result' => $result]);
    }

    /**
     * Обновление статус домена
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionUpdateStatus()
    {
        $model = Domain::findOne(['id' => \Yii::$app->request->post('id'), 'status' => Domain::STATUS_SENT]);
        if ($model) {
            $result = APIHelper::getTaskStatus($model->handle);
            if ($result['status'] == 'success') {
                $model->status = Domain::STATUS_SUCCESS;
                $model->save(false);
                return $this->asJson(['success' => true]);
            } else {
                if ($result['status'] == 'failed') {
                    $model->status = Domain::STATUS_FAILED;
                    $model->save(false);
                } elseif ($result['status'] == 'cancelled') {
                    $model->status = Domain::STATUS_CANCELLED;
                    $model->save(false);
                }
                return $this->asJson(['success' => false, 'data' => $result]);
            }
        } else {
            return $this->asJson(['success' => false, 'error' => 'Запись о домене не найдена или неверный статус для проверки']);
        }
    }
}