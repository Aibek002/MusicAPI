<?php


namespace app\components;
use Yii;
use app\models\User;
use yii\authclient\ClientInterface;
use yii\web\User as WebUser;


class AuthHandler
{
    private $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    public function handle()
    {
        $userAttributes = $this->client->getUserAttributes();
        $email = $userAttributes['email'] ?? null;
        $name = $userAttributes['given_name'] ?? null;
        $surname = $userAttributes['family_name'] ?? null;


        if ($email === null) {
            throw new \Exception('Не получилось получить email от провайдера.');
        }

        $user = User::find()->where(['email' => $email])->one();
        // $auth = Yii::$app->authManager->getRoles();
        $rolesFromKeycloak = $userAttributes['roles'];
        

        if ($user) {

            Yii::$app->user->login($user);

        } else {

            $user = new User([
                'email' => $email,
                'name' => $name,
                'surname' => $surname
            ]);


            if ($user->save()) {
                $roles_array = []; // Инициализация массива

                // Добавляем роли в массив
                foreach ($rolesFromKeycloak as $roleName) {
                    $role = Yii::$app->authManager->getRole($roleName); // Получаем роль по имени
                
                    if ($role) {
                        $roles_array[] = $role->name; // Добавляем имя роли в массив
                    }
                }
                
                // Проходим по массиву и выполняем действия с каждой ролью
                foreach ($roles_array as $role_name) {
                    $getSingleRole = Yii::$app->authManager->getRole($role_name);
                    $id_user = $user->getId();

                    if ($getSingleRole) {
                        if (!Yii::$app->authManager->checkAccess($id_user, $role)) {
                            Yii::$app->authManager->assign($getSingleRole, $id_user);
                        }

                    }
                }

                Yii::$app->user->login($user);

            } else {
                throw new \Exception('Не удалось создать нового пользователя');
            }

        }
        return $user;

    }

}