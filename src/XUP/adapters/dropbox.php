<?php
namespace XUP\Uploader;

class Dropbox extends XUP {
	protected	$value;
	protected	$status;
	function __construct() {	
		$this->value = strtolower((new \ReflectionClass($this))->getShortName());
	}
	public function check($formid,$qid) {
		if(empty($formid) || empty($qid) || empty($this->value)) {
			return "Error1";
		}
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$sql = "SELECT * FROM widget_access_keys  WHERE formId =".mysqli_real_escape_string($con,$formid)." AND questionId = ".mysqli_real_escape_string($con,$qid)." AND value = '$this->value'";
		$result = mysqli_query($con,$sql);
		mysqli_close($con);
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
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$formid = mysqli_real_escape_string($con,$formid);
		$sql = "REPLACE INTO widget_access_keys (`formId`,`questionId`,`value`,`key`) VALUES (".mysqli_real_escape_string($con,$formid).",".mysqli_real_escape_string($con,$qid).",'".$this->value."','".mysqli_real_escape_string($con,$key)."')";
		$result = mysqli_query($con,$sql);
		mysqli_close($con);
		if ($result == true) {
			$this->value = true;
			return true;
		}
		else{
			mysqli_close($con);
			return false;
		}
	}
	public function remove($formid,$qid) {
		return false;
	}
	public function upload($formid,$qid,$file,$params) {
		$client = new GearmanClient();
		$client->addServer("127.0.0.1","4730");	
		$client->doBackground("toprakDbxUpload","path");
	}
	public function test() {
		return $this->value . ":✔";
	}
}