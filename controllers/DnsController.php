<?php

namespace app\controllers;

use app\components\APIHelper;
use app\models\DnsChange;
use app\models\Domain;
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionUpdate()
    {
        if (\Yii::$app->request->isAjax) {
            $model = new DnsChange();
            if ($model->load(\Yii::$app->request->post())) {
                \Yii::warning($model->attributes);
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }
        $result = [];
        $model = new DnsChange();
        if (\Yii::$app->request->isPost) {
            $domain = Domain::findOne(['id' => \Yii::$app->request->post('DnsChange')['domain_id']]);
            if ($domain) {
                $result = $domain->updateNS(\Yii::$app->request->post('DnsChange'));
                $model = $result['model'];
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
            $result = APIHelper::getTaskStatus($model->handle);
            if ($result['status'] == 'success') {
                $model->status = DnsChange::STATUS_SUCCESS;
                $model->save(false);
                return $this->asJson(['success' => true]);
            } else {
                if ($result['status'] == 'failed') {
                    $model->status = DnsChange::STATUS_FAILED;
                    $model->save(false);
                } elseif ($result['status'] == 'cancelled') {
                    $model->status = DnsChange::STATUS_CANCELLED;
                    $model->save(false);
                }
                return $this->asJson(['success' => false, 'data' => $result]);
            }
        } else {
            return $this->asJson(['success' => false, 'error' => 'Запись об операции не найдена или неверный статус для проверки']);
        }
    }
}