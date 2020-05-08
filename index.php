<?php
require __DIR__ . '/vendor/autoload.php';

const APPLICATION_ID = 'local.5ea23d7a8a67f7.95443029';
const APPLICATION_SECRET = 'xO6V1vibY9EaytkukVum0skzxg4Kvci0LfRJgtvVaP4NqoWfjW';

const PROTOCOL = 'https';
const DOMAIN = 'ramssolgroup.bitrix24.com';
const REDIRECT_URL = 'http://47.254.237.19/';
const PATH = 'https';

// we get the code or member id
if(empty($_GET['code']) || empty($_GET['member_id'])){
  $params = array(
    "response_type" => "code",
    "client_id" => APPLICATION_ID,
    "redirect_uri" => REDIRECT_URL,
  );
  $path = "/oauth/authorize/";

  Header("HTTP 302 Found");
    Header("Location: ".PATH.'://'.DOMAIN.$path."?".http_build_query($params));
    die();
}

// create a logger object
$log = new \Monolog\Logger('bitrix24');
$log->pushHandler(new \Monolog\Handler\StreamHandler('log/error.log', \Monolog\Logger::INFO));

// create bitrix24 objects
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope(['task', 'entity']);
$obB24App->setApplicationId(APPLICATION_ID);
$obB24App->setApplicationSecret(APPLICATION_SECRET);

// user data
$obB24App->setDomain(DOMAIN);
$obB24App->setRedirectUri(REDIRECT_URL);
$obB24App->setMemberId($_GET['member_id']);
$obB24App->setAccessToken($obB24App->getFirstAccessToken($_GET['code'])['access_token']);


$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();
echo "<pre>";
print_r($arCurrentB24User);
echo "</pre>";
