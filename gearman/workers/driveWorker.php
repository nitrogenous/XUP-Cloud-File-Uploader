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
	var_dump($params,"\n\n\n",$tokens, "\n\nA\n");
 

	$client = new Google_Client();
	$client->setAuthConfig("client_secrets.json");
	$client->setSubject($file);
	$client->setScopes(["https://www.googleapis.com/auth/drive"]);
	$client->setApplicationName("XUP_File_Uploader");


	$client->setAccessToken((string)$tokens["access_token"]);
	if($client->isAccessTokenExpired())
	{
		var_dump("yalala");
		$refresh = $client->refreshToken((string)$tokens["refresh_token"]);
		$drive = new Drive();
		$drive->save($formid,$qid,json_encode(array("access_token" => (string)$refresh["access_token"],"refresh_token" => (string)$tokens["refresh_token"])));
		echo "Key Updated";
	}	
	$service = new Google_Service_Drive($client);
	$folderMeta = new Google_Service_Drive_DriveFile(array("name" => "yololo","mimeType" => "application/vnd.google-apps.folder"));
	$fileMeta = new Google_Service_Drive_DriveFile(array("name" => $file, "parents" => array("yololo")));
	$folder = $service->files->create($folderMeta,array("fields" => "id"));
	$file = $service->files->create($fileMeta,array(
		"data" =>file_get_contents($base_path.$path),
		"mimeType" => "application/octet-stream",
		"uploadType" => "media"));
	var_dump($folder,$file);


	return $job->workload();
}