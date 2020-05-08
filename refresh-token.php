<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/const/bitrix.php';

$log = new \Monolog\Logger('bitrix24');
$log->pushHandler(new \Monolog\Handler\StreamHandler('log/error.log', \Monolog\Logger::INFO));

// create bitrix24 objects
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope(['task', 'entity']);
$obB24App->setApplicationId(APPLICATION_ID);
$obB24App->setApplicationSecret(APPLICATION_SECRET);
$obB24App->setDomain(DOMAIN);
$obB24App->setRedirectUri(REDIRECT_URL);

// check if code expired
$access_token = $_GET['access_token'];
$refresh_token = $_GET['refresh_token'];
$obB24App->setAccessToken($access_token);
$access_token_expired = $obB24App->isAccessTokenExpire();

// set refresh token
$obB24App->setRefreshToken($refresh_token);

// renew token
$renew_token = $obB24App->getNewAccessToken();
var_dump($access_token_expired);
