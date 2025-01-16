<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audio_genres_catalog}}`.
 */
class m250115_180124_create_audio_genres_catalog_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%audio_genres_catalog}}', [
            'id' => $this->primaryKey(),
            'genre_type' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%audio_genres_catalog}}');
    }
}
