<?php
namespace XUP\Uploader;

class AmazonWebServices extends XUP {
	protected	$value;
	protected 	$key;
	protected	$status;
	function __construct() {	
		$this->value = strtolower((new \ReflectionClass($this))->getShortName());
	}
	public function test() {
		return $this->value . ":✔";
	}
	public function select($formid,$qid) {
		return null;
	}
	public function insert($formid,$qid,$key) {
		return null;
	}
	public function upload($params) {
		$client = new \GearmanClient();
		$client->addServer("127.0.0.1","4730");	
		return $client->doNormal("toprakAWS",$params);
	}
	public function deleteKey($params){
		return null;
	}
	public function deleteFile($params) {
		$params = (array) json_decode($params);
		$job = json_encode(array("key" => $params["aws"],"remove" => $params["remove"]));
		$client = new \GearmanClient();
		$client->addServer("127.0.0.1","4730");
		return $client->doBackground("toprakAWSRemove",$job);
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