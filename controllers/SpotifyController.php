<?php

namespace app\controllers;

use app\components\AuthHandler;
use Yii;
use yii\web\Controller;
use yii\authclient\ClientInterface;

class SpotifyController extends Controller
{
    // public function actions()
    // {
    //     return [
    //         'auth' => [
    //             'class' => 'yii\authclient\AuthAction',
    //             'successCallback' => [$this, 'onAuthSuccess'],
    //         ],
    //     ];
    // }
    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionLogin()
    {
        $user = Yii::$app->user;
        if (!$user->isGuest && $user->identity->access_token) {
            return $this->redirect(['spotify/index']);
        }
        $client = Yii::$app->authClientCollection->getClient('spotify');
        $auth = $client->buildAuthUrl();
        return $this->redirect($auth);
    }
    public function actionAuthCallback()
    {
        $client = Yii::$app->authClientCollection->getClient('spotify');
        $authcode = Yii::$app->request->get('code');

        if ($authcode) {
            $accessToken = $client->fetchAccessToken($authcode);


            if ($accessToken) {
                // Получаем данные пользователя
                $client->setAccessToken($accessToken);
                $userAttributes = $client->api('me');

                $user = (new AuthHandler($client));
                var_dump($user);
                die;
                if (Yii::$app->user->login($user)) {
                    Yii::$app->session->setFlash('success', 'Вы вошли как ' . Yii::$app->user->username);

                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка аутентификации');

                }

            } else {
                Yii::$app->session->setFlash('error', 'Ошибка аутентификации');
            }
        }

        return $this->redirect(['/spotify/index']);
    }

    public function actionProfile()
    {
        $user = Yii::$app->session->get('spotify_user');
        if (!$user) {
            return $this->redirect(['auth', 'authclient' => 'spotify']);
        }
        return $this->render('profile', ['user' => $user]);
    }
}
