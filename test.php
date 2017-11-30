<?
$code = $_POST["code"];
require_once __DIR__.'/vendor/autoload.php';


session_start();

$client = new Google_Client();
$client->authenticate($code);
var_dump($client->getAccessToken());
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
