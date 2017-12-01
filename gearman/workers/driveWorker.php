<?php
require_once("/www/v3/toprak/src/XUP/adapters/drive.php");

use XUP\FileUploader\Drive;

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
	var_dump($params);

	$client = new \Google_Client();
	$client->setAuthConfig("client_secrets.json");
	$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
	$client->setRedirectUri("https://toprak.jotform.pro/Adapter/index.html");
	$client->setAccessType("offline");
	$client->setApprovalPrompt("force");
	$client->setIncludeGrantedScopes(true);
	$client->setAccessToken((string)$tokens["access_token"]);
	if($client->isAccessTokenExpired())
	{
		$refresh = $client->refreshToken((string)$tokes["refresh_token"]);
		$drive = new \Drive();
		$drive->save($formid,$qid,json_encode(array("access_token" => (string)$tokens["access_token"],"refresh_token" => (string)$tokens["refresh_token"]));
		echo "Key Updated";
	}


	$client = new Client($token);
	$adapter = new DropboxAdapter($client);
	$filesystem = new Filesystem($adapter);
	$base_path = DIRECTORY_SEPARATOR . "tmp";
	$path = DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. "questionid".$qid . DIRECTORY_SEPARATOR . $file;
	var_dump($path);

	$stream = fopen($base_path.$path,"r+");
	$filesystem->putStream($path,$stream);
	fclose($stream);	
	return $job->workload();
}