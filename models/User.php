<?php

namespace app\models;


use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword($password)
    {
        // return \Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
        return $this->password_hash === $password;
    }
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);  // Поиск по полю 'username'
    }

  
}
