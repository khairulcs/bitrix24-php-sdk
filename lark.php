<?php
header("Content-Type:application/json");
$post = file_get_contents("php://input");

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/classes/writetolog.php';
require __DIR__ . '/classes/send.php';
require __DIR__ . '/classes/readwritefile.php';

$readwrite = new readwritefile();
$tokens = $readwrite->read('tokens.php');
$access_token = $tokens['access_token'];
$member_id = $tokens['member_id'];
$app_access_token = $tokens['lark_access_token'];
$text_msg = $at_bot_text_msg = $user_open_id = $open_chat_id = $open_id = "";
// decode the post
$arPost = json_decode($post);
if($arPost) {
    
$text_msg = $arPost->event->text;
$at_bot_text_msg = $arPost->event->text_without_at_bot;
$user_open_id = $arPost->event->user_open_id;
$open_chat_id = $arPost->event->open_chat_id;
$open_id = $arPost->event->open_id;
//$text_msg =
$funcWriteToLog = new writetolog();
$funcWriteToLog->call($arPost, "EVENT SUBSCRIPTION");
}

// HELP
$at_bot_help_msg = strtolower($at_bot_text_msg);
$help_msg = strtolower($text_msg);

if ($at_bot_help_msg == ' help' || $help_msg == 'help') {
    $content_body = array(
        "text" => "Bitrix24 help: starts with @Bitrix24 <keyword>
- help
- notify status
- task update status
- whois <email_address>
- subscribe <email_address>",
    );
    // send to chat group
    send_message('chat_id', $open_chat_id, $content_body, $app_access_token);
}

if ($at_bot_text_msg == ' notify status') {
    $content_body = array(
        "text" => "What status?",
    );
    // send to personal chat
    send_message('chat_id', $open_chat_id, $content_body, $app_access_token);
}

// open_id is used for direct message to user
// 'open_id' => $open_id,

if ($at_bot_text_msg == ' task update status') {
    $content_body = array(
        "text" => "No status yet",
    );
    // TODO: Notify user in lark
    $data = array(
        'open_chat_id' => $open_chat_id,
        'msg_type' => 'text',
        'content' => $content_body,
    );
    $payload = json_encode($data);
    $funcSendMessage = new message();
    $send = $funcSendMessage->send($app_access_token, $payload);

    $funcWriteToLog->call($send, 'SEND MESSAGE');
}

// whois email
$check_stripped = strip_string($at_bot_text_msg);
$whois = $check_stripped[1];
$strippedEmail = $check_stripped[2];

$funcWriteToLog->call($check_stripped, 'STRIPPED');
if ($whois == 'whois') {
    $content_body = array(
        "text" => "How do i know?",
    );
    $new_open_id = $open_id;

    // get open id by email
    $emailInfo = get_email_info($strippedEmail, $app_access_token);
    $funcWriteToLog->call($emailInfo, 'EMAIL INFO');
    $new_open_user = $emailInfo->data->email_users->{$strippedEmail};
    $new_open_id = $new_open_user[0]->open_id;
    $funcWriteToLog->call($new_open_id, 'NEW OPEN ID');
    // send to personal chat
    $user = get_user_info($new_open_id, $app_access_token);
    $uName = $user->data->user_info->name;
    $uAvatar = $user->data->user_info->avatar_240;
    $uEmployeeId = $user->data->user_info->employee_id;
    $uLeaderId = $user->data->user_info->leader_employee_id;

    // send message
    $content_body = array(
        "text" => "Name: $uName
Employee ID: $uEmployeeId
Leader Employee ID: $uLeaderId
uAvatar: $uAvatar",
    );
    // send to chat group
    send_message('open_id', $user_open_id, $content_body, $app_access_token);
} // end whois

// subscribe <email>
$check_stripped = strip_string($at_bot_text_msg);
$subscribe = $check_stripped[1];
$subscribeEmail = $check_stripped[2];
$funcWriteToLog->call($check_stripped, "SUBSCRIBE EMAIL");
if ($subscribe == 'subscribe') {
    $search = $subscribeEmail;
    $lines = file('subscribers.txt');
    // Store true when the text is found
    $found = false;
    foreach ($lines as $line) {
        if (strpos($line, $search) !== false) {
            $found = true;
            echo $line;
        }
    }
    // If the text was not found, show a message
    if ($found) {
        // send message
        $content_body = array(
		"text" => "Your email address is already in the subscriber list!"
	);
    } else {
        $content_body = array(
                "text" => "Done!"
        );
        // $file = 'subscribers.txt';
        // // The new person to add to the file
        // $theEmail = $subscribeEmail."\n";
        // // Write the contents to the file, 
        // // using the FILE_APPEND flag to append the content to the end of the file
        // // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        // file_put_contents($file, $theEmail, FILE_APPEND | LOCK_EX);
    }
    // send to chat group
    send_message('open_chat_id', $open_chat_id, $content_body, $app_access_token);
        $funcWriteToLog->call($fp, 'FP SEND MESSAGE');
}

function strip_string($message)
{
    $stripped_msg = explode(" ", $message);
    return $stripped_msg;
}

// function email_exist($email)
// {
//     $lines = file('subscribers.txt');
//     // Store true when the text is found
//     $found = false;
//     foreach ($lines as $line) {
//         if (strpos($line, $search) !== false) {
//             $found = true;
//             echo $line;
//         }
//     }
//     return $found;
// }

// TODO: Notify user in lark
function send_message($id_type, $chat_id, $content_body, $app_access_token)
{
    // $id_type = ['chat_id', 'open_id']
    // $chat_id = based on id_type
    $data = array(
        $id_type => $chat_id,
        'msg_type' => 'text',
        'content' => $content_body,
    );
    $payload = json_encode($data);
    $funcSendMessage = new message();
    $send = $funcSendMessage->send($app_access_token, $payload);

    $funcWriteToLog = new writetolog();
    $funcWriteToLog->call($send, 'SEND MESSAGE');
}

function get_user_info($open_id, $app_access_token)
{
    // $id_type = ['chat_id', 'open_id']
    // $chat_id = based on id_type
    $data = array(
        'open_id' => $open_id,
    );
    $payload = json_encode($data);
    $funcGetId = new message();
    $user_info = $funcGetId->get_user_info($app_access_token, $payload);
    $user = json_decode($user_info);
    return $user;
}

function get_email_info($email, $app_access_token)
{
    // $id_type = ['chat_id', 'open_id']
    // $chat_id = based on id_type
    $data = array(
        'emails' => $email,
    );
    $payload = json_encode($data);
    $funcGetId = new message();
    $email_info = $funcGetId->get_email_info($app_access_token, $payload);
    $respEmail = json_decode($email_info);
    return $respEmail;
}

echo $post;
