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
    public function handle()
    {
        $userAttributes = $this->client->getUserAttributes();
        $email = $userAttributes['email'] ?? null;
        $name = $userAttributes['given_name'] ?? null;
        $surname = $userAttributes['family_name'] ?? null;

        if ($email === null) {
            throw new \Exception('Не получилось получить email от провайдера.');
        }

        // Проверяем, есть ли уже пользователь с таким email
        $user = User::find()->where(['email' => $email])->one();

        if ($user) {
            // Если пользователь найден, просто логиним
            Yii::$app->user->login($user);
            return $user;
        }

        // Если пользователь не найден, создаем нового
        $user = new User([
            'email' => $email,
            'name' => $name,
            'surname' => $surname
        ]);

        if ($user->save()) {
            Yii::$app->user->login($user);
            return $user;
        } else {
            throw new \Exception('Не удалось создать нового пользователя');
        }
    }


}