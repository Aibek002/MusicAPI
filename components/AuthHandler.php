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
        var_dump($userAttributes); die;
        $email = $userAttributes['email'] ?? null;
        $username = $userAttributes['display_name'] ?? null;

        $user = User::find()->where(['email' => $email])->one();

        if (!$user) {
            $user = new User([
                'email' => $email,
                'username' => $username,
                'auth_key' => Yii::$app->security->generateRandomString(),
            ]);
        }

        if (!$user->save()) {
            throw new \yii\db\Exception('Failed to save user data.');
        }
   
    
        
        return $user;
    }
}
