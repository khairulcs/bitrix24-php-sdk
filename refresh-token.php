<?php
session_start();
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

// renew token if expired
if(!$access_token_expired) {
    $renew_token = $obB24App->getNewAccessToken();
    $access_token = $renew_token['access_token'];
    $refresh_token = $renew_token['refresh_token'];
}
$_SESSION['access_token'] = $access_token;
$_SESSION['refresh_token'] = $refresh_token;
echo "<a href='refresh-token.php?access_token=".$_SESSION['access_token']."&refresh_token=".$_SESSION['refresh_token']."'>Check expired AT</a>";
var_dump($renew_token);
