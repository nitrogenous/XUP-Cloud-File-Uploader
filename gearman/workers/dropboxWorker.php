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
	// $path = DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $adapter;

	$stream = fopen("/tmp/72912031423950/questionid3/72912031423950.jpg","r+");
	$filesystem->writeStream("72912031423950.jpg",$stream);
	fclose($stream);

	return $job->workload();;	
}