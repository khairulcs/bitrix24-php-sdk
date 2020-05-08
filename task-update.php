<?php
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/send.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/writetolog.php';

$funcWriteToLog = new writetolog();
$funcWriteToLog->call($_REQUEST, 'TASK UPDATE');

$log = new \Monolog\Logger('bitrix24');
$log->pushHandler(new \Monolog\Handler\StreamHandler('log/error.log', \Monolog\Logger::INFO));

// create bitrix24 objects
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope(['task', 'entity']);
$obB24App->setApplicationId(APPLICATION_ID);
$obB24App->setApplicationSecret(APPLICATION_SECRET);
$obB24App->setDomain(DOMAIN);
$obB24App->setRedirectUri(REDIRECT_URL);
$obB24App->setAccessToken($_SESSION['access_token']);

// get current user
$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();

// get task item
$task_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
$responsible_id = $arCurrentB24Task['result']['RESPONSIBLE_ID'];

// log the REQUEST
$funcWriteToLog->call($_REQUEST, 'Task Update');

// get user by id
$filter = array(
    'ID' => $responsible_id,
);
$responsible_user = $obB24User->get('name', 'ASC', $filter);
$responsible_email = $responsible_user['result'][0]['EMAIL'];

//writeRaw($_REQUEST, 'incoming');
// TODO: Notify user in lark
$data = array(
    'email' => 'khairul.ariffin@feets.me',
    'msg_type' => 'text',
    'content' => array(
        'text' => $responsible_email,
    ),
);
$app_access_token = "t-3b7e105da2b5a1f6a9124fb5c50c793d24f907a1";
$payload = json_encode($data);
$funcSendMessage = new message();
$send = $funcSendMessage->send($app_access_token, $payload);
print($send);
