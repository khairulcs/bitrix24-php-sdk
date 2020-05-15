<?php
error_reporting(E_ALL);
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/send.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/writetolog.php';
require __DIR__ . '/classes/readwritefile.php';

$funcWriteToLog = new writetolog();
$funcWriteToLog->call($_REQUEST, 'TASK UPDATE');
$readwrite = new readwritefile();
$tokens = $readwrite->read('tokens.php');
$access_token = $tokens['access_token'];
$member_id = $tokens['member_id'];
$app_access_token = $tokens['lark_access_token'];

/*
[auth] => Array
        (
            [domain] => ramssolgroup.bitrix24.com
            [client_endpoint] => https://ramssolgroup.bitrix24.com/rest/
            [server_endpoint] => https://oauth.bitrix.info/rest/
            [member_id] => 0ee66411b1e9370b0a8adcc65abad92d
            [application_token] => jc2km60tk6w4xqzv0zt73b6c1im8knyo
        )
 */

//$access_token = $_REQUEST['auth']['application_token'];
//$member_id = $_REQUEST['auth']['member_id'];

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
// $task_id = 16517;
$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
$responsible_id = $arCurrentB24Task['result']['RESPONSIBLE_ID'];
$task_title = $arCurrentB24Task['result']['TITLE'];
$task_desc = $arCurrentB24Task['result']['DESCRIPTION'];
$task_resp_name = $arCurrentB24Task['result']['RESPONSIBLE_NAME'];
$task_resp_last_name = $arCurrentB24Task['result']['RESPONSIBLE_LAST_NAME'];
$task_created_by = $arCurrentB24Task['result']['CREATED_BY'];
$task_deadline = $arCurrentB24Task['result']['DEADLINE'];
$task_group_id = $arCurrentB24Task['result']['GROUP_ID'];
$task_status = $arCurrentB24Task['result']['STATUS'];

$rAction = "EDIT";

if ($task_status == 3) {
    $rAction = "STARTED";
} else if ($task_status == 2) {
    $rAction = "PAUSED";
} else if ($task_status == 5) {
    $rAction = "FINISHED";
}

// task config
$header_title = "TASK UPDATE - " . $rAction;

// adjust date formate
$task_deadline = date("d-m-Y H:i:s", strtotime($task_deadline));
// replace paragraph
$task_desc = str_replace("[P]", "", $task_desc);
$task_desc = str_replace("[/P]", "\n", $task_desc);

$filterGroup = array(
    'ID' => $task_group_id,
);
$workgroups = new \Bitrix24\Sonet\SonetGroup($obB24App);
$group = $workgroups->get('DESC', $filterGroup);
$task_group_name = $group['result'][0]['NAME'];

// log the REQUEST
$funcWriteToLog->call($_REQUEST, 'Task Update');

// get user by id
$filter = array(
    'ID' => $responsible_id,
);
$responsible_user = $obB24User->get('name', 'ASC', $filter);
$responsible_email = $responsible_user['result'][0]['EMAIL'];

// die if no email in the list
$search = $responsible_email;
$lines = file('subscribers.txt');
// Store true when the text is found
$found = false;
foreach($lines as $line)
{
  if(strpos($line, $search) !== false)
  {
    $found = true;
    echo $line;
  }
}
// If the text was not found, show a message
if(!$found)
{
  $funcWriteToLog->call($found, 'SEND MESSAGE');
  die();
}


// get created by user
$filter_created_by = array(
    'ID' => $task_created_by,
);
$created_user = $obB24User->get('name', 'ASC', $filter_created_by);
$created_name = $created_user['result'][0]['NAME'];

// set arrays of card
$events = array(
    'header_title' => $header_title,
    'title' => $task_title,
    'body' => $task_desc,
    'deadline' => $task_deadline,
    'group_name' => $task_group_name,
    'resp_name' => $task_resp_name,
    'created_by' => $created_name,
);

$wideScreenMode = array(
    'wide_screen' => false,
);

$header_title = array(
    'tag' => 'plain_text',
    'content' => $events['header_title'],
);

$combined_body = array(
    'tag' => 'lark_md',
    'content' => "**Title:** " . $events['title'] . 
    "\n**Project:** " . $events['group_name'] . 
    "\n**Created by:** " . $events['created_by'] . 
    "\n**Responsible:** " . $events['resp_name'] . 
    "\n**Due Date:** " . $events['deadline'] . 
    "\n**Details:**\n" . $events['body'],
);

$title = array(
    'tag' => 'lark_md',
    'content' => "**Title:** " . $events['title'],
);

$created = array(
    'tag' => 'lark_md',
    'content' => "**Created by:** " . $events['created_by'],
);

$responsible = array(
    'tag' => 'lark_md',
    'content' => "**Responsible:** " . $events['resp_name'],
);

$project = array(
    'tag' => 'lark_md',
    'content' => "**Project:** " . $events['group_name'],
);

$body = array(
    'tag' => 'lark_md',
    'content' => "**Details:**\n" . $events['body'],
);

$deadline = array(
    'tag' => 'lark_md',
    'content' => "**Due Date:** " . $events['deadline'],
);

$header = array(
    'title' => $header_title,
);

$actions = array(
    array(
        'tag' => 'button',
        'text' => array(
            'tag' => 'plain_text',
            'content' => 'View in Bitrix24',
        ),
        'url' => "https://ramssolgroup.bitrix24.com/company/personal/user/$responsible_id/tasks/task/view/$task_id/",
        'type' => 'default',
    ),
);

$elements2 = array(
    array(
        'tag' => 'div',
        'text' => $combined_body,
    ),
    array(
        'tag' => 'hr',
    ),
    array(
        'tag' => 'action',
        'actions' => $actions,
    ),
);

$elements = array(
    array(
        'tag' => 'div',
        'text' => $title,
    ),
    array(
        'tag' => 'div',
        'text' => $project,
    ),
    array(
        'tag' => 'div',
        'text' => $deadline,
    ),

    array(
        'tag' => 'div',
        'text' => $body,
    ),
    array(
        'tag' => 'hr',
    ),
    array(
        'tag' => 'action',
        'actions' => $actions,
    ),
);
$card = array(
    'config' => $wideScreenMode,
    'header' => $header,
    'elements' => $elements2,
);

//writeRaw($_REQUEST, 'incoming');
// TODO: Notify user in lark
$data = array(
    'email' => $responsible_email,
    'msg_type' => 'interactive',
    'update_multi' => false,
    'card' => $card,
);

$payload = json_encode($data);
$funcSendMessage = new message();
$send = $funcSendMessage->send($app_access_token, $payload);

$funcWriteToLog->call($send, 'SEND MESSAGE');
