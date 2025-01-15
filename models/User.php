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

    // Реализация методов интерфейса IdentityInterface

    public static function findIdentity($id)
    {
        // Найдите пользователя по его ID
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        // Найдите пользователя по токену доступа (если используется)
        return self::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id; // Возвращаем ID пользователя
    }

    public function getAuthKey()
    {
        return $this->auth_key; // Возвращаем ключ авторизации
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    // Дополнительно, можно реализовать метод для валидации пароля
    public function validatePassword($password)
    {
        return \Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }
}
