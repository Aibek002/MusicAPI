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
        $username = $userAttributes['preferred_username'] ?? null;


        if ($email === null) {
            throw new \Exception('Не получилось получить email от провайдера.');
        }

        $user = User::find()->where(['email' => $email])->one();
        if ($user) {
            Yii::$app->user->login($user);

        } else {

            $user = new User([
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'username' => $username
            ]);


            if ($user->save()) {
                Yii::$app->user->login($user);
            } else {
                throw new \Exception('Не удалось создать нового пользователя');
            }

        }
        return $userAttributes;

    }

}