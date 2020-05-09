<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/readwritefile.php';

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

$readwrite = new readwritefile();
$tokens = $readwrite->read('tokens.php');
$access_token = $tokens['access_token'];
$refresh_token = $tokens['refresh_token'];

$obB24App->setAccessToken($access_token);
$access_token_expired = $obB24App->isAccessTokenExpire();

// set refresh token
$obB24App->setRefreshToken($refresh_token);

// renew token if expired
if (!$access_token_expired) {
    $renew_token = $obB24App->getNewAccessToken();
    $access_token = $renew_token['access_token'];
    $refresh_token = $renew_token['refresh_token'];
}

$array_tokens = array(
    'code' => $tokens['code'],
    'member_id' => $tokens['member_id'],
    'access_token' => $access_token,
    'refresh_token' => $refresh_token,
);
$readwrite->write('tokens.php', $array_tokens);
echo "<a href='refresh-token.php'>Check expired AT</a>";
var_dump($renew_token);
