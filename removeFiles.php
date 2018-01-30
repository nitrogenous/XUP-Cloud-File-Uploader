<?php
// var_dump($_POST);
if(isset($_POST["formid"])){
	if(isset($_POST["folder"])){
		$formid = injection($_POST["formid"]);
		$folder = injection($_POST["folder"]);
		$path = DIRECTORY_SEPARATOR."tmp".DIRECTORY_SEPARATOR.injection($formid).DIRECTORY_SEPARATOR.injection($folder).DIRECTORY_SEPARATOR;
		if(!realpath($path)){
			exit("Please check the file path");
		}
		elseif(stripos(__DIR__,$path) != 0){
			exit("Please check the file path");
		}
		else{
			delete_files($path);
		}
		return true;
	}
}
else{
	exit("Please check the post method");
}

function delete_files($target){
	if(is_dir($target)){
		$files = glob($target."*",GLOB_MARK);
		foreach ($files as $file) {
			delete_files($file);
		}
		rmdir($target);
	}
	elseif(is_file($target)){
		unlink($target);
	}
}

function injection($str)
{
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
		// '46', 		// .
		// '47'		// /
	);
	do
	{
		$old = $str;
		$str = str_replace($bad, ' ', $str);
		if(stripos($str, '4647'))
		{
			$str = str_replace('4647', '', $str);
		}
	}
	while ($old !== $str);
	return $str;
}