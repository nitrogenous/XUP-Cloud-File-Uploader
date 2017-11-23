<?php
namespace XUP\Uploader;

class Dropbox extends XUP {
	protected	$value;
	protected 	$key;
	protected	$status;
	protected 	$con;
	function __construct() {	
		$this->value = strtolower((new \ReflectionClass($this))->getShortName());
		$this->con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
	}
	public function check($formid,$qid) {
		if(empty($formid) || empty($qid) || empty($this->value)) {
			return "Error1";
		}
		$sql = "SELECT * FROM widget_access_keys  WHERE formId =".mysqli_real_escape_string($this->con,$formid)." AND questionId = ".mysqli_real_escape_string($this->con,$qid)." AND value = '$this->value'";
		$result = mysqli_query($this->con,$sql);
		mysqli_close($this->con);
		if ($result->num_rows > 0) {
			$this->value = true;
			return true;
		}
		else{
			return false;
		}
	}
	public function save($formid,$qid,$key) {
		if(empty($formid) || empty($qid) || empty($key) || empty($this->value)) {
			return "Error";
		}
		$formid = mysqli_real_escape_string($this->con,$formid);
		$sql = "REPLACE INTO widget_access_keys (`formId`,`questionId`,`value`,`key`) VALUES (".mysqli_real_escape_string($this->con,$formid).",".mysqli_real_escape_string($this->con,$qid).",'".$this->value."','".mysqli_real_escape_string($this->con,$key)."')";
		$result = mysqli_query($this->con,$sql);
		mysqli_close($this->con);
		if ($result == true) {
			$this->value = true;
			return true;
		}
		else{
			mysqli_close($this->con);
			return false;
		}
	}
	public function remove($formid,$qid) {
		return false;
	}
	public function upload($formid,$qid,$file) {
			$this->get($formid,$qid);
			$params = json_encode(array("formid" => $formid,"qid" => $qid, "key" => $this->key, "file" => $file));
			$client = new \GearmanClient();
			$client->addServer("127.0.0.1","4730");	
			$client->doBackground("toprakDBX",$params);
			return $client->returnCode();
	}
	public function test() {
		return $this->value . ":✔";
	}
	public function get($formid,$qid){
		$sql = "SELECT `key`FROM `widget_access_keys` WHERE `formId` = $formid AND `qid` = $qid";
		$result = mysqli_query($this->con,$sql);
		mysqli_close($this->con,$sql);
		if ($result->num_rows > 0) {
			$this->key = $result;
			return true;
		}
		else{
			return false;
		};
	}
}