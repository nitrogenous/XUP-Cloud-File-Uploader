<?php
$folder = date("h-ia d-m-Y");
$key = injection($_POST["filekey"]);
$formid = injection($_POST["formid"]);
$qid = injection($_POST["qid"]);
$path = DIRECTORY_SEPARATOR . "tmp"; 
$file_path = implode(DIRECTORY_SEPARATOR, array($path,$formid,$folder."-".$key,"questionid".$qid));

if(realpath($file_path) !== true)
{
	if(file_exists($file_path) !== true)
	{
		$oldumask = umask(0);//kalkacak
		mkdir($file_path,0777,true);//644
		umask($oldumask);//kalkacak
	}
}
if(file_exists(DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR."$formid".DIRECTORY_SEPARATOR."$key.txt")){
	$folder = getFolder($formid,$key);
}
else{
	saveFolder($formid,$key,$folder);
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
	if(file_exists($file_path.DIRECTORY_SEPARATOR.$file_name)){
		$newFileName = fileNameExist($file_path,$file_name);
		save($_FILES[$key]["tmp_name"],$file_path,$newFileName,$folder);
	}
	else{
		save($_FILES[$key]["tmp_name"],$file_path,$file_name,$folder);		
	}
}

function fileNameExist($path,$filename){
	while(file_exists($path.DIRECTORY_SEPARATOR.$filename) != false) {
		$filename = "1_".$filename;
	}
	return $filename;
}

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
	do{
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
function getFolder($formid,$key){
	$file = fopen("/tmp/$formid/$key.txt","r");
	$date = fgets($file);
	fclose($file);
	return $date;
}
function saveFolder($formid,$key,$date){
	$file = fopen("/tmp/$formid/$key.txt","wr") or die ("Unable to open file");
	fwrite($file,$date."-".$key) or die ("Unable to write file!");
	fclose($file);
	return true;
}
function save($fileTmpName,$filePath,$fileName,$folder){
	if(move_uploaded_file($fileTmpName, $filePath. DIRECTORY_SEPARATOR .$fileName)){
		if(mime($filePath. DIRECTORY_SEPARATOR .$fileName.DIRECTORY_SEPARATOR.$fileName) != true)
		{
			exit(json_encode(array("succes"=>false,"error"=>"mime_content_type($fileName)")));	
		}
		chmod($filePath. DIRECTORY_SEPARATOR .$fileName, 0776);
		header("HTTP/1.1 200");
		exit(json_encode(array("succes"=>true,"filename" => $fileName,"folder" => $folder,"error"=>null)));			
	}
	else{
		var_dump($fileTmpName . " " . $fileName . " ".$filePath);
		header("HTTP/1.1 500");
		exit(json_encode(array("succes"=>false,"error"=>"Internal Server Error!")));
	}
}