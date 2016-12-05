<?php /*Original version had a probelm in php 4 due to preg not knowing \h. Fixed now. */
header("content-type:text/css");
header("Expires:".gmdate("D, d M Y H:i:s", (time()+(86400*7)))." GMT"); // Cache for 7 days.  Not much point going any higher. 

// grab the c parameter and ensure that it contains .css and no slashes
// this is a safety measure to prevent XSS
$c=$_GET["c"];
if(preg_match("/\//",$c) or !preg_match("/.css/",$c) || $c == "") {
	$error = "CSS error: Only local CSS files allowed or no CSS file name passed! Prostě nějaká chyba CSS, tak to koukejte někdo opravit!";
	die("body:before {content:\"$error\";color: red; font-size:200%;}"); // For those browsers that understand psudo classes they will see a clear error.
	exit;
}

// Grab the name of the folder to load the style from. 
// This and C will be the only vars collected from the command line.
$folder = $_GET["f"];
if ($folder !="" && !preg_match("/^[a-zA-Z0-9\-_]+$/",$folder)) { // Check that the folder name has only A to Z, numbers, - and _. If it doesn't drop it.
	unset ($folder);
	$error = "CSS error: Folder name passed to CSS contains invalid characters. Prostě nějaká chyba CSS, tak to koukejte někdo opravit!";
	die("body:before {content:\"$error\";color: red; font-size:200%;}");
	exit;
}

// load the content of the CSS file into the variable css, end if the
$css=load($c);
// file wasn't found.
if($css=="") {
	$error = "CSS error: File $c not found. Prostě nějaká chyba CSS, tak to koukejte někdo opravit!";
	die("body:before {content:\"$error\";color: red; font-size:200%;}");
	exit;
}

// Load the external style file.
if (!$folder == "") $external_style = load(getcwd()."/styles/$folder/style.cfg");
// Now that the file is loaded lets see what we need to do next. 
// If the folder var contains a vaild folder with a style.cfg in it then we'll use that otherwise we'll use the values in the CSS file.
if ($external_style!="") {
	preg_match_all("/\\$([a-z0-9A-Z_\-]+)[ |\t]*=[ |\t]*([^;$\n]*)/s",$external_style,$returns); // Finds all objects matching ${text and or digits}={value can be anything but semicolon} ;  They can be all on one line or spread out as long as they start with a dollar, finish with a semicolon and have an equals seperating the two halves.
	for ($i = 0;$i < count($returns[1]);$i++)
		$styles[$returns[1][$i]] = preg_replace(array("/^[\"|\'| |\t|\n]*/","/[\"|\'| |\t|\n]*$/"),"",$returns[2][$i]);// Strip any Quotes and spaces from the top and tail of the variables value and add it to the styles array.
		$styles["folder"] = $folder;
} else {
	preg_match_all("/(?:\/\*.*?\*\/)/s",$css,$comments); // Find all comments in the CSS
	foreach ($comments[0] as $comment) {
		preg_match_all("/\\$([a-z0-9A-Z_\-]+)[ \t]*=[ \t]*([^;$\n]*)/s",$comment,$returns); //Find all my variables inside said comments
		for ($i = 0;$i < count($returns[1]);$i++){
			$styles[$returns[1][$i]] = preg_replace(array("/^[\"|\'| |\t|\n]*/","/[\"|\'| |\t|\n]*$/"),"",$returns[2][$i]);// Strip any Quotes and spaces from the top and tail of the variables value and add it to the styles array.
		}
	}
}

// Create a URI option so I can include $URI in the stylesheet. It will point to this files location but easier to work out where we are from here rather than somewhere else.
global $HTTP_SERVER_VARS;
$URL = parse_url("http://".$HTTP_SERVER_VARS["HTTP_HOST"].$HTTP_SERVER_VARS["REQUEST_URI"]);
$styles["URI"] = dirname($URL["scheme"]."://".$URL["host"].$URL["path"]);

// I think I need to change this to a one pass solution, not sure it would change the CPU loading as I can't even see the load this places with 15k+ CSS files.
if (is_array($styles)) 
	foreach (array_keys($styles) as $key) {
		$css = preg_replace("/(\\$".$key.")([;|\t| |}|\)|\"|\'|\/|\r|\n])/U",$styles[$key]."$2",$css); // Replace all the bits that need replacing.  Set to match with certain termination chars to avoid var names that are shorter versions of later vars replacing things they're not meant to.
	}

// Shrink the CSS, remove all comments, new lines and unneeded whitespace. Makes the output unreadable to most people.  
// I Can now fill the CSS with comments and formatting and not have to worry too much about its size. 
// One of my CSS files was reduced by 4k, which is significant. Couple this with the seven day cache set at the top and the CSS shouldn't cause any load problems.
$css = preg_replace(array("/[\r|\n]/","/(?:\/\*.*?\*\/)/s","/[ |\r|\n|\t|;]*([;|\{|\}|:])[ |\r|\n|\t]*/","/(,)[ |\r|\n|\t]*/"),"$1",$css);

echo $css; // Output the ammended CSS file to the browser.

function load($filelocation) {
	if (file_exists($filelocation))	{
		$newfile = fopen($filelocation,"r");
		$file_content = fread($newfile, filesize($filelocation));
		fclose($newfile);
		return $file_content;
	}
}?>