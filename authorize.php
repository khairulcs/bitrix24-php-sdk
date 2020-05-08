<?php
// Start the session
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/const/bitrix.php';

$params = array(
    "response_type" => "code",
    "client_id" => APPLICATION_ID,
    "redirect_uri" => REDIRECT_URL,
);
$path = "/oauth/authorize/";

Header("HTTP 302 Found");
Header("Location: " . PATH . '://' . DOMAIN . $path . "?" . http_build_query($params));
die();
?>