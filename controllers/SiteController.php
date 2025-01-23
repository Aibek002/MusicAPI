<?php

namespace app\controllers;

use app\components\AuthHandler;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Url;
use Yii;
use yii\httpclient\Client;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Tags;
use app\models\Post;
use app\models\User;
use yii\data\Pagination;
use yii\data\Sort;
use yii\web\UploadedFile;
use yii\web\ServerErrorHttpException;

class SiteController extends Controller
{


    public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBearerAuth::class,
        'except' => ['auth-callback'], 
        'optional'=>['login'],
       
    ];
   
    

    return $behaviors;

}
public function beforeAction($action)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $accessToken = $_SESSION['access_token'] ?? null;
    $refreshToken = $_SESSION['refresh_token'] ?? null;
    $expiresAt = $_SESSION['token_expires_at'] ?? null;
    
    // var_dump($accessToken);die;

     if ($accessToken && $expiresAt && $expiresAt - time() < 300) { 
        if ($refreshToken) {
            $newTokens = $this->refreshAccessToken($refreshToken);
            // var_dump($newTokens);die;
            if ($newTokens) {
                $accessToken = $newTokens['access_token'];
                $refreshToken = $newTokens['refresh_token'];
                $expiresAt = $newTokens['token_expires_at'];
            } else {
                Yii::$app->response->redirect(['site/login'])->send();
                return false; 
            }
        } else {
            Yii::$app->response->redirect(['site/auth'])->send();
            return false; 
        }
    }
     if ($this->action->id !== 'auth-callback'&&$this->action->id !== 'login' && !$accessToken) {
        Yii::$app->response->redirect(['site/login'])->send();
        return false; 
    }
    if($accessToken){
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $accessToken);
    }
    return parent::beforeAction($action);
}

private function refreshAccessToken($refreshToken)
{
    $client = new \GuzzleHttp\Client(); // Используем Guzzle для HTTP-запросов

    try {

        $response = $client->post('http://192.168.122.85:8180/realms/music-api/protocol/openid-connect/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => 'musiccli',
                'client_secret' => '9bF9w4mpBxIlrkAnqz95EFAXHYCl88M3',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $user = User::findOne(['refresh_token' => $refreshToken]);
        $_SESSION['access_token'] = $data['access_token'];
        $_SESSION['refresh_token'] = $data['refresh_token'];
        $_SESSION['token_expires_at'] = time() + $data['expires_in'];
        
        if($user){

        $user->access_token = $data['access_token'];
        $user->refresh_token = $data['refresh_token'];
        $user->access_token_expires_at = time() + $data['expires_in'];
        $refreshTokenExpiresIn = $data['refresh_expires_in'] ?? 0;

        if(!$user->save()){
        Yii::error('Ошибка при сохранении обновлённых токенов для пользователя');
        }
    }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'token_expires_at' => time() + $data['expires_in'],
        ];
    } catch (\Exception $e) {
        Yii::error('Failed to refresh token: ' . $e->getMessage());
        return null; 
    }
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
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;    
        if ($exception instanceof UnauthorizedHttpException) {
            return $this->redirect(['site/login']);
        }

        return $this->render('error', ['exception' => $exception]);
    }
    public function actionIndex()
    {
        $tagId = Yii::$app->request->get('tag_id');
        $query = Post::find();
        $tags = Tags::find()->all();

        if ($tagId) {
            $query->andWhere(['tag_id' => $tagId]);
        }

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(), 
        ]);



        $sort = new Sort([
            'attributes' => [
                'titlePost' => [
                    'asc' => ['titlePost' => SORT_ASC],
                    'desc' => ['titlePost' => SORT_DESC],
                    'default' => SORT_ASC,
                    'label' => 'Title'
                ],

                'createAt' => [
                    'asc' => ['createAt' => SORT_ASC],
                    'desc' => ['createAt' => SORT_DESC],
                    'default' => SORT_ASC,
                    'label' => 'Date Created'
                ]
            ]
        ]);
        $query->orderBy($sort->orders);
        $post_music = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'post_music' => $post_music,
            'pagination' => $pagination,
            'tags' => $tags,
            'sort' => $sort
        ]);
       
    }
   
    



    public function actionLogin()
    {
        
            $user = Yii::$app->user;
            if (!$user->isGuest && $user->identity->access_token) {
                return $this->redirect(['site/index']);
            }
            $cli = Yii::$app->authClientCollection->getClient("keycloak");
            $authUrl = $cli->buildAuthUrl();
            return $this->redirect($authUrl);

    }


    public function actionCreatePost($id = null)
    {
        $request = Yii::$app->request;

        $audioFile = UploadedFile::getInstanceByName('nameAudioFile');
        $post = $id ? Post::findOne($id) : new Post();
        $tags = Tags::find()->all();

        if ($request->isPost) {
            $file = $this->prepareFile($audioFile);

            if ($audioFile) {
                if ($post->id && file_exists(Yii::getAlias('@webroot/musicsPost/' . $post->nameAudioFile))) {
                    unlink(Yii::getAlias('@webroot/musicsPost/' . $post->nameAudioFile)); 
                }

                $compressedFile = $this->processAudioFile($audioFile, $file);
                if ($compressedFile) {
                    $this->createPostFormRequest($post, $request, $compressedFile);
                }
            } else {
                $this->createPostFormRequest($post, $request, $post->nameAudioFile);
            }
        }

        return $this->render('create-post', ['post' => $post, 'tags' => $tags]);
    }
    private function compressMp3($filePath)
    {
        $compressedFilePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '_compressed.mp3';
        $compressedFile = pathinfo($filePath, PATHINFO_FILENAME) . '_compressed.mp3';
        $command = "ffmpeg -i $filePath -vn -ar 44100 -ac 2 -b:a 128k $compressedFilePath";
        exec($command);

        if (file_exists($compressedFilePath)) {
            Yii::$app->session->setFlash('success', 'File successfully compresed');
            unlink($filePath);
            return $compressedFile;
        }

        return $filePath;
    }
    private function prepareFile($audioFile)
    {
        if (!$audioFile) {
            return null;
        }

        $directory = Yii::getAlias('@webroot/musicsPost');
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return  uniqid('audio_', true) . '.' . $audioFile->extension;
    }
    private function createPostFormRequest($post, $request, $file)
    {
        $tags_id = $request->post('tags', []);
        foreach ($tags_id as $tag_id) {
            $post->titlePost = $request->post('titlePost');
            $post->descriptionPost = $request->post('descriptionPost');
            $post->nameAudioFile = $file;
            $post->tag_id = $tag_id;
            $post->postCreator = Yii::$app->user->getId();
            $this->savePost($post);
        }
    }
    private function processAudioFile($audioFile, $file)
    {
        $filePath = Yii::getAlias('@webroot/musicsPost') . '/' . $file;
        if ($audioFile && $file) {
            if (!$audioFile->saveAs($filePath)) {
                return false;
            }
        }



        return  $compresedFile = $this->compressMp3($filePath);
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

    public function actionCreateTags()
    {

        $request = Yii::$app->request;
        $tags = new Tags();
        if ($request->post('tags')) {
            
            $tags->tag_type = $request->post('tags');
            if ($tags->save()) {
                Yii::$app->session->setFlash('success', 'Tags created successfully!');
                return $this->redirect(['site/create-post']);
            }
        }
        return $this->render('create-tags', ['tags' => $tags]);
    }
    public function actionDelete($id)
    {
        $post = Post::findOne($id);

        if (!$post) {
            throw new \yii\web\NotFoundHttpException('Post not found.');
        }

        if ($post->postCreator !== Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to delete this post.');
        }
        $filePath = Yii::getAlias('@webroot/musicsPost/' . $post->nameAudioFile);


        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if ($post->delete()) {
            Yii::$app->session->setFlash('success', 'Post successfully deleted!');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete the post.');
        }

        return $this->redirect(['site/index']);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout(); 
        return $this->redirect(['site/index']);
       
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

            $token = $cli->fetchAccessToken($authcode);
            $accessToken = $token->getToken();
            $refreshToken =$token->getParam('refresh_token');
            $AccesstokenExpiresAt =$token->getParam('expires_in');
            $RefreshtokenExpiresAt =$token->getParam('refresh_expires_in');
            // var_dump($accessToken);die;
                // Сохранение токенов
                $_SESSION['access_token'] = $accessToken;
                $_SESSION['refresh_token'] = $refreshToken;
                $_SESSION['token_expires_at'] = time() + $AccesstokenExpiresAt;
                $_SESSION['refresh_token_expires_at'] = time() + $RefreshtokenExpiresAt;
            
            if(!$token){
                throw new ServerErrorHttpException("Не удалось получить токен");

            }
            $cli->setAccessToken($token);
            $user = (new AuthHandler($cli, $accessToken, $refreshToken,$AccesstokenExpiresAt,$RefreshtokenExpiresAt))->handle();

            Yii::$app->user->login($user);
            if (Yii::$app->user->isGuest) { 

                $msg = Yii::t("site", "Не удалось авторизовать пользователя");
                throw new ServerErrorHttpException($msg);
            }else{
                return $this->goHome();
            }

        } catch     (\Exception $e) {   
            Yii::error("Ошибка при аутентификации: " . $e->getMessage());
            throw new ServerErrorHttpException("Ошибка при аутентификации");
        }
    }
}
