<?php
namespace app\components;

use Yii;
use app\models\User;
use yii\authclient\ClientInterface;

class AuthHandler
{
    private $client;



    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle()
    {

        $userAttributes = $this->client->api('me');
        if ($userAttributes === false) {
            Yii::error('Ошибка получения данных пользователя: ' . json_encode($this->client->getLastResponse()));
            die;
        }
        $email = $userAttributes['email'] ?? null;
        $username = $userAttributes['display_name'] ?? null;
        $user = User::find()->where(['email' => $email])->one();

        if (!$user) {
            $user = new User([
                'email' => $email,
                'username' => $username,
                'auth_key' => Yii::$app->security->generateRandomString(),
                'access_token' => $_SESSION['access_token'],
                'access_token_expires_at' => $_SESSION['access_token_expires_at'],
                'refresh_token' => $_SESSION['refresh_token'],
            ]);
        }else{
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->access_token = $_SESSION['access_token'];
            $user->access_token_expires_at = $_SESSION['access_token_expires_at'];
            $user->refresh_token = $_SESSION['refresh_token'];
            $user->refresh_token_expires_at = $_SESSION['refresh_token_expires_at'];

        }

        if (!$user->save()) {
            throw new \yii\db\Exception('Failed to save user data.');
        }



        return $user;
    }
}
