<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clients".
 *
 * @property int $id
 * @property int|null $external_id
 * @property string|null $nameLocal
 * @property string|null $birthday
 * @property string|null $legal
 * @property string|null $emails
 * @property string|null $phones
 * @property string|null $addressLocalIndex
 * @property string|null $addressLocalCountry
 * @property string|null $addressLocalRegion
 * @property string|null $addressLocalCity
 * @property string|null $addressLocalStreet
 * @property string|null $identityCountry
 * @property string|null $identityType
 * @property string|null $identitySeries
 * @property string|null $identityNumber
 * @property string|null $identityIssuer
 * @property string|null $identityIssued
 * @property int|null $status
 * @property string|null $handle
 *
 * @property Domain[] $domains
 */
class Client extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELLED = 4;

    public static $statuses = [
        self::STATUS_NEW => 'Новый (не отправлен)',
        self::STATUS_SENT => 'Новый (отправлен)',
        self::STATUS_SUCCESS => 'Успешно создан',
        self::STATUS_FAILED => 'Ошибка при создании',
        self::STATUS_CANCELLED => 'Задача отменена',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['external_id', 'status'], 'integer'],
            [['nameLocal', 'birthday', 'legal', 'emails', 'phones', 'addressLocalIndex', 'addressLocalCountry', 'addressLocalRegion', 'addressLocalCity', 'addressLocalStreet', 'identityCountry', 'identityType', 'identitySeries', 'identityNumber', 'identityIssuer', 'identityIssued', 'handle'], 'string', 'max' => 255],
            [['nameLocal', 'birthday', 'emails', 'phones', 'addressLocalIndex', 'addressLocalCountry', 'addressLocalRegion', 'addressLocalCity', 'addressLocalStreet', 'identityCountry', 'identityType', 'identitySeries', 'identityNumber', 'identityIssuer', 'identityIssued'], 'required', 'message' => 'Введите все данные'],
            [['identitySeries', 'identityNumber', 'identityIssuer', 'identityIssued'], 'validateIdentity', 'skipOnEmpty' => false],
            ['emails', 'email', 'message' => 'Введите корректный email'],
            ['phones', 'validatePhone', 'message' => 'Введите корректный номер телефона'],
            ['addressLocalCountry', 'validateAddressCountry', 'skipOnEmpty' => false],
            ['addressLocalRegion', 'validateAddressRegion', 'skipOnEmpty' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'external_id' => 'External ID',
            'nameLocal' => 'ФИО',
            'birthday' => 'Дата рождения',
            'legal' => 'Legal',
            'emails' => 'Email',
            'phones' => 'Телефон',
            'addressLocalIndex' => 'Индекс',
            'addressLocalCountry' => 'Страна (двухбуквенный код)',
            'addressLocalRegion' => 'Регион (номер региона)',
            'addressLocalCity' => 'Город',
            'addressLocalStreet' => 'Адрес',
            'identityCountry' => 'Страна',
            'identityType' => 'Тип документа',
            'identitySeries' => 'Серия',
            'identityNumber' => 'Номер',
            'identityIssuer' => 'Кем выдан',
            'identityIssued' => 'Когда выдан',
            'handle' => 'Handle',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[Domains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomains()
    {
        return $this->hasMany(Domain::className(), ['client_id' => 'id']);
    }

    /**
     * @return bool
     */
    public function validateAddressCountry()
    {
        if (mb_strlen($this->addressLocalCountry) != 2) {
            $this->addError('addressLocalCountry', "Введите код страны из 2 букв");
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function validateAddressRegion()
    {
        if (intval($this->addressLocalRegion) == 0 || !is_numeric($this->addressLocalRegion)) {
            $this->addError('addressLocalRegion', "Введите числовой код региона");
            return false;
        }
        return true;
    }

    /**
     * Проверка номера телефона
     * @return bool
     */
    public function validatePhone()
    {
        $phones = \Yii::$app->api->formatToPhone($this->phones);
        if (strlen($phones) != 14) {
            $this->addError('phone', "Введите корректный номер телефона");
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function validateIdentity()
    {
        $result = true;

        if (!preg_match('/^\d{4}$/', $this->identitySeries)) {
            $this->addError('identitySeries', "Введите правильно серию паспорта");
            $result = false;
        }
        if (!preg_match('/^\d{6}$/', $this->identityNumber)) {
            $this->addError('identityNumber', "Введите правильно номер паспорта");
            $result = false;
        }
        if (!preg_match('/^[ 0-9a-zA-Zа-яёА-ЯЁ№\/\'\".,-]{3,128}$/', $this->identityIssuer)) {
            $this->addError('identityNumber', "Введите правильно место выдачи паспорта");
            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function sendRequest()
    {
        $result = \Yii::$app->api->performRequest('clientCreate', [
            'client' => [
                'legal' => 'person',
                'nameLocal' => $this->nameLocal,
                'birthday' => $this->birthday,
                'identity' => [
                    'type' => 'passport',
                    'country' => 'RU',
                    'series' => $this->identitySeries,
                    'number' => $this->identityNumber,
                    'issuer' => $this->identityIssuer,
                    'issued' => $this->identityIssued,
                ],
                'emails' => [$this->emails],
                'phones' => [
                    \Yii::$app->api->formatToPhone($this->phones)
                ],
                'addressLocal' => [
                    'index' => $this->addressLocalIndex,
                    'country' => $this->addressLocalCountry,
                    'region' => $this->addressLocalRegion,
                    'city' => $this->addressLocalCity,
                    'street' => $this->addressLocalStreet,
                ],
            ]
        ]);
        if ($result) {
            $this->external_id = $result['id'];
            $this->handle = $result['handle'];
            $this->status = self::STATUS_SENT;
            $this->save(false);
            \Yii::info('added client ' . $this->id . ' with external id ' . $this->external_id, 'log');
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function updateStatus(): bool
    {
        $result = \Yii::$app->api->performRequest('taskStatus', [
            'handle' => $this->handle,
        ]);
        if (is_array($result)) {
            if ($result['status'] == 'success') {
                $this->status = Client::STATUS_SUCCESS;
                $this->save(false);
                return true;
            } else {
                if ($result['status'] == 'failed') {
                    $this->status = Client::STATUS_FAILED;
                    $this->save(false);
                    return true;
                } elseif ($result['status'] == 'cancelled') {
                    $this->status = Client::STATUS_CANCELLED;
                    $this->save(false);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public static function listActiveClients(): array
    {
        return self::find()->select(['nameLocal', 'id'])->where(['status' => self::STATUS_SUCCESS])->indexBy('id')->orderBy('nameLocal')->column();
    }
}
