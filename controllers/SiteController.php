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
use app\models\Tags;
use app\models\Post;
use yii\data\Pagination;
use yii\data\Sort;
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
        $tagId = Yii::$app->request->get('tag_id'); // ID выбранного тега
        $query = Post::find();
        $tags = Tags::find()->all();

        if ($tagId) {
            $query->andWhere(['tag_id' => $tagId]);
        }

        // Настраиваем пагинацию
        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(), // Общее количество записей
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
        // Применяем лимит и смещение
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
        $tags = Tags::find()->all();

        if ($request->isPost) {
            $file =  $this->prepareFile($audioFile);
            if ($this->processAudioFile($audioFile, $file)) {
                $this->createPostFormRequest($post, $request, $file);
            }
        }
        return $this->render('create-post', ['post' => $post, 'tags' => $tags]);
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

    public function actionCreateTags()
    {

        $request = Yii::$app->request;
        $tags = new Tags();
        if ($request->isPost) {
            $tags->tag_type = $request->post('tags');
            if ($tags->save()) {
                Yii::$app->session->setFlash('success', 'Tags created successfully!');
                return $this->redirect(['site/create-post']);
            }
        }
        return $this->render('create-tags', ['tags' => $tags]);
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
