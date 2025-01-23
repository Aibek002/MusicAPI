<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%audio_genres_catalog}}`
 * - `{{%audio_status_catalog}}`
 */
class m250115_183421_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post}}', [
            'id' => $this->primaryKey(),
            'titlePost' => $this->string(50),
            'descriptionPost' => $this->text(),
            'nameAudioFile' => $this->string(255),
            'postCreator' => $this->integer(),
            'genre_id' => $this->integer(),
            'createAt' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updateAt' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'status_id' => $this->integer()->defaultValue(1),
        ]);

        // creates index for column `postCreator`
        $this->createIndex(
            '{{%idx-post-postCreator}}',
            '{{%post}}',
            'postCreator'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-post-postCreator}}',
            '{{%post}}',
            'postCreator',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `tags_id`
        $this->createIndex(
            '{{%idx-post-tags_id}}',
            '{{%post}}',
            'tags_id'
        );

        // add foreign key for table `{{%audio_tags_catalog}}`
        $this->addForeignKey(
            '{{%fk-post-tags_id}}',
            '{{%post}}',
            'tags_id',
            '{{%audio_tags_catalog}}',
            'id',
            'CASCADE'
        );

        // creates index for column `status_id`
        $this->createIndex(
            '{{%idx-post-status_id}}',
            '{{%post}}',
            'status_id'
        );

        // add foreign key for table `{{%audio_status_catalog}}`
        $this->addForeignKey(
            '{{%fk-post-status_id}}',
            '{{%post}}',
            'status_id',
            '{{%audio_status_catalog}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-post-postCreator}}',
            '{{%post}}'
        );

        // drops index for column `postCreator`
        $this->dropIndex(
            '{{%idx-post-postCreator}}',
            '{{%post}}'
        );

        // drops foreign key for table `{{%audio_genres_catalog}}`
        $this->dropForeignKey(
            '{{%fk-post-genre_id}}',
            '{{%post}}'
        );

        // drops index for column `genre_id`
        $this->dropIndex(
            '{{%idx-post-genre_id}}',
            '{{%post}}'
        );

        // drops foreign key for table `{{%audio_status_catalog}}`
        $this->dropForeignKey(
            '{{%fk-post-status_id}}',
            '{{%post}}'
        );

        // drops index for column `status_id`
        $this->dropIndex(
            '{{%idx-post-status_id}}',
            '{{%post}}'
        );

        $this->dropTable('{{%post}}');
    }
}
