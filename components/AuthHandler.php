<?php
namespace app\components;

use Yii;
use app\models\User;
use yii\authclient\ClientInterface;

class AuthHandler
{
    private $client;
    private $accessToken;
    private $refreshToken;
    private $AccesstokenExpiresAt;
    private $RefreshtokenExpiresAt;


    public function __construct(ClientInterface $client, $accessToken, $refreshToken,$AccesstokenExpiresAt,$RefreshtokenExpiresAt)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->AccesstokenExpiresAt=$AccesstokenExpiresAt;
        $this->RefreshtokenExpiresAt=$RefreshtokenExpiresAt;
    }

    public function handle()
    {
        // Получаем данные о пользователе от клиента (Keycloak)
        $userAttributes = $this->client->getUserAttributes();
        
        // Извлекаем необходимые данные
        $email = $userAttributes['email'] ?? null;
        $name = $userAttributes['given_name'] ?? null;
        $surname = $userAttributes['family_name'] ?? null;
        $username = $userAttributes['preferred_username'] ?? null;

        // Ищем пользователя в базе данных
        $user = User::find()->where(['email' => $email])->one();

        // Если пользователь не найден, создаем нового
        if (!$user) {
            $user = new User([
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'username' => $username,
                'auth_key' => Yii::$app->security->generateRandomString(),
            ]);
        }

    

        // Сохраняем токены
        $user->access_token = $this->accessToken;
        $user->refresh_token = $this->refreshToken;
        $user->access_token_expires_at= time() + $this->AccesstokenExpiresAt;
        $user->refresh_token_expires_at= time() + $this->RefreshtokenExpiresAt;

        // Сохраняем данные пользователя в базе
        if (!$user->save()) {
            throw new \yii\db\Exception('Failed to save user data.');
        }
   
    
        
        return $user;
    }
}
