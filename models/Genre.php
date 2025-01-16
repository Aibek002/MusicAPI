<?php
namespace app\models;

use yii\db\ActiveRecord;

class Genre extends ActiveRecord{
    public static function tableName()
    {
        return 'audio_genres_catalog';
    }
}