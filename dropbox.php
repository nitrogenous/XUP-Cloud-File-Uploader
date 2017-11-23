  <?
  die("Error!");
  // require_once(__DIR__. DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");
  // use League\Flysystem\Filesystem;
  // use Spatie\Dropbox\Client;
  // use Spatie\FlysystemDropbox\DropboxAdapter;

  // $access_token = $_POST["accesstoken"];
  // $form_id = $_POST["formid"];

  // if(stripos($access_token, ".".DIRECTORY_SEPARATOR))
  // {
  // 	die("400 Error!");
  // }
  // if(stripos($form_id	, ".".DIRECTORY_SEPARATOR))
  // {
  // 	die("400 Error!");
  // }

  // $client = new Client($access_token);
  // $adapter = new DropboxAdapter($client);
  // $filesystem = new Filesystem($adapter);
  // $path = $formid . DIRECTORY_SEPARATOR;

  // file_put_contents("asdas", var_export($_FILES));
  
  // foreach ($_FILES as $key => $value) {
  // 	if(stripos($_FILES[$key]["name"], "./") !== false)
  // 	{
  // 		die("Error!");
  // 	}

  // 	$stream = fopen($_FILES[$key]["tmp_name"],"r+");
  // 	$filesystem->writeStream($_FILES[$key]["name"],$stream);
  // 	fclose($stream);

  // }