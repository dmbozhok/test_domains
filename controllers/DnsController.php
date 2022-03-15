<?php

namespace app\controllers;

use app\components\APIHelper;
use app\models\DnsChange;
use app\models\Domain;
use Exception;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

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

    /**
     * Выполнение операции по смене ns серверов
     * @return array|string
     */
    public function actionUpdate()
    {
        $result = null;
        $model = new DnsChange();
        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post())) {
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $model->time = time();
            $model->status = DnsChange::STATUS_NEW;

            if ($model->save()) {
                try {
                    $result = $model->updateNS();
                } catch (InvalidConfigException | Exception $e) {
                    $result = false;
                }
            }
        }

        return $this->render('update', ['model' => $model, 'result' => $result]);
    }

    /**
     * Обновление статуса операции по смене ns серверов
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionUpdateStatus()
    {
        $model = DnsChange::findOne(['id' => \Yii::$app->request->post('id'), 'status' => DnsChange::STATUS_SENT]);
        if ($model) {
            if ($model->updateStatus()) {
                return $this->asJson(['success' => true]);
            } else {
                return $this->asJson(['success' => false]);
            }
        } else {
            return $this->asJson(['success' => false, 'error' => 'Запись об операции не найдена или неверный статус для проверки']);
        }
    }
}