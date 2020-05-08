<?php
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/send.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/writetolog.php';

$funcWriteToLog = new writetolog();
$funcWriteToLog->call($_REQUEST, 'TASK UPDATE');
$_SESSION['request'] = $_REQUEST;
// set session
if(!isset($_SESSION['task_id'])) {
	//$_SESSION['task_id'] = $_REQUEST['data']['FIELDS_AFTER']['ID'];
	$_SESSION['request'] = $_REQUEST; 
}
        setcookie('task_id', $_REQUEST['data']['FIELDS_AFTER']['ID'], time() + 180, "/"); // 360 = 1 hour
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

	// save into cookie
	setcookie('member_id', $_GET['member_id'], time() + 180, "/"); // 360 = 1 hour
	setcookie('b24_code', $_GET['code'], time() + 180, "/"); // 360 = 1 hour

	$memberId = $_GET['member_id'];
	$b24Code = $_GET['code'];

$funcWriteToLog->call($_REQUEST, 'Session Request');
//session_start();
//if(!isset($_SESSION['task_id'])) {
	$task_id = 16515;
//} else {
//	$task_id = $_SESSION['task_id'];
//}
//echo "Task ID: ".$_SESSION['task_id'];
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
$obB24App->setMemberId($memberId);
$obB24App->setAccessToken($obB24App->getFirstAccessToken($b24Code)['access_token']);


$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();
if(!isset($task_id)) {
    $task_id = 16515;
}

$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
$responsible_id = $arCurrentB24Task['result']['RESPONSIBLE_ID'];

$funcWriteToLog->call($_SESSION['request'], 'Session Request');

// get user by id
$filter = array(
        'ID' => $responsible_id
);

$specUser = $obB24User->get('name','ASC',$filter);
$responsible_email = $specUser['result'][0]['EMAIL'];

//writeRaw($_REQUEST, 'incoming');
// TODO: Notify user in lark
$data = array(
    'email' => 'khairul.ariffin@feets.me',
    'msg_type' => 'text',
    'content' => array(
         'text' => $task_id
        )
);
$app_access_token = "t-6c4699899c25382d5cf5f4bc922e90de994110fa";
$payload = json_encode($data);
$funcSendMessage = new message();
$send = $funcSendMessage->send($app_access_token, $payload);
print($send);
?>
