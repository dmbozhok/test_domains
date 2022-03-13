<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%domains}}`.
 */
class m220312_145951_create_domains_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%domains}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'nameIdn' => $this->string(),
            'emails' => $this->string(),
            'phones' => $this->string(),
            'handle' => $this->string(),
            'status' => $this->integer(),
            'time' => $this->integer(),
            'external_id' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%domains}}');
    }
}
