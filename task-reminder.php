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
$funcWriteToLog->call($_REQUEST, 'TASK REMINDER');
$readwrite = new readwritefile();
$tokens = $readwrite->read('tokens.php');
$access_token = $tokens['access_token'];
$member_id = $tokens['member_id'];
$app_access_token = $tokens['lark_access_token'];

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

// get all task list
$obB24TaskItems = new \Bitrix24\Task\Items($obB24App);
$dateToday = date("Y-m-d");
$dateTomorrow = date("Y-m-d",strtotime("+1 days"));
$arrOrder = array(
    'ID' => 'DESC',
);
$arrFilter = array(
    "<=DEADLINE" => $dateTomorrow,
    ">=DEADLINE" => $dateToday,
    "<=REAL_STATUS" => 3,
);
$arrTaskData = array("ID", "TITLE", "DEADLINE", "STATUS", "RESPONSIBLE_ID");
$arrNavParam = array();
$todayTaskList = $obB24TaskItems->getList($arrOrder, $arrFilter, $arrTaskData);

// log the task reminder
$funcWriteToLog->call($todayTaskList, 'Task Reminder');
$todayTaskList = $todayTaskList['result'];
// task config
$headerTitle = "TASK DEADLINE REMINDER";
$count = 0;
foreach ($todayTaskList as $key => $value) {
    $count++;
    if (($count % 3) == 0) {
        die();
        sleep(1);
    }
    // get task item
    $task_id = $value['ID'];
    $responsible_id = $value['RESPONSIBLE_ID'];
    $task_title = $value['TITLE'];
    $task_deadline = $value['DEADLINE'];

    // adjust date format
    $task_deadline = date("d-m-Y H:i:s", strtotime($task_deadline));

    // get user by id
    $filter = array(
        'ID' => $responsible_id,
    );
    $responsible_user = $obB24User->get('name', 'ASC', $filter);
    $responsible_email = $responsible_user['result'][0]['EMAIL'];
    $responsible_name = $responsible_user['result'][0]['NAME'];

    // set arrays of card
    $events = array(
        'header_title' => $headerTitle,
        'title' => $task_title,
        'body' => $task_desc,
        'deadline' => $task_deadline,
        'resp_name' => $responsible_name,
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
        "\n**Responsible:** " . $events['resp_name'] .
        "\n**Due Date:** " . $events['deadline'],
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

    $header = array(
        'title' => $header_title
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

    $card = array(
        'config' => $wideScreenMode,
        'header' => $header,
        'elements' => $elements2,
    );

//writeRaw($_REQUEST, 'incoming');
    // TODO: Notify user in lark
    $data = array(
        'email' => 'khairul.ariffin@feets.me',
        'msg_type' => 'interactive',
        'update_multi' => false,
        'card' => $card,
    );
    $payload = json_encode($data);
    $funcSendMessage = new message();
    $send = $funcSendMessage->send($app_access_token, $payload);

    $funcWriteToLog->call($send, 'SEND MESSAGE');
}

// // $task_id = 16517;
// $obB24Task = new \Bitrix24\Task\Item($obB24App);
// $arCurrentB24Task = $obB24Task->getData($task_id);

// // die if no email in the list
// $search = $responsible_email;
// $lines = file('subscribers.txt');
// // Store true when the text is found
// $found = false;
// foreach ($lines as $line) {
//     if (strpos($line, $search) !== false) {
//         $found = true;
//         echo $line;
//     }
// }
// // If the text was not found, show a message
// if (!$found) {
//     $funcWriteToLog->call($found, 'SEND MESSAGE');
//     die();
// }
