<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dns_change}}`.
 */
class m220313_033943_create_dns_change_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%dns_change}}', [
            'id' => $this->primaryKey(),
            'domain_id' => $this->integer(),
            'ns1' => $this->string(),
            'ns2' => $this->string(),
            'ns3' => $this->string(),
            'ns4' => $this->string(),
            'handle' => $this->string(),
            'status' => $this->integer(),
            'time' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-dns_change-domain_id',
            '{{%dns_change}}',
            'domain_id',
            '{{%domains}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-dns_change-domain_id',
            '{{%dns_change}}');
        $this->dropTable('{{%dns_change}}');
    }
}
