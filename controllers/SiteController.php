<?php

namespace app\controllers;

use app\components\AuthHandler;
use yii\helpers\Url;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Genre;
use app\models\Post;
use yii\web\UploadedFile;

class SiteController extends Controller
{


    public function behaviors()
    {
        return [
            "access" => [
                "class" => \yii\filters\AccessControl::class,
                "only" => ["logout", "index"],
                "rules" => [
                    // TODO(annad): Logout is post request!
                    ["allow" => true, "actions" => ["index", "logout"], "roles" => ["@"]],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            "error" => [
                "class" => \yii\web\ErrorAction::class,
            ],

            "auth" => [
                "class" => \yii\authclient\AuthAction::class,
                "clientCollection" => "authClientCollection",
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }





    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $cli = Yii::$app->authClientCollection->getClient("keycloak");
        $to = Url::to(["auth", "authclient" => $cli->getName()]);
        return $this->redirect($to);
    }

    public function actionCreatePost()
    {
        $request = Yii::$app->request;

        $audioFile = UploadedFile::getInstanceByName('nameAudioFile');
        $post = new Post();
        $genres = Genre::find()->all();

        if ($request->isPost) {
            $filePath =  $this->prepareFilePath($audioFile);
            if ($this->processAudioFile($audioFile, $filePath)) {
                $this->createPostFormRequest($post, $request, $filePath);
            }
        }
        return $this->render('create-post', ['post' => $post, 'genres' => $genres]);
    }
    private function prepareFilePath($audioFile)
    {
        if (!$audioFile) {
            return null;
        }

        $directory = Yii::getAlias('@webroot/musicsPost');
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory . '/' . uniqid('audio_', true) . '.' . $audioFile->extension;
    }
    private function createPostFormRequest($post, $request, $filePath)
    {
        $genres_id = $request->post('genres', []);
        foreach ($genres_id as $genre_id) {
            $post->titlePost = $request->post('titlePost');
            $post->descriptionPost = $request->post('descriptionPost');
            $post->nameAudioFile = $filePath;;
            $post->genre_id = $genre_id;
            $post->postCreator = Yii::$app->user->getId();
            // print_r($genre_id);
            $this->savePost($post);
        }

        
    }
    private function processAudioFile($audioFile, $filePath)
    {
        if ($audioFile && $filePath) {
            if (!$audioFile->saveAs($filePath)) {
                return false;
            }
        }

        return true;
    }

    private function savePost(Post $post)
    {
        if ($post->validate()) {
            if ($post->save()) {
                Yii::$app->session->setFlash('success', 'Post successfully saved!');
                return $this->redirect(['site/create-post']);
            } else {
                $this->logPostErrors($post);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Validation failed! Please check the form.');
        }
    }
    private function logPostErrors(Post $post)
    {
        $errors = $post->errors;
        $errorMessages = [];

        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $errorMessages[] = "$field: $error";
            }
        }

        Yii::$app->session->setFlash('error', 'Ошибка при сохранении поста: ' . implode(', ', $errorMessages));
    }
    public function actionLogout()
    {
        $user = Yii::$app->user;
        if (!$user->isGuest) {
            $client = Yii::$app->authClientCollection->getClient("keycloak");
            // TODO(annad): You must solve this problem on KeycloakClientWrapper level!
            try {
                $logoutUrl = (new \app\components\KeycloakClientWrapper($client))->getLogoutUrl();
            } catch (\Exception $e) {
                $logoutUrl = Url::base();
            }

            Yii::$app->user->logout($destroySession = true);
            return $this->redirect($logoutUrl);
        }

        return $this->goHome();
    }

    public function actionAbout()
    {
        return $this->render("about");
    }

    public function actionContact()
    {
        return $this->render("contact");
    }

    public function actionAuthCallback()
    {
        try {
            $req = Yii::$app->request;
            $authcode = $req->get("code");
            $cli = Yii::$app->authClientCollection->getClient("keycloak");
            $cli->fetchAccessToken($authcode);

            $token = $cli->getAccessToken();
            $cli->setAccessToken($token);
            $user = (new AuthHandler($cli))->handle();

            Yii::$app->user->login($user);

            if (Yii::$app->user->isGuest) {

                $msg = Yii::t("site", "Не удалось авторизовать пользователя");
                throw new ServerErrorHttpException($msg);
            }

            return $this->redirect(Url::to("index"));
        } catch (\Exception $e) {
            Yii::error("Ошибка при аутентификации: " . $e->getMessage());
            throw new ServerErrorHttpException("Ошибка при аутентификации");
        }
    }
}
