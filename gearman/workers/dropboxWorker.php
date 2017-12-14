<?php
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
use  League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDBX", "toprakDbxUpload");

while ($worker->work());

function toprakDbxUpload($job) {
	$params = (array)json_decode($job->workload());
	var_dump($params);
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
	// var_dump($path);

	$stream = fopen($base_path.$path,"r+");
	$filesystem->putStream($path,$stream);
	fclose($stream);	
	$url = "www.dropbox.com/home/$formid/$folder/questionid$qid";
	var_dump($url);
	return $url;	
}