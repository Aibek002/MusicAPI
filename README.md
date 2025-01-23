# MusicAPI

MusicAPI — это API для работы с музыкальными данными, предоставляющее доступ к информации о песнях, альбомах, исполнителях и других музыкальных сущностях. Это API может быть использовано для интеграции музыкальных сервисов, создания музыкальных приложений и других задач, связанных с музыкой.

## Установка

### Клонирование репозитория

Сначала клонируйте репозиторий на свой локальный компьютер:

```bash
git clone https://github.com/Aibek002/musicapi.git
```
###  Запуск с помощью Docker
Для того чтобы запустить проект с использованием Docker, выполните следующие шаги:

#### Перейдите в директорию проекта:
```bash
cd musicapi    
```
####  Запустите Docker контейнеры:
```bash
docker-compose up --build
```
Это создаст и запустит контейнеры, установит зависимости и запустит приложение.

API будет доступно по адресу http://localhost:80
### ###3. Использование Composer
Если вы хотите установить зависимости вручную, можно использовать Composer:
Установите зависимости:
```bash
composer install
```
### Запуск Keycloak локально
Для работы с аутентификацией через Keycloak вам нужно установить его локально. Вот шаги для этого:

#### 1. Скачивание и установка Keycloak
Перейдите на официальную страницу Keycloak: Keycloak Downloads и скачайте последнюю версию.
Распакуйте архив в удобное место на вашем компьютере.
Перейдите в папку с распакованным Keycloak.
#### 2. Запуск Keycloak
Откройте терминал и перейдите в директорию с распакованным Keycloak.

#### Запустите сервер Keycloak с помощью следующей команды:

  Для Linux/MacOS:
  ```bash
  ./bin/standalone.sh
  ```
  Для Windows:
  ```bash
  .\bin\standalone.bat start-dev --http-port=8180
  ```
  Это запустит сервер Keycloak, который будет доступен по адресу http://localhost:8180

#### Настройка Keycloak
Откройте браузер и перейдите по адресу http://localhost:8080.
Войдите в админскую панель, используя учетные данные администратора (по умолчанию admin/admin).
Создайте новый Realm (например, musicapi).
Создайте Client с типом confidential и укажите URL вашего приложения (например, http://localhost:8000).
#### Запишите Client ID и Client Secret, они понадобятся для настройки интеграции с API.

### Интеграция с MusicAPI
Для интеграции MusicAPI с Keycloak настройте конфигурацию аутентификации с использованием OAuth2. Используйте полученные данные из Keycloak (Client ID и Client Secret), чтобы настроить API для аутентификации через Keycloak.

####  Подключение библиотеки Keycloak
Для того чтобы интегрировать Keycloak с вашим приложением, нужно использовать соответствующие библиотеки. В случае PHP приложения вам нужно будет подключить библиотеку для работы с OAuth2 и Keycloak.

#### Установка библиотеки
Воспользуйтесь Composer для установки библиотеки Keycloak:
```bash
composer require keycloak/keycloak-php
```
#### 2. Настройка клиента в Keycloak
Перейдите в админскую панель Keycloak по адресу http://localhost:8180.

Войдите в админскую панель с учетными данными администратора.

Создайте новый Realm (musicapi).

Создайте новый Client:

Перейдите в Clients -> Create.
Укажите имя клиента, musiccli.
Установите тип клиента как confidential.
Укажите адрес вашего приложения в поле Valid Redirect URIs (например, http://localhost/*).
Включите Standard Flow (для OAuth2) и Direct Access Grants.
Сохраните настройки.
После создания клиента, запишите Client ID и Client Secret, они понадобятся для настройки приложения.
### 1. Настройка компонента authClientCollection
Для начала вам нужно добавить конфигурацию Keycloak в компонент authClientCollection. В вашем файле конфигурации config/web.php или в другом соответствующем месте добавьте следующий код:
```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'keycloak' => [
                'class' => 'yii\authclient\OpenIdConnect',
                'clientId' => 'musiccli', // Ваш Client ID
                'clientSecret' => '9bF9w4mpBxIlrkAnqz95EFAXHYCl88M3', // Ваш Client Secret
                'returnUrl' => 'http://localhost/index.php?r=site/auth-callback', // URL, куда будет направлен редирект после авторизации
                'issuerUrl' => 'http://192.168.122.85:8180/realms/music-api/', // URL для вашего Keycloak (Issuer URL)
                'name' => 'keycloak',
                'validateAuthState' => true,
                'autoRefreshAccessToken' => true, // Автоматическое обновление токенов
                'validateJws' => false, // Отключаем валидацию JWS
                'stateStorage' => [
                    'class' => 'yii\authclient\SessionStateStorage',
                    'session' => 'session',
                ],
                'scope' => 'openid profile', // Запрашиваемые scope (профиль пользователя)
            ],
        ],
    ],
],
```
#### 2. Конфигурация контроллеров
В вашем контроллере SiteController необходимо настроить обработку аутентификации, получения токена и редиректа для callback-а. В коде ниже показано, как это делать.

Обновите метод actions:
```php
public function actions()
{
    return [
        'auth' => [
            'class' => 'yii\authclient\AuthAction',
            'clientCollection' => 'authClientCollection',
        ],
    ];
}
```
Добавьте методы для обработки входа и callback:
actionLogin: Инициирует процесс аутентификации с Keycloak.
```php
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
```
actionAuthCallback: Обрабатывает редирект с Keycloak, получает токен и авторизует пользователя.
```php
public function actionAuthCallback()
{
    try {
        $req = Yii::$app->request;

        // Получаем код из запроса
        $authcode = $req->get("code");

        // Получаем клиента Keycloak
        $cli = Yii::$app->authClientCollection->getClient("keycloak");

        // Получаем токен
        $token = $cli->fetchAccessToken($authcode);
        $accessToken = $token->getToken();
        $refreshToken = $token->getParam('refresh_token');
        $accessTokenExpiresAt = $token->getParam('expires_in');
        $refreshTokenExpiresAt = $token->getParam('refresh_expires_in');

        if (!$token) {
            throw new ServerErrorHttpException("Не удалось получить токен");
        }

        // Устанавливаем токен для клиента
        $cli->setAccessToken($token);

        // Обрабатываем пользователя
        $user = (new AuthHandler($cli, $accessToken, $refreshToken, $accessTokenExpiresAt, $refreshTokenExpiresAt))->handle();

        // Сохраняем токен в куках
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => 'access_token',
            'value' => $accessToken,
            'httpOnly' => true, 
            'secure' => true,
            'expire' => time() + $accessTokenExpiresAt,
        ]));

        // Логиним пользователя
        Yii::$app->user->login($user);

        // Если пользователь не авторизован
        if (Yii::$app->user->isGuest) {
            throw new ServerErrorHttpException("Не удалось авторизовать пользователя");
        }

        return $this->goHome();
    } catch (\Exception $e) {
        Yii::error("Ошибка при аутентификации: " . $e->getMessage());
        throw new ServerErrorHttpException("Ошибка при аутентификации");
    }
}
```
Обновите middleware для проверки авторизации
```php
public function beforeAction($action)
{
    if ($accessToken = Yii::$app->request->cookies->getValue('access_token')) {
        Yii::$app->request->headers->set('Authorization', 'Bearer ' . $accessToken);
    }
    return parent::beforeAction($action);
}
```
 Работа с пользователями
Для работы с пользователями при аутентификации создайте компонент AuthHandler, который будет обрабатывать получение данных пользователя из Keycloak и сохранять их в базу данных:
```php
namespace app\components;

use Yii;
use app\models\User;
use yii\authclient\ClientInterface;

class AuthHandler
{
    private $client;
    private $accessToken;
    private $refreshToken;
    private $AccesstokenExpiresAt;
    private $RefreshtokenExpiresAt;

    public function __construct(ClientInterface $client, $accessToken, $refreshToken, $AccesstokenExpiresAt, $RefreshtokenExpiresAt)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->AccesstokenExpiresAt = $AccesstokenExpiresAt;
        $this->RefreshtokenExpiresAt = $RefreshtokenExpiresAt;
    }

    public function handle()
    {
        $userAttributes = $this->client->getUserAttributes();
        $email = $userAttributes['email'] ?? null;
        $name = $userAttributes['given_name'] ?? null;
        $surname = $userAttributes['family_name'] ?? null;
        $username = $userAttributes['preferred_username'] ?? null;

        $user = User::find()->where(['email' => $email])->one();

        if (!$user) {
            $user = new User([
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'username' => $username,
                'auth_key' => Yii::$app->security->generateRandomString(),
            ]);
        }

        $user->access_token = $this->accessToken;
        $user->refresh_token = $this->refreshToken;
        $user->access_token_expires_at = time() + $this->AccesstokenExpiresAt;
        $user->refresh_token_expires_at = time() + $this->RefreshtokenExpiresAt;

        if (!$user->save()) {
            throw new \yii\db\Exception('Failed to save user data.');
        }

        return $user;
    }
}
```

