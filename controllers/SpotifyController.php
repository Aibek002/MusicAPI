<?php

namespace app\controllers;

use app\components\AuthHandler;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Controller;
use yii\authclient\ClientInterface;

class SpotifyController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'login'],

        ];



        return $behaviors;
    }
    public function beforeAction($action)
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $accessToken = $_SESSION['access_token'] ?? null;
        // var_dump($accessToken);die;
        if ($accessToken) {
            Yii::$app->request->headers->set('Authorization', 'Bearer ' . $accessToken);
        }
        return parent::beforeAction($action);
    }
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
        $authcode = Yii::$app->request->get('code');
        $client = Yii::$app->authClientCollection->getClient('spotify');

        if ($authcode) {
            $token = $client->fetchAccessToken($authcode);
            $this->setSession($token);

            if (!$token) {
                Yii::$app->session->setFlash('error', 'Ошибка аутентификации');
            }
            // Получаем данные пользователя
            $client->setAccessToken($token);

            $user = (new AuthHandler($client))->handle();

            // var_dump($user);
            // die;
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Вы вошли как ' . Yii::$app->user->identity->username);

            } else {
                Yii::$app->session->setFlash('error', 'Ошибка аутентификации');

            }



        }

        return $this->redirect(['/spotify/index']);
    }
    public function setSession($token)
    {
        $_SESSION['access_token'] = $token->getToken();
        $_SESSION['refresh_token'] = $token->getParam('refresh_token');
        $_SESSION['token_expires_at'] = time() + $token->getParam('expires_in');
        $_SESSION['refresh_token_expires_at'] = time() + $token->getParam('refresh_expires_in');
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
