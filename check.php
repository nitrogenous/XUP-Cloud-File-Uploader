<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");

use XUP\Uploader\Main;
use XUP\Uploader\Drive;
use XUP\Uploader\Dropbox;
use XUP\Uploader\AmazonWebServices;

$services = array("Drive" => null,"Dropbox" => null,"AmazonWebServices" => null);	

$qid = injection($_POST["qid"]);
$formid = injection($_POST["formid"]);
foreach ($services as $service => $value) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$services[$service] = $adapter->check($formid,$qid);
}
exit(json_encode($services));
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

