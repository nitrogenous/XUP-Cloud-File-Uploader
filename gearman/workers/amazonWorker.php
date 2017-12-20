<?php
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
// require_once '/www/v3/toprak/lib/init.php';

use Aws\S3\S3Client;
use Aws\Common\Credentials\Credentials;

$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakAWS", "toprakAwsUpload");

while ($worker->work());

function toprakAwsUpload($job) {
	$params = (array)json_decode($job->workload());
	$formid = $params["formid"];
	$folder = $params["folder"];
	$qid = $params["qid"];
	$file = $params["file"];
	$keys = (array)json_decode($params["key"]);
	$access = $keys["access"];
	$secret = $keys["secret"];
	$bucket = $keys["bucket"];
	$region = $keys["region"];

	$base_path = DIRECTORY_SEPARATOR . "tmp";
	$path =  $folder . DIRECTORY_SEPARATOR. "questionid".$qid;
	$key = "toprak" . DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. $file;
	$sourcepath =$base_path.DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR .$path.DIRECTORY_SEPARATOR.$file;
	var_dump($keys);

	if(!file_exists($sourcepath)){
		echo"Error!File.Does.Not.Exist";
		return("Error!File.Does.Not.Exist");
	}
	else{
		$s3 = S3Client::factory(array(
			"region" => $region,
			'version' => '2006-03-01',
			"credentials" => array(
				"key" => $access,
				"secret" => $secret
			)
		));
		$result = $s3->putObject(array(
			"Bucket" => $bucket,
			"Key" => $key,
			"SourceFile" => $sourcepath,
			"ContenType" => "application/octet-stream",
			"ACL" => "public-read"
		));
		$url = $result["ObjectURL"];
		var_dump($url);
		return $url;	
	}
}