<?php
require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('bitrix24');
$log->pushHandler(new StreamHandler('log/error.log', Logger::DEBUG));

$param = array(
    'scope' => 'app',
    'app_id' => 'local.5ea23d7a8a67f7.95443029',
    'app_secret' => 'xO6V1vibY9EaytkukVum0skzxg4Kvci0LfRJgtvVaP4NqoWfjW',
    'domain' => 'oauth.bitrix.info',
    'member_id' => '0ee66411b1e9370b0a8adcc65abad92d',
    'access_token' => '2ce5ae5e0046c6030040b3220000011c5046032ddf5d0602e75fab1adaa8f7c3a752dd',
    'refresh_token' => '1c64d65e0046c6030040b3220000011c5046033a986a4624bc9d4dcdaed50f38b9ccc8'

);

// init lib
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope($param['scope']);
$obB24App->setApplicationId($param['app_id']);
$obB24App->setApplicationSecret($param['app_secret']);
 
// set user-specific settings
$obB24App->setDomain($param['domain']);
$obB24App->setMemberId($param['member_id']);
$obB24App->setAccessToken($param['access_token']);
$obB24App->setRefreshToken($param['refresh_token']);

// get information about current user from bitrix24
$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();

var_dump($arCurrentB24User);
