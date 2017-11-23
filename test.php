<? 
$client = new GearmanClient();
$client->addServer("127.0.0.1","4730");	
$client->doBackground("toprakDbxUpload","path");