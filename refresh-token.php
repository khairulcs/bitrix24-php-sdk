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

$access_token_expired = $obB24App->isAccessTokenExpire();
echo "<pre>";
print_r($access_token_expired);
echo "</pre>";
