<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dns_change".
 *
 * @property int $id
 * @property int|null $domain_id
 * @property string|null $ns
 * @property string|null $handle
 * @property int|null $status
 * @property int|null $time
 *
 * @property Domain $domain
 */
class DnsChange extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;
    const STATUS_CANCELLED = 4;

    /**
     * @var mixed|null
     */
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
        return 'dns_change';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['domain_id', 'status', 'time'], 'integer'],
            [['ns'], 'string'],
            [['handle'], 'string', 'max' => 255],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain_id' => 'Domain ID',
            'ns' => 'Ns',
            'handle' => 'Handle',
            'status' => 'Status',
            'time' => 'Time',
        ];
    }

    /**
     * Gets query for [[Domain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
    }
}
