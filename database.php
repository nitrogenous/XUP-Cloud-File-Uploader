<?php require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");use XUP\Uploader\Main;use XUP\Uploader\Drive;use XUP\Uploader\Dropbox;use XUP\Uploader\AmazonWebServices;$output;$values = array_filter(explode(",",$_POST["clouds"]));	$action = $_POST["action"];$DB = new \Database();foreach ($values as $key) {	$result = $DB->$action($_POST,$key); 	$output[$key] = $result;}$DB->send_status($output);class Database{	protected $bad = array(			'<!--', '-->',			"'", '"',			'<', '>',			'&', '$',			'=',			';',			'?',			'/',			'!',			'#',			'%20',		//space			'%22',		// "			'%3c',		// <			'%253c',	// <			'%3e',		// >			'%0e',		// >			'%28',		// (			'%29',		// )			'%2528',	// (			'%26',		// &			'%24',		// $			'%3f',		// ?			'%3b',		// ;			'%3d',		// =			'%2F',		// /			'%2E',		// .			// '46', 		// .			// '47'		// /		);	protected $access_token;	function check($post,$value) {		$qid = $this->injection($post["qid"]);		$formid = $this->injection($post["formid"]);		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->check($formid,$qid);	}	function save($post,$value) {		$qid = $this->injection($post["qid"]);		$key = $post["key"];			$formid = $this->injection($post["formid"]);		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->save($formid,$qid,$key);	}	function upload($post,$value) {		$value =  "XUP\Uploader\\".$value;		$adapter = new $value();		$params = json_encode(array("formid" => $this->injection($post["formid"]),"folder"=>$this->injection($post["folder"]),"qid" => $this->injection($post["qid"]), "key" => $post["key"], "file" => $this->injection($post["file"]),"folderKey" => $this->injection($post["folderKey"])));		return $adapter->upload($params);	}	function remove($post,$value){		$value = "XUP\Uploader\\".$value;		$adapter = new $value();		$params = json_encode(array("formid" => $this->injection($post["formid"]),"qid" => $this->injection($post["qid"]),"remove" => $this->injection($post["remove"])));		return $adapter->remove($params);	}	function injection($str) {		do		{			$old = $str;			$str = str_replace($this->bad, ' ', $str);		}		while ($old !== $str);		return $str;		}	function tokens($post,$value)	{		$formid = $this->injection($post["formid"]);		$qid = $this->injection($post["qid"]);		$auth = $post["key"];		$value = "XUP\Uploader\\".$value;		$adapter = new $value();		return $adapter->tokens($formid,$qid,$auth);	}	function send_status($first) {		exit(json_encode($first));		}}