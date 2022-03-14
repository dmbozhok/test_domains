<?php


namespace app\components;


use yii\helpers\Json;
use yii\httpclient\Client;

class APIComponent extends \yii\base\BaseObject
{
    /**
     * @var array
     */
    private $auth;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $token = '';

    /**
     * APIComponent constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->auth = [
            'login' => $this->user,
            'password' => $this->password,
        ];

        $this->client = new Client([
            'baseUrl' => $this->url,
        ]);

        $tokenRequest = $this->performRequest('authLogin', $this->auth, false);
        $this->token = $tokenRequest['token'];
    }

    /**
     * @param $value
     */
    public function seturl($value)
    {
        $this->url = $value;
    }

    /**
     * @param $value
     */
    public function setuser($value)
    {
        $this->user = $value;
    }

    /**
     * @param $value
     */
    public function setpassword($value)
    {
        $this->password = $value;
    }

    /**
     * @param string $method
     * @param array $data
     * @param bool $auth
     * @return false|mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function performRequest(string $method, array $data, bool $auth = true)
    {
        if ($auth) {
            $data = [
                'auth' => $this->token ? [
                    'token' => $this->token,
                ] : $this->auth,
            ] + $data;
        }
        $response = $this->client->createRequest()
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
            \Yii::trace($response->content, 'http');
            $data = Json::decode($response->content);
            if (isset($data['result'])) {
                return $data['result'];
            } else {
                \Yii::error('error', 'http');
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $phone
     * @return string
     */
    public function formatToPhone($phone): string
    {
        $phone = str_replace(['+', '(', ')', ' ', '-', '_'], '', $phone);
        $phone = substr($phone, 1);
        return preg_replace("/([0-9]{3})([0-9]{7})/", "+7 $1 $2", $phone);
    }
}