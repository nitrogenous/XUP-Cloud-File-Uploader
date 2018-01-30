<?php
$worker = new GearmanWorker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDBX", "toprakDbxUpload");
$worker->addFunction("toprakDBXRemove", "toprakDbxRemove");
while ($worker->work());

function toprakDbxUpload($job) {
	try{		
		$params = (array)json_decode($job->workload());
		var_dump($params);
		foreach ($params as $param) {
			if(empty($param)){
				echo($param." is null!");
				return json_encode(array("Error" => $param." is null","File" => null,"Url" => null));
			}
		}
		$token = (string)$params["key"];
		$formid = $params["formid"];
		$file = $params["file"];
		$qid = $params["qid"];
		$folder = $params["folder"];
		$base_path = DIRECTORY_SEPARATOR . "tmp";
		$path = DIRECTORY_SEPARATOR . $formid . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR. "questionid".$qid . DIRECTORY_SEPARATOR . $file;
		// var_dump($path);
		if(!file_exists($base_path.$path)){
			echo"Error!File.Does.Not.Exist";
			return("Error!File.Does.Not.Exist");
		}
		else{	
			$client = new Client($token);
			$adapter = new DropboxAdapter($client);
			$filesystem = new Filesystem($adapter);
			$stream = fopen($base_path.$path,"r+");
			$filesystem->write($path,$stream);
			fclose($stream);	
			$url = "www.dropbox.com/home/$formid/$folder/questionid$qid";
			var_dump($url);
			return json_encode(array("Error" => 0,"File" => $file,'Folder' => null, "Url" => $url, "Remove" => $path));	
		}
	}
	catch(Exception $e){
		return json_encode(array("Error" => $e,"File" => null,'Folder' => null,"Url" => null,"Remove" => null));
	}
}
function toprakDbxRemove($job){
	try{
		$params = (array)json_decode($job->workload());
		foreach ($params as $param => $value) {
			if(empty($value) || $value == "null" || $value == "{}"){
				var_dump($param." is null!");
				return json_encode(array("Error" => $param." is null","File" => null,"Url" => null));
			}
		}
		$token = $params["key"];
		$remove = (array)json_decode($params["remove"]);
		$remove = $remove["Dropbox"];
		var_dump($remove);
		$client = new Client($token);
		$adapter = new DropboxAdapter($client);
		$filesystem = new Filesystem($adapter);
		$filesystem->delete($remove);
		return json_encode(array("Error" => 0));	
	}	
	catch(Exception $e){
		return json_encode(array("Error" => $e));
	}
}