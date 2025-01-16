<?php

namespace app\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{

    public static function tableName()
    {
        return 'post';
    }
    public function rules()
    {
        return [
            [['titlePost','descriptionPost','nameAudioFile','postCreator','genre_id'],'required'],
            [['titlePost'], 'string', 'max' => 50],
            [['descriptionPost'], 'string', 'min'=>10],
            [['nameAudioFile'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'titlePost' => 'Title',
            'descriptionPost' => 'Description',
            'nameAudioFile' => 'Audio File',
            'postCreator' => 'Creator',
            'genre_id' => 'Genre',
            'status_id' => 'Status',
        ];
    }
}
