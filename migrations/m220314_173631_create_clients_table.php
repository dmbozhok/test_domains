<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%clients}}`.
 */
class m220314_173631_create_clients_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%clients}}', [
            'id' => $this->primaryKey(),
            'external_id' => $this->integer(),
            'nameLocal' => $this->string(),
            'birthday' => $this->string(),
            'legal' => $this->string(),
            'emails' => $this->string(),
            'phones' => $this->string(),
            'addressLocalIndex' => $this->string(),
            'addressLocalCountry' => $this->string(),
            'addressLocalRegion' => $this->string(),
            'addressLocalCity' => $this->string(),
            'addressLocalStreet' => $this->string(),
            'identityCountry' => $this->string(),
            'identityType' => $this->string(),
            'identitySeries' => $this->string(),
            'identityNumber' => $this->string(),
            'identityIssuer' => $this->string(),
            'identityIssued' => $this->string(),
            'handle' => $this->string(),
            'status' => $this->integer(),
        ]);

        $this->addColumn('{{%domains}}', 'client_id', $this->integer());

        $this->createIndex(
            'idx-domains-client_id',
            '{{%domains}}',
            'client_id'
        );
        $this->addForeignKey(
            'fk-domains-client_id',
            '{{%domains}}',
            'client_id',
            '{{%clients}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-domains-client_id',
            '{{%domains}}');
        $this->dropIndex(
            'idx-domains-client_id',
            '{{%domains}}');
        $this->dropColumn('{{%domains}}', 'client_id');
        $this->dropTable('{{%clients}}');
    }
}
