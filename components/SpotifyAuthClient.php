<?php
namespace app\components;

use yii\authclient\OAuth2;

class SpotifyAuthClient extends OAuth2
{
    public $authUrl = 'https://accounts.spotify.com/authorize';
    public $tokenUrl = 'https://accounts.spotify.com/api/token';
    public $apiBaseUrl = 'https://api.spotify.com/v1';
    public $redirectUri = 'http://localhost/index.php?r=spotify/auth-callback';
    public function init()
    {
        parent::init();
        $this->scope = 'user-read-email user-read-private playlist-read-private';
    }

    protected function initUserAttributes()
    {
        return $this->api('me', 'GET');
    }

    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $request->addHeaders(['Authorization' => 'Bearer ' . $accessToken->getToken()]);
    }
}
