<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audio_status_catalog}}`.
 */
class m250115_180227_create_audio_status_catalog_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%audio_status_catalog}}', [
            'id' => $this->primaryKey(),
            'status_type' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%audio_status_catalog}}');
    }
}
