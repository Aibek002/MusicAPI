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

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
    /**
     * {@inheritdoc}
     */
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

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $cli = Yii::$app->authClientCollection->getClient("keycloak");
        $to = Url::to(["auth", "authclient" => $cli->getName()]);
        return $this->redirect($to);
        // print_r($to);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    

    public function actionAuthCallback()
    {
        $req = Yii::$app->request;
        $authcode = $req->get("code");
        $cli = Yii::$app->authClientCollection->getClient("keycloak");

        if (!$authcode) {
            $msg = Yii::t("site", "Невалидный код авторизации");
            throw new BadRequestHttpException($msg);
        }

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
        
    }
}
