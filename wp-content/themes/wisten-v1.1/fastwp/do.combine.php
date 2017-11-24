<?php
header('Server: FastWP');
header('X-Powered-By: FastWP.Net');
$expires = 60 * 60 * 1;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
$denied = array('.exe', '.php', '.js', '.com', '.bat', '.html');
if (isset($_REQUEST['scripts'])) {
	header('Content-type: text/javascript');
	checkRequest($_REQUEST['scripts']);
	$ext = '.js';
	foreach(explode(',', $_REQUEST['scripts']) as $file) {
		$file_path = dirname(dirname(__FILE__)).
		'/js/'.$file.$ext;
		if (file_exists($file_path)) {
			$content = file_get_contents($file_path);
			// $content = preg_replace("|//\n|ims",'', $content);
			$expr = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\)\/\/.*))/';
			$content = preg_replace($expr, '', $content);
			$content = preg_replace('/\s+/', ' ', $content);
			echo $content;
			// echo php_strip_whitespace($content);
			// echo php_strip_whitespace($file_path);
		} else {
			echo "/* File not found: $file$ext */";
		}
	}
}
if (isset($_REQUEST['styles'])) {
	header('Content-type: text/css');
	checkRequest($_REQUEST['styles']);
	$ext = '.css';
	foreach(explode(',', $_REQUEST['styles']) as $file) {
		$file_path = dirname(dirname(__FILE__)).
		'/css/'.$file.$ext;
		if (file_exists($file_path)) {
			echo preg_replace('/\s+/', ' ', file_get_contents($file_path));
		} else {
			echo "/* File not found: $file$ext */";
		}
	}
}
function checkRequest($req = null) {
	global $denied;
	foreach($denied as $extension) {
		if (substr_count($req, $extension) > 0) {
			die('Incorrect request '.$extension);
		}
	}
}

function remove_comments( & $string )
{
  $string = preg_replace("%(#|;|(//)).*%","",$string);
  $string = preg_replace("%/\*(?:(?!\*/).)*\*/%s","",$string); // google for negative lookahead
  return $string;
}