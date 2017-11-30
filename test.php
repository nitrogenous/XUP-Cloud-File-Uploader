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
var_dump($access);
// if($client->isAccesTokenExpired())
// {
// $refresh = $client->refreshToken("1/sCLILMDOxVDsEDSNo2KNfjvTw2ed0T3KPrqBoKh9OxY");
// }
$service = new Google_Service_Drive($client);
$client->setAccessToken($access["access_token"]);
$file = new Google_Service_Drive_DriveFile();
$result = $service->files->create($file,array("data" =>file_get_contents("/tmp/72912031423950/07-11-55 30-11-17/questionid3/JotformDevLogoMini.png"),"mimeType" => "application/octet-stream","uploadType" => "media")
);