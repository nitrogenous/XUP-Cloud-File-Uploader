<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;
use XUP\Uploader\Dropbox;
use XUP\Uploader\AmazonWebServices;
$output = array();
$services = array("Drive","Dropbox","AmazonWebServices");
$action = $_POST["action"];
foreach ($services as $service) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$output[$service] = $action($adapter,$_POST);
}
exit(json_encode($output));

function upload($adapter,$post) {
	$params = json_encode(array("formid" => injection($post["formid"]),"folder"=>injection($post["folder"]),"qid" => injection($post["qid"]), "key" => $post["key"], "file" => injection($post["file"]),"folderKey" => injection($post["folderKey"])));
	return $adapter->upload($params);
}
function deleteFile($adapter,$post) {
	$params = json_encode(array("formid" => injection($post["formid"]),"qid" => injection($post["qid"]),"remove" => $post["remove"],"aws" => $post["aws"]));
	return $adapter->deleteFile($params);
}

function injection($str) {
	$bad = array(
		'<!--', '-->',
		"'", '"',
		'<', '>',
		'&', '$',
		'=',
		';',
		'?',
		'/',
		'!',
		'#',
		'%20',		//space
		'%22',		// "
		'%3c',		// <
		'%253c',	// <
		'%3e',		// >
		'%0e',		// >
		'%28',		// (
		'%29',		// )
		'%2528',	// (
		'%26',		// &
		'%24',		// $
		'%3f',		// ?
		'%3b',		// ;
		'%3d',		// =
		'%2F',		// /
		'%2E',		// .
		// '46', 		// .
		// '47'		// /
	);
	do
	{
		$old = $str;
		$str = str_replace($bad, ' ', $str);
	}
	while ($old !== $str);
	return $str;	
}
