<?php
require_once("/www/v3/toprak/Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
use  League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDrive", "toprakDriveUpload");

while ($worker->work());

function toprakDriveUpload($job) {
	$params = (array)json_decode($job->workload());
	var_dump($params);

	$client = new \Google_Client();
	$client->setAuthConfig("client_secrets.json");
	$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
	$client->setRedirectUri("https://toprak.jotform.pro/Adapter/index.html");
	$client->setAccessType("offline");
	$client->setApprovalPrompt("force");
	$client->setIncludeGrantedScopes(true);
	$client->setAccessToken($code);
	

	$token = (string)$params["key"];
	$formid = $params["formid"];
	$file = $params["file"];
	$qid = $params["qid"];
	$folder = $params["folder"];
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