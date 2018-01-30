<?php
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."main.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."drive.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."dropbox.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."XUP".DIRECTORY_SEPARATOR."adapters".DIRECTORY_SEPARATOR."amazonwebservices.php");
require_once(DIRECTORY_SEPARATOR."www".DIRECTORY_SEPARATOR."v3".DIRECTORY_SEPARATOR."toprak".DIRECTORY_SEPARATOR."Adapter". DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");

use Aws\S3\S3Client;
use XUP\Uploader\Main;
use XUP\Uploader\Drive;
use XUP\Uploader\Dropbox;
use Spatie\Dropbox\Client;
use  League\Flysystem\Filesystem;
use XUP\Uploader\AmazonWebServices;
use Aws\Common\Credentials\Credentials;
use Spatie\FlysystemDropbox\DropboxAdapter;


function injection($str) {
	$bad = array(
		'<!--', '-->',
		"'", '"',
		'<', '>',
		'&', '$',
		'=',
		';',
		'?',
		'/',
		'!',
		'#',
		'%20',		//space
		'%22',		// "
		'%3c',		// <
		'%253c',	// <
		'%3e',		// >
		'%0e',		// >
		'%28',		// (
		'%29',		// )
		'%2528',	// (
		'%26',		// &
		'%24',		// $
		'%3f',		// ?
		'%3b',		// ;
		'%3d',		// =
		'%2F',		// /
		'%2E',		// .
		// '46', 	// .
		// '47'		// /
	);
	do
	{
		$old = $str;
		$str = str_replace($bad, ' ', $str);
	}
	while ($old !== $str);
	return $str;	
}

function type($str){
	$neverAllow =  array(
			'php', 
			'pl', 
			'cgi',
			'rb', 
			'asp', 
			'aspx',
			'exe', 
			'scr', 
			'dll',
			'msi',
			'vbs',
			'bat',
			'com',
			'pif',
			'cmd',
			'vxd',
			'cpl'
		);
	foreach ($neverAllow as $fft){
		if(stripos($str,$fft) !== FALSE)
		{
			return false;
		}		
	}
	return true; 
}

function mime($str){
	$neverAllow = array(
		"application/octet-stream",
		"application/javascript",
		"text/javascript"
	);
	foreach ($neverAllow as $fmt){
		if(stripos($fmt, $str))
		{
			return false;
		}		
		else
		{
			return true;
		}
	}
}