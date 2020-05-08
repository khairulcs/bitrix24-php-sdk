<?php
// Start the session
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

// get code and member id
$params = array(
    "response_type" => "code",
    "client_id" => APPLICATION_ID,
    "redirect_uri" => REDIRECT_URL,
);
$path = "/oauth/authorize/";

Header("HTTP 302 Found");
Header("Location: " . PATH . '://' . DOMAIN . $path . "?" . http_build_query($params));
die();

$access_token = $obB24App->getFirstAccessToken($_GET['code']);
print_r($access_token);