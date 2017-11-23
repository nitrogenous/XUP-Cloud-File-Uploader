<?php
require_once("/www/v3/toprak/Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
use  League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDBX", "toprakDbxUpload");

while ($worker->work());

function toprakDbxUpload($job) {
	$params = (array)json_decode($job->workload());
	$token = $params["key"];
	$formid = $params["formid"];
	$file = $params["file"];
	$client = new Client($token);
	$adapter = new DropboxAdapter($client);
	$filesystem = new Filesystem($adapter);
	$path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $file;

	$stream = fopen($path,"r+");
	$filesystem->writeStream($file,$stream);
	fclose($stream);

	return $job->workload();;	
}