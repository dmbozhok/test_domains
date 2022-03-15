<?php

namespace app\controllers;

use app\components\APIHelper;
use app\models\Domain;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\httpclient\Exception;
use yii\web\Controller;
use yii\web\Response;

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
        $api = \Yii::$app->api;
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
     * @return string|array
     */
    public function actionAdd()
    {
        $model = new Domain();
        $result = null;
        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post())) {
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $model->status = Domain::STATUS_NEW;
            $model->nameIdn = $model->name;
            $model->name = idn_to_ascii($model->nameIdn);
            $model->time = time();
            if ($model->save()) {
                try {
                    $result = $model->sendRequest();
                } catch (InvalidConfigException | Exception $e) {
                    $result = false;
                }
            }
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
            if ($model->updateStatus()) {
                return $this->asJson(['success' => true]);
            } else {
                return $this->asJson(['success' => false]);
            }
        } else {
            return $this->asJson(['success' => false, 'error' => 'Запись о домене не найдена или неверный статус для проверки']);
        }
    }
}