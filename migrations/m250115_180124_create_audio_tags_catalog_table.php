<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audio_genres_catalog}}`.
 */
class m250115_180124_create_audio_tags_catalog_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%audio_tags_catalog}}', [
            'id' => $this->primaryKey(),
            'tag_type' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%audio_tags_catalog}}');
    }
}
