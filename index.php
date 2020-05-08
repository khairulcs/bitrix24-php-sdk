<?php
require __DIR__ . '/vendor/autoload.php';
require_once('classes/send.php');
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
$specUser = $obB24User->get('name','ASC',$filter);
echo "<pre>";
//print_r($specUser);
echo "</pre>";


$task_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];

if($task_id == "") {
	$task_id = 16515;
}
$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
//echo "<pre>";
//print_r($arCurrentB24Task);
//echo "</pre>";
$responsible_id = $arCurrentB24Task['result']['RESPONSIBLE_ID'];

// get user by id
$filter = array(
        'ID' => $responsible_id
);

$specUser = $obB24User->get('name','ASC',$filter);
//echo "<pre>";
//print_r($specUser);
//echo "</pre>";
echo $responsible_email = $specUser['result'][0]['EMAIL'];

//writeRaw($_REQUEST, 'incoming');
// TODO: Notify user in lark


$data = array(
    'email' => 'khairul.ariffin@feets.me',
    'msg_type' => 'text',
    'content' => array(
         'text' => 'Hello there kidsos'
        )
);
$app_access_token = "t-4b98896733af3970ecf73d47f0d96c2de29d9da4";
$payload = json_encode($data);

$funcSendMessage = new message();

$send = $funcSendMessage->send($app_access_token, $payload);
print($send);
?>
