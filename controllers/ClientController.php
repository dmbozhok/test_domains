<?php


namespace app\controllers;


use app\components\APIHelper;
use app\models\Client;
use yii\base\InvalidConfigException;
use yii\bootstrap4\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\httpclient\Exception;
use yii\web\Response;

class ClientController extends \yii\web\Controller
{
    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $query = Client::find()->orderBy(['id' => SORT_DESC]);
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => array('pageSize' => 20),
                'sort' => false
            ]),
        ]);
    }

    /**
     * @return array|string
     */
    public function actionAdd()
    {
        $result = null;
        $model = new Client();
        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post())) {
            if (\Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
            $model->status = Client::STATUS_NEW;
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
     * Обновление статуса создания клиента
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionUpdateStatus()
    {
        $model = Client::findOne(['id' => \Yii::$app->request->post('id'), 'status' => Client::STATUS_SENT]);
        if ($model) {
            if ($model->updateStatus()) {
                return $this->asJson(['success' => true]);
            } else {
                return $this->asJson(['success' => false]);
            }
        } else {
            return $this->asJson(['success' => false, 'error' => 'Запись не найдена или неверный статус для проверки']);
        }
    }
}