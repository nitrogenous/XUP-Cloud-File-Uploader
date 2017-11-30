<?
require_once __DIR__.'/vendor/autoload.php';

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
$client->setAuthConfig("client_secrets.json");
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY); 
$client->setRedirectUri("https://toprak.jotform.pro"); 
$client->setAccessType("offline");
$client->setApprovalPrompt("force");
$client->setIncludeGrantedScopes(true); 
$authenticate = $client->authenticate($code);
$access = $client->getAccessToken($code);
var_dump($access["access_token"],"\n",$access["refresh_token"]);
