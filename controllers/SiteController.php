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

    public function actionCreatePost($id = null)
    {
        $request = Yii::$app->request;

        $audioFile = UploadedFile::getInstanceByName('nameAudioFile');
        $post = $id ? Post::findOne($id) : new Post();
        $tags = Tags::find()->all();
    
        if ($request->isPost) {
            // Подготовка файла
            $file = $this->prepareFile($audioFile);
    
            // Если новый файл был загружен, обработаем его
            if ($audioFile) {
                // Если пост существует и файл изменен, удаляем старый файл
                if ($post->id && file_exists(Yii::getAlias('@webroot/musicsPost/' . $post->nameAudioFile))) {
                    unlink(Yii::getAlias('@webroot/musicsPost/' . $post->nameAudioFile)); // Удаляем старый файл
                }
    
                // Сжимаем новый файл
                $compressedFile = $this->processAudioFile($audioFile, $file);
                if ($compressedFile) {
                    $this->createPostFormRequest($post, $request, $compressedFile);
                }
            } else {
                // Если файл не загружен, используем старый файл
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
        if ($request->isPost) {
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
