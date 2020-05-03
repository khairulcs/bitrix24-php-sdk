<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('bitrix24');
$log->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));


// init lib
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope($arParams['app']);
$obB24App->setApplicationId($arParams['local.5ea23d7a8a67f7.95443029']);
$obB24App->setApplicationSecret($arParams['xO6V1vibY9EaytkukVum0skzxg4Kvci0LfRJgtvVaP4NqoWfjW']);
 
// set user-specific settings
$obB24App->setDomain($arParams['oauth.bitrix.info']);
$obB24App->setMemberId($arParams['0ee66411b1e9370b0a8adcc65abad92d']);
$obB24App->setAccessToken($arParams['2ce5ae5e0046c6030040b3220000011c5046032ddf5d0602e75fab1adaa8f7c3a752dd']);
$obB24App->setRefreshToken($arParams['1c64d65e0046c6030040b3220000011c5046033a986a4624bc9d4dcdaed50f38b9ccc8']);

// get information about current user from bitrix24
$obB24User = new \Bitrix24\User\User($obB24App);
$arCurrentB24User = $obB24User->current();

var_dump($arCurrentB24User);