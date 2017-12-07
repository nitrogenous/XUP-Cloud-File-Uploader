<?
require_once("/www/v3/toprak/Adapter/vendor/autoload.php");
// require_once("/www/v3/toprak/Adapter/src/XUP/main.php");
// require_once("/www/v3/toprak/Adapter/src/XUP/adapters/drive.php");

// use XUP\Uploader\Main;
// use XUP\Uploader\Drive;

$file = file_get_contents($_FILES["upload"]["tmp_name"]);
// var_dump($file); die();

$client = new Google_Client();
$client->setAuthConfig("client_secrets.json");
$client->setSubject($file);
$client->setScopes(["https://www.googleapis.com/auth/drive"]);
$client->setApplicationName("XUP_File_Uploader");

$client->setAccessToken("ya29.GlsbBcwwHBwWf_k65IYxoOgviU4oewJ8PTKEUExFlCNvj7xJd4Hnm0qCX4ZLWKcmIC61l1aS04Pof0kdEWYKaJvQvj8xoVAmYcqSqx5Tt0r-n1x7SrE1ziVHfsJu");

// if($client->isAccessTokenExpired()) {
// var_dump("Refreshing");
// $refresh = $client->refreshToken("1/0Hrcl6V4z3lomwp60m1qXhIqov4dwoT2h7u5Jo5Ie1A");
// var_dump("Refreshed");
// }

$service = new Google_Service_Drive($client);
$fileMeta = new Google_Service_Drive_DriveFile(array("name" => "Test"));
$fileService = $service->files->create($fileMeta,array(
"data" => $file,
"mimeType" => "application/octet-stream",
"uploadType" => "media"
));

var_dump($fileService);