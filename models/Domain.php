<?php

namespace app\models;

use app\components\APIHelper;
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
}
