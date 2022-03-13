<?php

namespace app\components;

use yii\helpers\Json;
use yii\httpclient\Client;

class APIHelper
{
    const BASE_URL = 'https://vrdemo.virtreg.ru/vr-api';
    const USER = 'demo';
    const CLIENT = 813;
    const PASSWORD = 'demo';
    public static $token = '';

    /**
     * Массив с данными для авторизации
     * @return string[]
     */
    private static function getAuthObject(): array
    {
        return [
            'login' => self::USER ,//. '/' . self::CLIENT,
            'password' => self::PASSWORD,
        ];
    }

    /**
     * Выполняет запрос
     * @param string $method
     * @param $data
     * @return false|mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private static function performRequest(string $method, $data)
    {
        $client = new Client([
            'baseUrl' => self::BASE_URL,
        ]);
        $response = $client->createRequest()
            ->setMethod('post')
            ->addHeaders(['content-type' => 'application/json'])
            ->setContent(Json::encode([
                "jsonrpc" => "2.0",
                'method' => $method,
                'params' => $data,
                'id' => uniqid(),
            ]))
            ->send();

        if ($response->isOk) {
            \Yii::trace($response->content);
            $data = Json::decode($response->content);
            if ($data) {
                return $data['result'];
            } else {
                \Yii::error('error', 'http');
            }
        } else {
            return false;
        }
    }

    /**
     * Получаем токен для авторизации
     * @return false|mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    private static function getAuthToken()
    {
        if (self::$token) {
            return self::$token;
        }
        $data = self::performRequest('authLogin', self::getAuthObject());
        if (isset($data['token'])) {
            self::$token = $data['token'];
            return $data['token'];
        } else {
            return false;
        }
    }

    /**
     * Отправляет запрос на создание домена и возвращает ответ
     * @param $domainData
     * @return false|mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public static function createDomain($domainData)
    {
        return self::performRequest('domainCreate', [
            'auth' => [
                'token' => self::getAuthToken(),
            ],
            'clientId' => self::CLIENT,
            'domain' => $domainData,
        ]);
    }

    /**
     * Получение статуса по асинхронной задаче
     * @param $handle
     * @return false|mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public static function getTaskStatus($handle)
    {
        return self::performRequest('taskStatus', [
            'auth' => [
                'token' => self::getAuthToken(),
            ],
            'handle' => $handle,
        ]);
    }

    public static function updateDomainNS($domainId, $servers)
    {
        return self::performRequest('domainUpdate', [
            'auth' => [
                'token' => self::getAuthToken(),
            ],
            'id' => $domainId,
            'clientId' => self::CLIENT,
            'domain' => [
                'delegated' => true,
                'nservers' => $servers,
            ],
        ]);
    }
}