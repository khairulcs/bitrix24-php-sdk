<?php
error_reporting(E_ALL);
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/send.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/writetolog.php';
require __DIR__ . '/classes/readwritefile.php';

// refresh token
$url = 'http://47.254.237.19/refresh-token.php';
$refresh_tokens = file_get_contents($url);

$funcWriteToLog = new writetolog();
$funcWriteToLog->call($_REQUEST, 'TASK UPDATE');
$readwrite = new readwritefile();
$tokens = $readwrite->read('tokens.php');
$access_token = $tokens['access_token'];
$member_id = $tokens['member_id'];

$log = new \Monolog\Logger('bitrix24');
$log->pushHandler(new \Monolog\Handler\StreamHandler('log/error.log', \Monolog\Logger::INFO));

// create bitrix24 objects
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope(['task', 'entity']);
$obB24App->setApplicationId(APPLICATION_ID);
$obB24App->setApplicationSecret(APPLICATION_SECRET);
$obB24App->setDomain(DOMAIN);
$obB24App->setRedirectUri(REDIRECT_URL);
$obB24App->setMemberId($member_id);
$obB24App->setAccessToken($access_token);

// get current user
$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();

// get task item
$task_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
$responsible_id = $arCurrentB24Task['result']['RESPONSIBLE_ID'];
$task_title = $arCurrentB24Task['result']['task']['title'];
$task_desc = $arCurrentB24Task['result']['task']['description'];

// log the REQUEST
$funcWriteToLog->call($_REQUEST, 'Task Update');

// get user by id
$filter = array(
    'ID' => $responsible_id,
);
$responsible_user = $obB24User->get('name', 'ASC', $filter);
$responsible_email = $responsible_user['result'][0]['EMAIL'];

// set arrays of card

$events = array(
    'title' => $task_title,
    'body' => $task_desc,
);

$wideScreenMode = array(
    'wide_screen' => false,
);

$title = array(
    'tag' => 'plain_text',
    'content' => $events['title'],
);

$body = array(
    'tag' => 'plain_text',
    'content' => $events['body'],
);

$header = array(
    'title' => $title,
);

$actions = array(
    array(
        'tag' => 'button',
        'text' => array(
            'tag' => 'plain_text',
            'content' => 'View in Bitrix24',
        ),
        'url' => "https://ramssolgroup.bitrix24.com/company/personal/user/1/tasks/task/view/$task_id",
        'type' => 'default',
    ),
);

$elements = array(
    array(
        'tag' => 'div',
        'text' => $body,
    ),
    array(
        'tag' => 'hr',
    ),
    array(
        'tag' => 'action',
        'actions' => $actions
    )
);
$card = array(
    'config' => $wideScreenMode,
    'header' => $header,
    'elements' => $elements,
);


//writeRaw($_REQUEST, 'incoming');
// TODO: Notify user in lark
$data = array(
    'email' => 'khairul.ariffin@feets.me',
    'msg_type' => 'interactive',
    'update_multi' => false,
    'card' => $card
);
$app_access_token = "t-546ba4b9811d6007629a065984e21c3e3f4911ad";
$payload = json_encode($data);
$funcSendMessage = new message();
$send = $funcSendMessage->send($app_access_token, $payload);
print($send);

?>
