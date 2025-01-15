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
        // print_r($cli);

    }

    /**
     * Logout action.
     *
     * @return Response
     */
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
        $req = Yii::$app->request;
        $authcode = $req->get("code");


        $cli = Yii::$app->authClientCollection->getClient("keycloak");
        $cli->fetchAccessToken($authcode);

        $token = $cli->getAccessToken();
        $cli->setAccessToken($token);
        $user = (new AuthHandler($cli))->handle();
        print_r($user);
        // Yii::$app->user->login($user);
        Yii::$app->user->login($user);

        if (Yii::$app->user->isGuest) {

            $msg = Yii::t("site", "Не удалось авторизовать пользователя");
            throw new ServerErrorHttpException($msg);
        }

        return $this->redirect(Url::to("index"));

        // echo '<pre>' . print_r($user, true) . '</pre>';


    }
}