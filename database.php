<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."includes.php");

$output = array();
$services = $_POST["clouds"];
$services = explode(",",$services);
$services = array_filter($services);
$action = $_POST["action"];	
foreach ($services as $service) {
	$class = "XUP\Uploader\\".$service;
	$adapter = new $class();
	$output[$service] = $action($adapter,$_POST);
}
exit(json_encode($output));
function select($adapter,$post) {
	$qid = injection($post["qid"]);
	$formid = injection($post["formid"]);
	return $adapter->select($formid,$qid);
}

function insert($adapter,$post) {
	$qid = injection($post["qid"]);
	$key = $post["key"];	
	$formid = injection($post["formid"]);
	return $adapter->insert($formid,$qid,$key);			
	// }
}
function deleteKey($adapter,$params){
	return null;
}


