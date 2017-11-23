<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<form action="test.php" method="POST" enctype="multipart/form-data" id=XUP>
		
		<input type="file" name="upload" id="upload">
		<input type="submit" name="submit" id="submit">

	</form>


	<?php 
	
		
	 ini_set('display_errors', 1);
	 ini_set('display_startup_errors', 1);
	 error_reporting(E_ALL);
	require_once(__DIR__ . "/vendor/autoload.php");


	use League\Flysystem\Filesystem;
	use Spatie\Dropbox\Client;
	use Spatie\FlysystemDropbox\DropboxAdapter;
	$client = new Client("pdl8DbDI8rAAAAAAAAAASEIPRuiiP_zF5bUYhCEuVGl0IZMAm-LMKQmownWNPqCP");

	$adapter = new DropboxAdapter($client);

	$filesystem = new Filesystem($adapter);

	// $filesystem->createDir("wololo");


	if(isset($_POST["submit"]))
	{

	 foreach ($_FILES as $key => $value) {
		$stream = fopen($_FILES[$key]["tmp_name"],"r+");
		$filesystem->writeStream($_FILES[$key]["name"],$stream);
		fclose($stream);
	 }

	}
	print_r($filesystem->listContents());

	

	?>
</body>
</html>
