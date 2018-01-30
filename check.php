<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."includes.php");

$services = array("Drive" => null,"Dropbox" => null,"AmazonWebServices" => null);	

$qid = injection($_POST["qid"]);
$formid = injection($_POST["formid"]);
foreach ($services as $service => $value) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$services[$service] = $adapter->check($formid,$qid);
}
exit(json_encode($services));
