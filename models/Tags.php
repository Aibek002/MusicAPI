<?php
namespace app\models;

use yii\db\ActiveRecord;

class Tags extends ActiveRecord{
    public static function tableName()
    {
        return 'audio_tags_catalog';
    }
}