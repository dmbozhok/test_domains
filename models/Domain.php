<?php

namespace app\models;

use app\components\APIHelper;
use kdn\yii2\validators\DomainValidator;
use Yii;

/**
 * This is the model class for table "domains".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $nameIdn
 * @property string|null $emails
 * @property string|null $phones
 * @property string|null $handle
 * @property int|null $status
 * @property int|null $time
 * @property int|null $external_id
 *
 * @property DnsChange[] $dnsChanges
 */
class Domain extends \yii\db\ActiveRecord
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
        return 'domains';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'time', 'external_id'], 'integer'],
            ['name', 'required', 'message' => 'Введите доменное имя'],
            ['emails', 'required', 'message' => 'Введите email'],
            ['phones', 'required', 'message' => 'Введите телефон'],
            ['name', DomainValidator::class, 'enableIDN' => true, 'allowURL' => false, 'message' => 'Введите корректное доменное имя'],
            ['emails', 'email', 'message' => 'Введите корректный email'],
            ['phones', 'validatePhone', 'message' => 'Введите корректный номер телефона'],
            [['name', 'nameIdn', 'emails', 'phones', 'handle'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'nameIdn' => 'Name Idn',
            'emails' => 'Emails',
            'phones' => 'Phones',
            'handle' => 'Handle',
            'status' => 'Status',
            'time' => 'Time',
            'external_id' => 'External ID',
        ];
    }

    /**
     * Gets query for [[DnsChanges]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDnsChanges()
    {
        return $this->hasMany(DnsChange::className(), ['domain_id' => 'id']);
    }

    /**
     * Проверка номера телефона
     * @return bool
     */
    public function validatePhone()
    {
        $phones = self::formatToPhone($this->phones);
        if (strlen($phones) != 14) {
            $this->addError('phone', "Введите корректный номер телефона");
            return false;
        }
        return true;
    }

    /**
     * Форматирование номера телефона
     * @param $phone
     * @return array|string|string[]|null
     */
    public static function formatToPhone($phone)
    {
        $phone = str_replace(['+', '(', ')', ' ', '-', '_'], '', $phone);
        $phone = substr($phone, 1);
        return preg_replace("/([0-9]{3})([0-9]{7})/", "+7 $1 $2", $phone);
    }

    /**
     * Добавляет домен в бд и через api в систему и возвращает успешно ли это прошло
     * @param $data
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public static function add($data): array
    {
        $model = new self();
        $model->attributes = $data;
        $model->nameIdn = $model->name;
        $model->name = idn_to_ascii($model->nameIdn);
        $model->status = self::STATUS_NEW;
        $model->time = time();

        if ($model->save()) {
            $result = APIHelper::createDomain([
                'name' => $model->name,
                // 'emails' => [$model->emails],
                // 'phones' => [self::formatToPhone($model->phones)],
                'comment' => 'Comment: created via API',
            ]);
            if ($result) {
                $model->external_id = $result['id'];
                $model->handle = $result['handle'];
                $model->status = self::STATUS_SENT;
                \Yii::info('added ' . $model->id . ' with external id ' . $model->external_id, 'log');
                $model->save(false);
                return ['model' => $model, 'sent' => true];
            } else {
                \Yii::warning('empty result', 'log');
                return ['model' => $model, 'sent' => false];
            }
        } else {
            \Yii::error($model->errors);
            return ['model' => $model, 'success' => false];
        }
    }

    /**
     * Обновляет ns сервера
     * @param array $ns
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function updateNS(array $ns): array
    {
        $modelChange = new DnsChange();
        $modelChange->domain_id = $this->id;
        $modelChange->time = time();
        $modelChange->status = DnsChange::STATUS_NEW;
        $modelChange->ns1 = $ns['ns1'];
        $modelChange->ns2 = $ns['ns2'];
        $modelChange->ns3 = $ns['ns3'];
        $modelChange->ns4 = $ns['ns4'];

        if ($modelChange->validate() && $modelChange->save()) {
            $result = APIHelper::updateDomainNS($this->external_id, array_filter([
                $modelChange->ns1, $modelChange->ns2, $modelChange->ns3, $modelChange->ns4,
            ]));
            if ($result) {
                $modelChange->handle = $result['handle'];
                $modelChange->status = DnsChange::STATUS_SENT;
                $modelChange->save(false);
                return ['model' => $modelChange, 'sent' => true];
            } else {
                \Yii::warning('empty result', 'log');
                return ['model' => $modelChange, 'sent' => false];
            }
        } else {
            return ['model' => $modelChange, 'errors' => $modelChange->errors];
        }
    }

    /**
     * Массив активированных доменов
     * @return array
     */
    public static function listActiveDomains(): array
    {
        return self::find()->select(['name', 'id'])->where(['status' => self::STATUS_SUCCESS])->indexBy('id')->orderBy('name')->column();
    }
}
