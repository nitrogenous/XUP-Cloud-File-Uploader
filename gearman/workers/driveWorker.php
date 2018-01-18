<?php
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter".DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR."autoload.php");
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDrive", "toprakDriveUpload");
$worker->addFunction("toprakDriveRemove", "toprakDriveRemove");

while($worker->work());


function toprakDriveUpload($job) {
	try{		
	$params = (array)json_decode($job->workload());
	var_dump($params);
		// foreach ($params as $param) {
		// 	if(empty($param)){
		// 		return json_encode(array("Error" => 2,"File" => null,"Url" => null));
		// 	}
		// }
		$tokens = (array)json_decode($params["key"]);
		$formid = $params["formid"];
		$file = $params["file"];
		$qid = $params["qid"];
		$folder = $params["folder"];
		$folderKey = $params["folderKey"];
		$base_path = DIRECTORY_SEPARATOR . "tmp";
		$path =  $folder . DIRECTORY_SEPARATOR. "questionid".$qid;
		$file_path = $base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file;
		var_dump($params,"\n\n\n");
		// var_dump($base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file);
		if(!file_exists($file_path)){
			return json_encode(array("Error" => "File Does Not Exist","File" => null,"Url" => null, "Remove" => null));
		}
		else{
			$client = new Google_Client();
			$client->setAuthConfig("client_secrets.json");
			$client->setSubject($file);
			$client->setScopes(["https://www.googleapis.com/auth/drive"]);
			$client->setApplicationName("XUP_File_Uploader");
			$client->setAccessToken((string)$tokens["access_token"]);
			if($client->isAccessTokenExpired()) {
				$refresh = $client->refreshToken((string)$tokens["refresh_token"]);
				$drive = new Drive();
				$drive->save($formid,$qid,json_encode(array("access_token" => (string)$refresh["access_token"],"refresh_token" => (string)$tokens["refresh_token"])));	
				echo "\nKey Updated\n\n";
			}	 
			$service = new Google_Service_Drive($client);
			$pagetoken = null;
			$folderid = null;
			$isFolder = false;
			do { 
				$driveFiles = $service->files->listFiles();
				foreach ($driveFiles->files as $dFiles) {
					if($dFiles->name == $formid){
						$folderid = $dFiles->getId();
						var_dump($dFiles->getId());	
					}
					if(!empty($folderKey)){
						var_dump($dFiles->getId(), "\n",$folderKey,"\n");
						if($dFiles->getId() == $folderKey){
							$isFolder = true;
						}	
					}
				}
				$pagetoken = $driveFiles->pageToken;
			} 
			while ($pagetoken != null);
			var_dump($isFolder + " " + $folderKey);
			if($isFolder){
				$fileMeta = new Google_Service_Drive_DriveFile(array("name" => $file, "parents" => array($folderKey)));
				$fileService = $service->files->create($fileMeta,array(
					"data" =>file_get_contents($base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file),
					"mimeType" => "application/octet-stream",
					"uploadType" => "media"));
				$url = "www.drive.google.com/#folders/$folderKey";
				$return = json_encode(array("folderKey" => $folderKey,"url" => $url));
				var_dump($return);
				return $return;
			}
			if($folderid == null) {
				$folderMeta = new Google_Service_Drive_DriveFile(array("name" => $formid,"mimeType" => "application/vnd.google-apps.folder"));
				$folder = $service->files->create($folderMeta,array("fields" => "id"));
				$folderid = $folder->getId();
			}
			$pathArray = array_filter(explode("/", $path));
			
			foreach ($pathArray as $paths) {
				$folderMeta = new Google_Service_Drive_DriveFile(array("name" => $paths, "mimeType" => "application/vnd.google-apps.folder","parents" => array($folderid)));
				$folder = $service->files->create($folderMeta,array("fields" => "id"));
				// var_dump("Created Subfolder".$paths);
				$folderid = $folder->getId();
			}
			$fileMeta = new Google_Service_Drive_DriveFile(array("name" => $file, "parents" => array($folderid)));
			$fileService = $service->files->create($fileMeta,array(
				"data" =>file_get_contents($base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file),
				"mimeType" => "application/octet-stream",
				"uploadType" => "media"));
		// var_dump($folder,$fileService);

			$url = "www.drive.google.com/#folders/$folderid";
			var_dump($url);
			return json_encode(array("Error" => 0,"File" => $file,"Url" => $url, "Remove" => $fileService->getId()));
		}
	}
	catch(Exception $e){
		return json_encode(array("Error" => $e,"File" => null,"Url" => null, "Remove" => null));
	}
}
function toprakDriveRemove($job) {
	// $params = (array)json_decode($job->workload());
}
