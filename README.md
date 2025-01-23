# MusicAPI

MusicAPI — это API для работы с музыкальными данными, предоставляющее доступ к информации о песнях, альбомах, исполнителях и других музыкальных сущностях. Это API может быть использовано для интеграции музыкальных сервисов, создания музыкальных приложений и других задач, связанных с музыкой.

## Установка

### 1. Клонирование репозитория

Сначала клонируйте репозиторий на свой локальный компьютер:

```bash
git clone https://github.com/Aibek002/musicapi.git
```
### 2. Запуск с помощью Docker
Для того чтобы запустить проект с использованием Docker, выполните следующие шаги:

#### 1. Перейдите в директорию проекта:
```bash
cd musicapi    
```
#### 2. Запустите Docker контейнеры:
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

#### 1. Подключение библиотеки Keycloak
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

