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
	$path =  $folder . DIRECTORY_SEPARATOR. "questionid".$qid;
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
	
	$pagetoken = null;
	$folderid = null;
	do { 
		$driveFiles = $service->files->listFiles();
		foreach ($driveFiles->files as $dFiles) {
			var_dump($dFiles->name);
			if($dFiles->name == $formid)
			{
				$folderid = $dFiles->id;
			}
		}
		$pagetoken = $driveFiles->pageToken;
	} while ($pagetoken != null);

	if($folderid == null)
	{
		$folderMeta = new Google_Service_Drive_DriveFile(array("name" => $formid,"mimeType" => "application/vnd.google-apps.folder"));
		$folder = $service->files->create($folderMeta,array("fields" => "id"));
		$folderid = $folder->getId();
	}

		$pathArray = array_filter(explode("/", $path));
		
		foreach ($pathArray as $paths) {
			$folderMeta = new Google_Service_Drive_DriveFile(array("name" => $paths, "mimeType" => "application/vnd.google-apps.folder","parents" => array($folderid)));
			$folder = $service->files->create($folderMeta,array("fields" => "id"));
			var_dump("Created Subfolder".$paths);
			$folderid = $folder->getId();
		}


	$fileMeta = new Google_Service_Drive_DriveFile(array("name" => $file, "parents" => array($folderid)));
	$fileService = $service->files->create($fileMeta,array(
		"data" =>file_get_contents($base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file),
		"mimeType" => "application/octet-stream",
		"uploadType" => "media"));
	var_dump($folder,$fileService);


	return $job->workload();
}