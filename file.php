<?php 
require_once(__DIR__.DIRECTORY_SEPARATOR."includes.php");

if($_POST["action"] == "save"){
	function fileNameExist($path,$filename){
		while(file_exists($path.DIRECTORY_SEPARATOR.$filename) != false) {
			$filename = "1_".$filename;
		}
		return $filename;
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
	$formid = injection($_POST["formid"]);
	$key = injection($_POST["filekey"]);
	$folder = null;
	if(realpath("/tmp/$formid") !== true){
		if(file_exists("/tmp/$formid" !== true)){
			$oldumask = umask(0);//kalkacak
			mkdir("/tmp/$formid",0777,true);//644
			umask($oldumask);//kalkacak
		}
	}
	if(file_exists(DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR."$formid".DIRECTORY_SEPARATOR."$key.txt")){
		$folder = getFolder($formid,$key);
	}
	else{
		$folder = date("h-ia d-m-Y"); //Submisson date
		saveFolder($formid,$key,$folder);
		$folder = $folder."-".$key;
	}
	$qid = injection($_POST["qid"]);
	$path = DIRECTORY_SEPARATOR . "tmp"; 
	$file_path = implode(DIRECTORY_SEPARATOR, array($path,$formid,$folder,"questionid".$qid));
	if(realpath($file_path) !== true){
		if(file_exists($file_path) !== true){
			$oldumask = umask(0);//kalkacak
			mkdir($file_path,0777,true);//644
			umask($oldumask);//kalkacak
		}
	}
	foreach ($_FILES as $key => $value) {
		$file_name = injection($_FILES[$key]["name"]);
		$array = explode('.', $file_name);
		$extension = end($array);
		if(type($extension) != true){
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

}


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
