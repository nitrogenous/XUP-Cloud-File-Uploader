<?
require_once __DIR__.'/vendor/autoload.php';


// session_start();

$code = explode('"',$_POST["code"]);
$del = array('"',"{","}","code",":");
do{
	$old = $code;
	$code = str_replace($del,"",$code);
	$code = array_filter($code);
}
while($old !== $code);
$code = implode($code);
$client = new Google_Client();
$client->authenticate($code);
$client->getAccessToken();
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
