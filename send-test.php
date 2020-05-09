<?php
error_reporting(E_ALL);
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/send.php';

// set arrays of card

$events = array(
    'title' => 'Testing',
    'body' => 'Testing body',
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
        array(
            'tag' => 'div',
            'text' => $body,
        ),
        array(
            'tag' => 'hr',
        ),
        array(
            'tag' => 'action',
            'actions' => array(
                $actions,
            ),
        ),
    ),
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
    'msg_type' => 'text',
    'update_multi' => false,
    'card' => $card
);
$app_access_token = "t-546ba4b9811d6007629a065984e21c3e3f4911ad";
$payload = json_encode($data);
$funcSendMessage = new message();
$send = $funcSendMessage->send($app_access_token, $payload);
print($send);
?>