<?php

namespace app\controllers;

use app\components\AuthHandler;
use app\models\SearchTrackForm;
use Yii;
use yii\helpers\Json;
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
            'optional' => ['index', 'auth-callback', 'login'],

        ];



        return $behaviors;
    }
    public function beforeAction($action)
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $accessToken = $_SESSION['access_token'] ?? null;
        if ($accessToken) {
            Yii::$app->request->headers->set('Authorization', 'Bearer ' . $accessToken);
        }
        return parent::beforeAction($action);
    }
    public function actionIndex()
    {
        $model = new SearchTrackForm();
        if ($model->load(Yii::$app->request->get()) && $model->validate()) {
            $accessToken = Yii::$app->request->headers->get('Authorization'); // Твой токен
            $query=urldecode($model->query);
            $url = "https://api.spotify.com/v1/search?q=" . urlencode($query) . "&type=track";

            $headers = [
                "Authorization:" . $accessToken,
                "Content-Type: application/json",
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $tracks = $data["tracks"]["items"] ?? [];
            } else {
                $tracks = [];
            }
        } else {
            $tracks = [];
        }

        return $this->render('index', [
            'tracks' => $tracks,
           'model' => $model,
        ]);
    }
    public function actionLogin()
    {
        $user = Yii::$app->user;

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

            if (!$token) {
                Yii::$app->session->setFlash('error', 'Ошибка аутентификации');
            }
            $this->setSession($token);
            $client->setAccessToken($token);

            $user = (new AuthHandler($client))->handle();

            if (!$user) {
                Yii::$app->session->setFlash('error', 'Ошибка аутентификации');

            }



        }

        return $this->redirect(['/spotify/index']);
    }
    public function setSession($token)
    {
        $_SESSION['access_token'] = $token->getToken();
        $_SESSION['refresh_token'] = $token->getParam('refresh_token');
        $_SESSION['access_token_expires_at'] = time() + $token->getParam('expires_in');
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
