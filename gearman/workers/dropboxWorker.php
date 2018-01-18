<?php
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
use  League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDBX", "toprakDbxUpload");
$worker->addFunction("toprakDBXRemove", "toprakDbxRemove");
while ($worker->work());

function toprakDbxUpload($job) {
	try{		
		$params = (array)json_decode($job->workload());
		var_dump($params);
		foreach ($params as $param) {
			if(empty($param)){
				return json_encode(array("Error" => "File Does Not Exist","File" => null,"Url" => null, "Remove" => null));
			}
		}
		$token = (string)$params["key"];
		$formid = $params["formid"];
		$file = $params["file"];
		$qid = $params["qid"];
		$folder = $params["folder"];
		$base_path = DIRECTORY_SEPARATOR . "tmp";
		$path = DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. "questionid".$qid . DIRECTORY_SEPARATOR . $file;
		// var_dump($path);
		if(!file_exists($base_path.$path)){
			echo"Error!File.Does.Not.Exist";
			return("Error!File.Does.Not.Exist");
		}
		else{	
			$client = new Client($token);
			$adapter = new DropboxAdapter($client);
			$filesystem = new Filesystem($adapter);
			$stream = fopen($base_path.$path,"r+");
			$filesystem->write($path,$stream);
			fclose($stream);	
			$url = "www.dropbox.com/home/$formid/$folder/questionid$qid";
			var_dump($url);
			return json_encode(array("Error" => 0,"File" => $file, "Url" => $url, "Remove" => $path));	
		}
	}
	catch(Exception $e){
		return json_encode(array("Error" => $e,"File" => null,"Url" => null,"Remove" => null));
	}
}
function toprakDbxRemove($job){
	$params = (array)json_decode($job->workload());
	$token = $params["key"];
	$remove = (array)$params["remove"];
	var_dump(json_decode(json_encode($remove)));
	$remove = $remove["Dropbox"];
	$client = new Client($token);
	$adapter = new DropboxAdapter($client);
	$filesystem = new Filesystem($adapter);
	// $test = $filesystem->listContents("/".$remove);
	$test = "a";
	// $token = (string)$params["key"];
	// $remove = json_decode($params["remove"])["Dropbox"];
	var_dump($test);
	return $test;
}