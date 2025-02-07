<?php
namespace app\models;

use yii\base\Model;

class SearchTrackForm extends Model
{
    public $query;

    public function rules()
    {
        return [
            [['query'], 'required'],
            [['query'], 'string', 'max' => 255],
        ];
    }
}
