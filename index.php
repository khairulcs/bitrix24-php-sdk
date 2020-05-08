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
// echo "<pre>";
// print_r($arCurrentB24User);
// echo "</pre>";


// $task_id = $_REQUEST['data']['FIELDS_AFTER']['ID'];
$task_id = 284;
$obB24Task = new \Bitrix24\Task\Item($obB24App);
$arCurrentB24Task = $obB24Task->getData($task_id);
echo "<pre>";
print_r($arCurrentB24Task);
echo "</pre>";


// TODO: Notify user in lark
/*
function writeRaw($data, $title = '') {
  //$app_access_token = "t-bb622eb78b82d0a10286fcb7880cf07c7afbbc5b";
  $app_access_token = getAccessToken();
  $event = $data['event'];

  $task_title = "TASK_TITLE";
  $task_creator = "USER_A";

  $task_id = $data['data']['FIELDS_AFTER']['ID'];

  $auth_code = "3d8ba25e0046c6030040b3220000011c504603443b9ac2470d3d3e9df28196d508f3de";
  $url = "https://ramssolgroup.bitrix24.com/rest/tasks.task.get?auth=".$auth_code."&taskId=".$task_id;
  $json = file_get_contents($url);
  $objT = json_decode($json);

  if($objT->result->task->title != '') {
    $task_title = $objT->result->task->title;
    $task_desc = $objT->result->task->description;
  }

  if($objT->result->task->creator->name != '') {
    $task_creator = $objT->result->task->creator->name;
    $creator_icon = $objT->result->task->creator->icon;
  }

  if($objT->result->task->responsible->name != '') {
    $task_resp = $objT->result->task->responsible->name;
    $resp_icon = $objT->result->task->responsible->icon;
    if($objT->result->task->status == 3) {
      $rAction = "STARTED";
    } else if($objT->result->task->status == 2) {
      $rAction = "PAUSED";
    } else if($objT->result->task->status == 5) {
      $rAction = "FINISHED";
    }

  }


  $chat_id = "oc_7490f4b7d043444d4d12acf2d6112e33";
  if($event == "ONTASKADD") {
    $event_title = "Task - Add task";
    $event_body = $task_creator." added a new task - ".$task_title;
  }

  if($event == "ONTASKUPDATE") {
    $event_title = "Task - Update task";
    $event_body = $task_resp." ".$rAction." ".$task_title;
  }

  if($event == "ONCRMLEADADD") {
    $event_title = "CRM - Add lead";
    $event_body = "USER_A has created a new lead";
  }

  if($event == "ONCRMLEADUPDATE") {
    $event_title = "CRM - Update lead";
    $event_body = "USER_A has updated his lead";
  }

  $curl = curl_init();

  curl_setopt_array($curl, array(
  CURLOPT_URL => "https://open.larksuite.com/open-apis/message/v4/send/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>'{
    "chat_id": "'.$chat_id.'",
    "msg_type": "interactive",
    "update_multi": false,
    "card": {
      "config": {
        "wide_screen_mode": false
      },
      "header": {
        "title": {
          "tag": "plain_text",
          "content": "'.$event_title.'"
        }
      },
      "elements":
      [
       {
         "tag": "div",
         "text": {
           "tag": "plain_text",
           "content": "'.$event_body.'"
	 }
       },
       {
         "tag": "hr"
       },
       {
         "tag": "action",
         "actions": [{
           "tag": "button",
           "text": {
             "tag": "plain_text",
             "content": "View in Bitrix24"
	   },
           "url":"https://ramssolgroup.bitrix24.com/company/personal/user/1/tasks/task/view/'.$task_id.'/",
           "type": "default"
         }]
       }
      ]
    }
  }',
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/json",
    "Authorization: Bearer ".$app_access_token,
    "Content-Type: text/plain"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

 $log = "\n------------------------\n";
 $log .= date("Y.m.d G:i:s") . "\n";
 $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
 $log .= $event;
 $log .= "\n------------------------\n";
 file_put_contents(getcwd() . '/hook2.log', $log, FILE_APPEND);
 return true;
}
*/
