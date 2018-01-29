<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;
use XUP\Uploader\Dropbox;
use XUP\Uploader\AmazonWebServices;
$output
$services = array("Drive","Dropbox","AmazonWebServices");
$action = $_POST["action"];
foreach ($services as $service => $value) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$output = $action($_POST);
}
exit(json_encode($output));

function upload($post,$value) {
	$value =  "XUP\Uploader\\".$value;
	$adapter = new $value();
	$params = json_encode(array("formid" => $this->injection($post["formid"]),"folder"=>$this->injection($post["folder"]),"qid" => $this->injection($post["qid"]), "key" => $post["key"], "file" => $this->injection($post["file"]),"folderKey" => $this->injection($post["folderKey"])));

	return $adapter->upload($params);
}
