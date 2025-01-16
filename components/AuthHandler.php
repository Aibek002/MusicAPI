<?php


namespace app\components;

use Yii;
use app\models\User;
use yii\authclient\ClientInterface;
use yii\web\User as WebUser;


class AuthHandler
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getUserAttribute($userAttributes)
    {

        $email = $userAttributes['email'] ?? null;
        $name = $userAttributes['given_name'] ?? null;
        $surname = $userAttributes['family_name'] ?? null;
        $username = $userAttributes['preferred_username'] ?? null;

        return compact('email', 'name', 'surname', 'username');
    }

    public function validateUser($userData)
    {

        if ($userData['email'] === null) {
            throw new \Exception('Не получилось получить email от провайдера.');
        }
    }

    public function saveUserAttributesOnDataBase($userData)
    {

        $user = new User([
            'email' => $userData['email'],
            'name' => $userData['name'],
            'surname' => $userData['surname'],
            'username' => $userData['username']
        ]);

        if (!$user->save()) {
            throw new \Exception('Не удалось создать нового пользователя');
        }
    }



    public function handle()
    {
        $userAttributes = $this->client->getUserAttributes();
        $userData = $this->getUserAttribute($userAttributes);
        $this->validateUser($userData);
        $user = User::find()->where(['email' => $userData['email']])->one();

        if (!$user) {
            $this->saveUserAttributesOnDataBase($userData);
        }

        return $user;
    }
}
