<?php

$worker = new Worker();
$worker->addServer("127.0.0.1", "4730");
$worker->addFunction("toprakDBX","toprakDbxUpload");

while ($worker->work());

function toprakDbxUpload($job)
{
	echo $job;
	return "test success";	
}