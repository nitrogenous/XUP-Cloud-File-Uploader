<?php
namespace XUP\Uploader;

class AmazonWebServices extends XUP {
	protected	$value;
	protected 	$key;
	protected	$status;
	function __construct() {	
		$this->value = strtolower((new \ReflectionClass($this))->getShortName());
	}
	public function check($formid,$qid) {
		return null;
		if(empty($formid) || empty($qid) || empty($this->value)) {
			return "Error1";
		}
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$sql = "SELECT * FROM widget_access_keys  WHERE formId =".mysqli_real_escape_string($con,$formid)." AND questionId = ".mysqli_real_escape_string($con,$qid)." AND value = '$this->value'";
		$result = mysqli_query($con,$sql);
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
		$sql = "REPLACE INTO widget_access_keys (`formId`,`questionId`,`value`,`key`) VALUES (".mysqli_real_escape_string($con,$formid).",".mysqli_real_escape_string($con,$qid).",'".mysqli_real_escape_string($con,$this->value)."','".mysqli_real_escape_string($con,$key)."')";
		$result = mysqli_query($con,$sql);
		if ($result == true) {
			$this->value = true;
			return true;
		}
		else{
			return false;
		}
	}
	public function remove($formid,$qid) {
		return false;
	}
	public function upload($params) {
		$client = new \GearmanClient();
		$client->addServer("127.0.0.1","4730");	
		return $client->doNormal("toprakAWS",$params);
	}
	public function test() {
		return $this->value . ":✔";
	}
	public function get($formid,$qid){
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$sql = "SELECT `key` FROM `widget_access_keys` WHERE formId = $formid AND questionId = $qid AND value = '".$this->value."'";
		$result = mysqli_query($con,$sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()){
				$this->key = $row['key'];
				return $row['key'];
			}
		}
		else{
			return false;
		};
	}
	public function tokens($formid,$qid,$auth) {
		return null;
	}
	public function query($query){
		$con = mysqli_connect("127.0.0.1","toprak","toprak","toprak_jotform3");
		$result = mysqli_query($con,$query);
		mysqli_close($con);
		return $result;
	}
}