<?php
/**
 * Performs an action and outputs result as html - for ajax tasks.
 *
 * @package WassUp Real-time Analytics
 * @subpackage action.php
 *
 * USAGE:
 *   action.php?action=dosomething&arg1=x&arg2=y (as ajax url)
 *    -'action' query parameter is required.
 */
//security check#1: wassup referrer required
$is_attack=false;
if (!empty($_SERVER['HTTP_REFERER'])&& stristr($_SERVER['HTTP_REFERER'],"wassup")===false) {
	die('Bad referer!');
}
//security check#2: block obvious hack attempts on request and referer
if(empty($_SERVER['HTTP_REFERER'])) $targets=array($_SERVER['REQUEST_URI']);
else $targets=array($_SERVER['REQUEST_URI'],$_SERVER['HTTP_REFERER']);
foreach($targets AS $target){
	if (preg_match('/["\';<>\$\\\*]/',$target)>0) {
		$is_attack=true;
		break;
	} elseif (preg_match('/(\.+\/){3,}/',$target)>0) {
		$is_attack=true;
		break;
	} elseif (preg_match('/(&lt;|&#60;|%3C)/',$target)>0) {
		$is_attack=true;
		break;
	} elseif (preg_match('#[^a-z_/\-](select|delete|update|alter|drop|create|union|\-1|\-9+)[^a-z_/]#i',$target)>0) {
		$is_attack=true;
		break;
	} elseif (preg_match('/[^a-z_\-](dir|file|href|img|location|path|src|thisdir|document_root.?)\=/i',$target)>0) {
		$is_attack=true;
		break;
	} elseif(preg_match('/[\.\/](aspx?|bin|dll|cgi|cmd|etc|exe|ini|jsp)/i',$target)>0) {
		$is_attack=true;
		break;
	} elseif(preg_match('/(document|function|script|window|cookie)[^a-z0-9\s]/i',$target)>0) {
		$is_attack=true;
		break;
	} elseif(preg_match('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/',$target)>0){
		$is_attack=true;
		break;
	} 
} //end foreach
if($is_attack){
	if($target == $_SERVER['HTTP_REFERER']) die('#2:Bad referer!'.$_SERVER['HTTP_REFERER']);
	else die('Bad request!');
}
//security check#3:  check that hash exists
if(!isset($_GET['whash'])){
	die('Missing or invalid parameter!');
}
//include required wordpress files
$thisfile=preg_replace('/\\\\/','/',__FILE__);
if ( !function_exists('get_bloginfo') ) {
	//IMPORTANT NOTE: The additional GET parameter, "wpabspath=ABSPATH", is required whenever "/wp-content/" directory is in a different directory from Wordpress core files (WP v2.6+). 
	if ( !empty($_GET['wpabspath']) ) {
	//esc_attr not used because it wouldn't be defined here
		$wpabspath=htmlspecialchars((base64_decode(urldecode($_GET['wpabspath']))),ENT_QUOTES);
	}
	if(empty($wpabspath)|| !is_dir($wpabspath)){
		$wpabspath=substr($thisfile,0,strpos($thisfile,'/wp-content/')+1);
	}
	//clean up $wpabspath in case misconfigured
	if(!empty($wpabspath)){
		$cleanpath=preg_replace(array('/\\\\/','#[ /]+$#'),array('/',''),$wpabspath);
		$wpabspath=$cleanpath;
	}
	if(is_readable($wpabspath. '/wp-config.php')){
		include_once($wpabspath.'/wp-config.php');
	}elseif(is_readable($wpabspath. '/../wp-config.php')){
		include_once($wpabspath.'/../wp-config.php');
	}else{
		die('wp-config.php not found!');
	}
	if(!defined('ABSPATH')) die('wp-config.php do not load!');
}
//security check#4: check that user is logged in (can be faked)
$validuser=false;
$current_user = $GLOBALS['current_user'];
//#only logged-in users are allowed to run this script -Helene D.
if(empty($current_user->user_login)) {
	$logged_user = wp_get_current_user();
	$validuser = (!empty($logged_user->user_login)? true: false);
}else{
	$validuser=true;
}
if (!$validuser) wp_die(__("login required!"));

//security check#5: check hash value
$hashfail = true;
$wassup_options=$GLOBALS['wassup_options'];
if (isset($_GET['whash']) && !empty($wassup_options->whash)){
	if ($_GET['whash'] == $wassup_options->whash || $_GET['whash'] == htmlspecialchars($wassup_options->whash,ENT_QUOTES))
		$hashfail=false;
}

//#perform an "action" and display the results, if any
if (!$hashfail) {
	$wp_version=$GLOBALS['wp_version'];
	$wpdb=$GLOBALS['wpdb'];

	//define WassUp constants
	if(!defined('WASSUPVERSION')) wassup_init();
	//load wassupOptions class
	if (empty($wassup_options)){
		if(!class_exists('wassupOptions') && file_exists(WASSUPDIR.'/lib/wassup.class.php')){
			if(defined('PHP_VERSION') && version_compare(PHP_VERSION,'5.1','<')) require_once(WASSUPDIR."/lib/compat-lib/wassup.class.php");
			else require_once(WASSUPDIR.'/lib/wassup.class.php');
		}
		$wassup_options = new wassupOptions;
	}
	//#set required variables
	$wassup_table = $wassup_options->wassup_table;
	$wassup_tmp_table = $wassup_table . "_tmp";
	$wdebug_mode=false;	//debug set below

	// ### Separate "delete" action for non-html output
	// ACTION: DELETE ON THE FLY FROM VISITOR DETAILS VIEW
	if ($_GET['action'] == "deleteID") {
		if (!empty($_GET['id'])) {
			//make sure there is no suspicious chars in id
			$wassup_id=$wassup_options->cleanFormText($_GET['id']);
			if($wassup_id == $_GET['id']){
				if (method_exists($wpdb,'prepare'))
					$deleted=$wpdb->query($wpdb->prepare("DELETE FROM $wassup_table WHERE `wassup_id`='%s'", $wassup_id));
				else
					$deleted=$wpdb->query(sprintf("DELETE FROM $wassup_table WHERE `wassup_id`='%s'",$wassup_id));
				if(is_wp_error($deleted)){
					$errno=$deleted->get_error_code();
					if((int)$errno > 0){
						$msg=__("An error occurred during delete","wassup")." id=".$wassup_id."\n<br/>";
						$msg.="$errno: ".$deleted->get_error_message()."\n";
					}
					$deleted=0;
				}else{
					$msg=sprintf(__("%d records deleted!","wassup"),$deleted);
				}
				if(empty($deleted)) {
					die($msg);
				}
			}else{
				die(__("Error: invalid id parameter:".esc_attr($_GET['id'])));
			}
		} else {
			die(__("Error: missing id parameter","wassup"));
		}
		exit;
	} //end if action==deleteID

	// ### Begin actions that have output...
	if (!empty($_GET['debug_mode'])) {
		$wdebug_mode=true;
		$mode_reset=ini_get('display_errors');
		if(defined('PHP_VERSION')&& version_compare(PHP_VERSION,5.4,'<'))@error_reporting(E_ALL);
		else @error_reporting(E_ALL ^ E_STRICT);
		ini_set('display_errors','On');
	}
	//load wassup core functions
	if (!function_exists('stringShortener')) {
		if (file_exists(WASSUPDIR .'/lib/main.php')) {
			include_once(WASSUPDIR . '/lib/main.php');
		} else {
			echo '<span style="font-color:red;">Action '.__("ERROR: file not found","wassup").' - main.php</span>';
			exit;
		}
	}
	//#perform an action and display output
	//force browser to disable caching so action.php works as an ajax request
	nocache_headers();
	// ACTION: RUN SPY VIEW
	if ($_GET['action'] == "spia") {
		$rows=0;
		$spytype="";
		//cannot use 'get_user_option' for spy timestamp...causes query caching causes duplicates (needs SQL_NO_CACHE)
		//$wassup_user_settings=get_user_option('_wassup_settings');
		//$from_spydate=$wassup_user_settings['utimestamp'];
		$from_spydate=wassupDb::get_wassupmeta($current_user->user_login,"_spytimestamp",true);
		if(empty($from_spydate) || !is_numeric($from_spydate)) $from_spydate="";
		if(!empty($_GET['rows']) && is_numeric($_GET['rows'])) $rows = (int)$_GET['rows'];
		if(!empty($_GET['spiatype'])) $spytype=$wassup_options->cleanFormText($_GET['spiatype']);
		wassup_spiaView($from_spydate,$rows,$spytype);
		exit;
	}
	$vers='?ver='.WASSUPVERSION;
	if($wdebug_mode)$vers.='b'.rand(0,9999);
	$html_head= '
<!DOCTYPE html>
<html>
<head>
 <title>WassUp '.esc_attr($_GET['action']).'</title>
 <link rel="stylesheet" href="'.WASSUPURL.'/css/wassup.css'.$vers.'" type="text/css" />
</head>
<body class="wassup-ajax">'."\n";
	if($wdebug_mode){
		$html_head.="<!-- *WassUp DEBUG On-->\n";
		$html_head.="<!-- *normal setting: display_errors=$mode_reset -->\n";
	}
	$html_foot='
</body>
</html>';
	//#retrieve common command-line arguments
	$to_date=0;
	$from_date=0;
	if (isset($_GET['to_date']) && is_numeric($_GET['to_date'])) {
		$to_date = (int)$_GET['to_date'];
	} else {
		$to_date = current_time('timestamp');
	}
	if (isset($_GET['from_date']) && is_numeric($_GET['from_date'])) {
		$from_date = (int)$_GET['from_date'];
	} else {
		$from_date = ($to_date - 180);	//3 minutes
	}
	//#check that $to_date is a number
	if (!is_numeric($to_date)) { //bad date sent
		echo '<span style="color:red;">Action '.__("ERROR: bad date","wassup").', '.$to_date.'</span>';
		exit;
	}
	// ACTION: SUMMARY PIE CHART - TODO
	if ($_GET['action'] == "piechart") {
		// Prepare Pie Chart
		$wTot = New WassupItems($table_name,$from_date,$to_date);
		$items_pie[] = $wTot->calc_tot("count", $search, "AND spam>0", "DISTINCT");
		$items_pie[] = $wTot->calc_tot("count", $search, "AND searchengine!='' AND spam=0", "DISTINCT");
		$items_pie[] = $wTot->calc_tot("count", $search, "AND searchengine='' AND referrer NOT LIKE '%".$this->WpUrl."%' AND referrer!='' AND spam=0", "DISTINCT");
		$items_pie[] = $wTot->calc_tot("count", $search, "AND searchengine='' AND (referrer LIKE '%".$this->WpUrl."%' OR referrer='') AND spam=0", "DISTINCT");
		echo $html_head;?>
		<div style="text-align: center"><img src="http://chart.apis.google.com/chart?cht=p3&amp;chco=0000ff&amp;chs=600x300&amp;chl=Spam|Search%20Engine|Referrer|Direct&amp;chd=<?php Gchart_data($items_pie, null, null, null, 'pie'); ?>" /></div><?php
		echo $html_foot;

	// ACTION: SHOW TOP TEN
	} elseif ($_GET['action'] == "topten"|| $_GET['action']=="Topstats") {
		$top_limit=0;
		$title="";
		$res=670;
		if(isset($_GET['width']) && is_numeric($_GET['width'])){
			$res = (int)$_GET['width'];
		}
		//show title and print button in popup window
		if(!empty($_GET['popup'])){
			$res=$wassup_options->wassup_screen_res;
			echo '<html>
<head>
<title>'.$title.'</title>
<link rel="stylesheet" id="wassup-style-css"  href="'.WASSUPURL.'/css/wassup.css?ver='.WASSUPVERSION.'" type="text/css" media="all" />
<script type="text/javascript">function printstat(){if(typeof(window.print)!="undefined")window.print();}</script>
</head>
<body class="wassup-ajax">
<div id="wassup-wrap" class="topstats topstats-print">'."\n";
			if($wdebug_mode){
				echo "<!-- *WassUp DEBUG On-->\n";
				echo "<!-- *normal setting: display_errors=$mode_reset -->\n";
			}
		}else{
			echo $html_head; 
			echo '<div id="wassup-wrap" class="topstats">'."\n";
			$title=false;
		}
		wassup_top10view($from_date,$to_date,$res,$top_limit,$title);
		echo '</div><!-- /wassup-wrap -->'."\n";
		echo $html_foot;
		exit;

	// ACTION: DISPLAY GEOGRAPHIC AND WHOIS DETAILS	- TODO
	} else {
		echo $html_head;
		echo '<span style="color:red;">Action.php '.__("ERROR: Missing or unknown parameters","wassup").', action='.esc_attr($_GET["action"]).'</span>';
		echo $html_foot;
	}  
	if ($wdebug_mode) {
		//$wpdb->print_error();	//debug
		ini_set('display_errors',$mode_reset);	//turn off debug
	}
} else {
	echo '<html><head><title>WassUp Action Error</title></head><body>';
	echo '<span style="color:red;">Action.php '.__("ERROR: Nothing to do here","wassup").'</span>';
	echo '</body></html>';
} //end if !$hashfail
?>
