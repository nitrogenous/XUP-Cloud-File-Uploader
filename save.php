<?php
function injection($str)
{
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
		if(stripos($str, '4647'))
		{
			$str = str_replace('4647', '', $str);
		}
	}
	while ($old !== $str);
	return $str;
}
function type($str)
{
	$neverAllow =  array(
		'php', 
		'pl', 
		'cgi',
		'rb', 
		'asp', 
		'aspx',
		'exe', 
		'scr', 
		'dll',
		'msi',
		'vbs',
		'bat',
		'com',
		'pif',
		'cmd',
		'vxd',
		'cpl'
	);
	foreach ($neverAllow as $fft){
		if(stripos($str,$fft) !== FALSE)
		{
			return false;
		}		
	}
	return true; 
}
function mime($str)
{
	$neverAllow = array(
		"application/octet-stream",
		"application/javascript",
		"text/javascript"
	);
	foreach ($neverAllow as $fmt){
		if(stripos($fmt, $str))
		{
			return false;
		}		
		else
		{
			return true;
		}
	}
}
$formid = injection($_POST["formid"]);
$qid = injection($_POST["qid"]);
$path = DIRECTORY_SEPARATOR . "tmp"; 
$file_path = implode(DIRECTORY_SEPARATOR, array($path,$formid,"questionid".$qid));
if(realpath($file_path) !== true)
{
	if(file_exists($file_path) !== true)
	{
		mkdir($file_path, 777,true);
	}
}
foreach ($_FILES as $key => $value) {
	$file_name = injection($_FILES[$key]["name"]);
	$array = explode('.', $file_name);
	$extension = end($array);
	if(type($extension) != true)
	{
		exit(json_encode(array("succes"=>false,"error"=>"type")));
	}
	
	$chars = range("a","z");
	$numbers= range("0","9");
	foreach ($chars as $char){
		if(stripos($file_name, $char)){
			break;		
		}
		else{
			foreach ($numbers as $number) {
				if(stripos($file_name, $number)){
					$tmp = explode(".", $file_name);
					$extension = ".".end($tmp);
					$file_name = $formid.$extension;
				}
			}
		}
	}
	
	if(move_uploaded_file($_FILES[$key]["tmp_name"], $file_path. DIRECTORY_SEPARATOR .$file_name)){
		if(mime($file_path. DIRECTORY_SEPARATOR .$file_name.DIRECTORY_SEPARATOR.$file_name) != true)
		{
			exit(json_encode(array("succes"=>false,"error"=>"mime_content_type(filename)")));	
		}
		chmod($file_path. DIRECTORY_SEPARATOR .$file_name, 0777);
		header("HTTP/1.1 200");
		die(json_encode(array("succes"=>true,"error"=>null)));			
	}
	else{
		var_dump($file_tmp_name . $file_name .$file_path);
		header("HTTP/1.1 500");
		die(json_encode(array("succes"=>false,"error"=>"Internal Server Error!")));
	}
}