<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/const/bitrix.php';
require __DIR__ . '/classes/readwritefile.php';

$log = new \Monolog\Logger('bitrix24');
$log->pushHandler(new \Monolog\Handler\StreamHandler('log/error.log', \Monolog\Logger::INFO));

// create bitrix24 objects
$obB24App = new \Bitrix24\Bitrix24(false, $log);
$obB24App->setApplicationScope(['task', 'entity']);
$obB24App->setApplicationId(APPLICATION_ID);
$obB24App->setApplicationSecret(APPLICATION_SECRET);
$obB24App->setDomain(DOMAIN);
$obB24App->setRedirectUri(REDIRECT_URL);

// get code and member id
if (!isset($_GET['code'])) {
    $params = array(
        "response_type" => "code",
        "client_id" => APPLICATION_ID,
        "redirect_uri" => REDIRECT_URL,
    );
    $path = "/oauth/authorize/";

    Header("HTTP 302 Found");
    Header("Location: " . PATH . '://' . DOMAIN . $path . "?" . http_build_query($params));
}
$first_access_token = $obB24App->getFirstAccessToken(@$_GET['code']);
$access_token = $first_access_token['access_token'];
$refresh_token = $first_access_token['refresh_token'];
$readwrite = new readwritefile();
$array_tokens = array(
    'code' => $_GET['code'],
    'member_id' => $_GET['member_id'],
    'access_token' => $access_token,
    'refresh_token' => $refresh_token,
);
$readwrite->write('tokens.php', $array_tokens);
$tokens = $readwrite->read('tokens.php');
// set access token
echo "<pre>";
print_r($first_access_token);
echo "</pre>";
echo "<a href='refresh-token.php?access_token=" . $access_token . "&refresh_token=" . $refresh_token . "'>Check expired AT</a>";
