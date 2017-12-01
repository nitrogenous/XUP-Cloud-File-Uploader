<?php
require_once("/www/v3/toprak/Adapter/src/XUP/main.php");
require_once("/www/v3/toprak/Adapter/vendor/autoload.php");
require_once("/www/v3/toprak/Adapter/src/XUP/adapters/drive.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDrive", "toprakDriveUpload");

while ($worker->work());

function toprakDriveUpload($job) {
	$params = (array)json_decode($job->workload());
	$tokens = (array)json_decode($params["key"]);
	$formid = $params["formid"];
	$file = $params["file"];
	$qid = $params["qid"];
	$folder = $params["folder"];
	$base_path = DIRECTORY_SEPARATOR . "tmp";
	$path = DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. "questionid".$qid . DIRECTORY_SEPARATOR . $file;
	var_dump($params,"\n\n\n",$tokens);
 

	$client = new Google_Client();
	$client->setAuthConfig("client_secrets.json");
	$client->setSubject($file);
	$client->setScopes(["https://www.googleapis.com/auth/drive"]);
	$client->setApplicationName("XUP_File_Uploader");
	//client->Authenticate("4/Qkb_ayXJvR6dn_fNi1dx2vL47gWyETPf3EquFhdFIDU");

	$client->setAccessToken((string)$tokens["access_token"]);
	// if($client->isAccessTokenExpired())
	// {
	// 	$refresh = $client->refreshToken((string)$tokens["refresh_token"]);
	// 	$drive = new Drive();
	// 	$drive->save($formid,$qid,json_encode(array("access_token" => (string)$tokens["access_token"],"refresh_token" => (string)$tokens["refresh_token"])));
	// 	$client->setAccessToken((string)$tokens["access_token"]);
	// 	echo "Key Updated";
	// }
	$service = new Google_Service_Drive($client);
	$gfile = new Google_Service_Drive_DriveFile();
	$result = $service->files->create($gfile,array(
		"data" =>file_get_contents($base_path.$path),
		"mimeType" => "application/octet-stream",
		"uploadType" => "media"				));
	var_dump($result);


	return $job->workload();
}