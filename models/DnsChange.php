<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dns_change".
 *
 * @property int $id
 * @property int|null $domain_id
 * @property string|null $ns1
 * @property string|null $ns2
 * @property string|null $ns3
 * @property string|null $ns4
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
        self::STATUS_NEW => 'Новый запрос (не отправлен)',
        self::STATUS_SENT => 'Новый запрос (отправлен)',
        self::STATUS_SUCCESS => 'Успешно отправлен',
        self::STATUS_FAILED => 'Ошибка при отправке',
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
            [['ns1', 'ns2', 'ns3', 'ns4', 'handle'], 'string', 'max' => 255],
            [['ns1', 'ns2', 'ns3', 'ns4',], 'validateNS', 'skipOnEmpty' => false],
            // [['ns1', 'ns2', 'ns3', 'ns4',], 'validateRequireNS',],
            [['domain_id'], 'exist', 'skipOnEmpty' => false, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id'], 'message' => 'Выберите домен'],
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
            'ns1' => 'Ns 1',
            'ns2' => 'Ns 2',
            'ns3' => 'Ns 3',
            'ns4' => 'Ns 4',
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

    /**
     * Проверка на ввод хотя бы одного адреса
     * @return bool
     */
    public function validateRequireNS()
    {
        $data = [$this->ns1, $this->ns2, $this->ns3, $this->ns4,];
        \Yii::warning($data);
        \Yii::warning(count(array_filter($data)));
        if (count(array_filter($data)) == 0) {
            $this->addError('ns1', 'Введите хотя бы одно имя');
            return false;
        }
        return true;
    }

    /**
     * Проверка на валидность доменных имен
     * @param $attribute
     * @return bool
     */
    public function validateNS($attribute)
    {
        $data = [$this->ns1, $this->ns2, $this->ns3, $this->ns4,];
        if (count(array_filter($data)) < 2) {
            $this->addError('ns1', 'Введите два ns-сервера');
            return false;
        }
        $val = $this->$attribute;
        if ($val != '') {
            $domains = explode(' ', $val);
            foreach ($domains as $domain) {
                if (!self::checkDomain($domain)) {
                    $this->addError($attribute, 'Введите валидное доменное имя');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Проверка подходит ли имя под правила по доменным именам
     * @param $domain_name
     * @return bool
     */
    private static function checkDomainName($domain_name): bool
    {
        $domain_len = strlen($domain_name);
        if ($domain_len < 3 or $domain_len > 253)
            return false;

        if (stripos($domain_name, 'http://') === 0)
            $domain_name = substr($domain_name, 7);
        elseif (stripos($domain_name, 'https://') === 0)
            $domain_name = substr($domain_name, 8);

        if (stripos($domain_name, 'www.') === 0)
            $domain_name = substr($domain_name, 4);

        if (strpos($domain_name, '.') === false or $domain_name[strlen($domain_name) - 1] == '.' or $domain_name[0] == '.')
            return false;

        return (filter_var('http://' . $domain_name, FILTER_VALIDATE_URL) === false) ? false : true;
    }

    /**
     * Проверяет является строка валидным доменым именем или публичным ip адресом
     * @param $name
     * @return bool
     */
    private static function checkDomain($name): bool
    {
        $ascii = idn_to_ascii($name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($ascii != $name) {
            return self::checkDomainName($ascii);
        }
        return self::checkDomainName($name) || filter_var($name, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function updateNS()
    {
        $result = \Yii::$app->api->performRequest('domainUpdate', [
            'id' => $this->domain->external_id,
            'clientId' => $this->domain->client->external_id,
            'domain' => [
                'delegated' => true,
                'nservers' => array_filter([
                    $this->ns1,
                    $this->ns2,
                    $this->ns3,
                    $this->ns4,
                ]),
            ],
        ]);

        if ($result) {
            $this->handle = $result['handle'];
            $this->status = DnsChange::STATUS_SENT;
            $this->save(false);
            \Yii::info('added domain change ns servers request ' . $this->id, 'log');
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
                $this->status = self::STATUS_SUCCESS;
                $this->save(false);
                return true;
            } else {
                if ($result['status'] == 'failed') {
                    $this->status = self::STATUS_FAILED;
                    $this->save(false);
                    return true;
                } elseif ($result['status'] == 'cancelled') {
                    $this->status = self::STATUS_CANCELLED;
                    $this->save(false);
                    return true;
                }
            }
        }
        return false;
    }
}
