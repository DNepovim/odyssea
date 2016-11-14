<?php
/*
Plugin Name: StatPress
Plugin URI: http://www.statpress.org
Description: Real time stats for your Wordpress blog
Version: 1.4.3
Author: Daniele Lippi
Author URI: http://www.danielelippi.it
*/

$_STATPRESS['version']='1.x';
$_STATPRESS['feedtype']='';

include ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/includes/charts.php';

function iri_add_pages() {
	# Crea/aggiorna tabella se non esiste
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		iri_StatPress_CreateTable();
	}
	# add submenu
	$mincap=get_option('statpress_mincap');
	if($mincap == '') {
		$mincap="level_8";
	}
// ORIG   add_submenu_page('index.php', 'StatPress', 'StatPress', 8, 'statpress', 'iriStatPress');

    add_menu_page('StatPress', 'StatPress', $mincap, __FILE__, 'iriStatPress');
    add_submenu_page(__FILE__, __('Overview','statpress'), __('Overview','statpress'), $mincap, __FILE__, 'iriStatPress');
    add_submenu_page(__FILE__, __('Details','statpress'), __('Details','statpress'), $mincap, __FILE__ . '&statpress_action=details', 'iriStatPress');
    add_submenu_page(__FILE__, __('Spy','statpress'), __('Spy','statpress'), $mincap, __FILE__ . '&statpress_action=spy', 'iriStatPress');
    add_submenu_page(__FILE__, __('Search','statpress'), __('Search','statpress'), $mincap, __FILE__ . '&statpress_action=search', 'iriStatPress');
    add_submenu_page(__FILE__, __('Export','statpress'), __('Export','statpress'), $mincap, __FILE__ . '&statpress_action=export', 'iriStatPress');
    add_submenu_page(__FILE__, __('Options','statpress'), __('Options','statpress'), $mincap, __FILE__ . '&statpress_action=options', 'iriStatPress');
    add_submenu_page(__FILE__, __('StatPressUpdate','statpress'), __('StatPressUpdate','statpress'), $mincap, __FILE__ . '&statpress_action=up', 'iriStatPress');
    add_submenu_page(__FILE__, __('Statpress blog','statpress'), __('Statpress blog','statpress'), $mincap, 'http://www.statpress.org');

}


function iriStatPress() {
?>
<?php
	if ($_GET['statpress_action'] == 'export') {
		iriStatPressExport();
	} elseif ($_GET['statpress_action'] == 'up') {
		iriStatPressUpdate();
	} elseif ($_GET['statpress_action'] == 'spy') {
		iriStatPressSpy();
	} elseif ($_GET['statpress_action'] == 'search') {
		iriStatPressSearch();
	} elseif ($_GET['statpress_action'] == 'details') {
		iriStatPressDetails();
	} elseif ($_GET['statpress_action'] == 'options') {
		iriStatPressOptions();
	} elseif(1) {
		iriStatPressMain();
	}
}

function iriStatPressOptions() {
	if($_POST['saveit'] == 'yes') {
		update_option('statpress_collectloggeduser', $_POST['statpress_collectloggeduser']);
		update_option('statpress_autodelete', $_POST['statpress_autodelete']);
		update_option('statpress_daysinoverviewgraph', $_POST['statpress_daysinoverviewgraph']);
		update_option('statpress_mincap', $_POST['statpress_mincap']);
		update_option('statpress_donotcollectspider', $_POST['statpress_donotcollectspider']);
		update_option('statpress_cryptip', $_POST['statpress_cryptip']);
		
		# update database too
		iri_StatPress_CreateTable();
		print "<br /><div class='updated'><p>".__('Saved','statpress')."!</p></div>";
	} else {
?>
	<div class='wrap'><h2><?php _e('Options','statpress'); ?></h2>
	<form method=post><table width=100%>
<?php
	print "<tr><td><input type=checkbox name='statpress_collectloggeduser' value='checked' ".get_option('statpress_collectloggeduser')."> ".__('Collect data about logged users, too.','statpress')."</td></tr>";
	print "<tr><td><input type=checkbox name='statpress_donotcollectspider' value='checked' ".get_option('statpress_donotcollectspider')."> ".__('Do not collect spiders visits','statpress')."</td></tr>";
	print "<tr><td><input type=checkbox name='statpress_cryptip' value='checked' ".get_option('statpress_cryptip')."> ".__('Crypt IP addresses','statpress')."</td></tr>";

?>
	<tr><td><?php _e('Automatically delete visits older than','statpress'); ?>
	<select name="statpress_autodelete">
	<option value="" <?php if(get_option('statpress_autodelete') =='' ) print "selected"; ?>><?php _e('Never delete!','statpress'); ?></option>
	<option value="1 month" <?php if(get_option('statpress_autodelete') == "1 month") print "selected"; ?>>1 <?php _e('month','statpress'); ?></option>
	<option value="3 months" <?php if(get_option('statpress_autodelete') == "3 months") print "selected"; ?>>3 <?php _e('months','statpress'); ?></option>
	<option value="6 months" <?php if(get_option('statpress_autodelete') == "6 months") print "selected"; ?>>6 <?php _e('months','statpress'); ?></option>
	<option value="1 year" <?php if(get_option('statpress_autodelete') == "1 year") print "selected"; ?>>1 <?php _e('year','statpress'); ?></option>
	</select></td></tr>

	<tr><td><?php  _e('Days in Overview graph','statpress'); ?>
	<select name="statpress_daysinoverviewgraph">
	<option value="7" <?php if(get_option('statpress_daysinoverviewgraph') == 7) print "selected"; ?>>7</option>
	<option value="10" <?php if(get_option('statpress_daysinoverviewgraph') == 10) print "selected"; ?>>10</option>
	<option value="20" <?php if(get_option('statpress_daysinoverviewgraph') == 20) print "selected"; ?>>20</option>
	<option value="30" <?php if(get_option('statpress_daysinoverviewgraph') == 30) print "selected"; ?>>30</option>
	<option value="50" <?php if(get_option('statpress_daysinoverviewgraph') == 50) print "selected"; ?>>50</option>
	</select></td></tr>

	<tr><td><?php _e('Minimum capability to view stats','statpress'); ?>
	<select name="statpress_mincap">
<?php iri_dropdown_caps(get_option('statpress_mincap')); ?>
	</select> 
	<a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank"><?php _e("more info",'statpress'); ?></a>
	</td></tr>
	
	<tr><td><br><input type=submit value="<?php _e('Save options','statpress'); ?>"></td></tr>
	</tr>
	</table>
	<input type=hidden name=saveit value=yes>
	<input type=hidden name=page value=statpress><input type=hidden name=statpress_action value=options>
	</form>
	</div>
<?php

	} # chiude saveit
}


function iri_dropdown_caps( $default = false ) {
	global $wp_roles;
	$role = get_role('administrator');
	foreach($role->capabilities as $cap => $grant) {
		print "<option ";
		if($default == $cap) { print "selected "; }
		print ">$cap</option>";
	}
}


function iriStatPressExport() {
?>
	<div class='wrap'><h2><?php _e('Export stats to text file','statpress'); ?> (csv)</h2>
	<form method=get><table>
	<tr><td><?php _e('From','statpress'); ?></td><td><input type=text name=from> (YYYYMMDD)</td></tr>
	<tr><td><?php _e('To','statpress'); ?></td><td><input type=text name=to> (YYYYMMDD)</td></tr>
	<tr><td><?php _e('Fields delimiter','statpress'); ?></td><td><select name=del><option>,</option><option>;</option><option>|</option></select></tr>
	<tr><td></td><td><input type=submit value=<?php _e('Export','statpress'); ?>></td></tr>
	<input type=hidden name=page value=statpress><input type=hidden name=statpress_action value=exportnow>
	</table></form>
	</div>
<?php
}

function iri_checkExport(){
	if ($_GET['statpress_action'] == 'exportnow') {
		$mincap=get_option('statpress_mincap');
		if ($mincap == '')
			$mincap = "level_8";
		if ( current_user_can( $mincap ) ) {
			iriStatPressExportNow();
		}
	}
}

function iriStatPressExportNow() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	$filename=get_bloginfo('title' )."-statpress_".$_GET['from']."-".$_GET['to'].".csv";
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=$filename");
	header('Content-Type: text/plain charset=' . get_option('blog_charset'), true);
    $qry = $wpdb->get_results("SELECT * FROM $table_name WHERE date>='".(date("Ymd",strtotime(substr($_GET['from'],0,8))))."' AND date<='".(date("Ymd",strtotime(substr($_GET['to'],0,8))))."';");
	$del=substr($_GET['del'],0,1);
	print "date".$del."time".$del."ip".$del."urlrequested".$del."agent".$del."referrer".$del."search".$del."nation".$del."os".$del."browser".$del."searchengine".$del."spider".$del."feed\n";
	foreach ($qry as $rk) {
		print '"'.$rk->date.'"'.$del.'"'.$rk->time.'"'.$del.'"'.$rk->ip.'"'.$del.'"'.$rk->urlrequested.'"'.$del.'"'.$rk->agent.'"'.$del.'"'.$rk->referrer.'"'.$del.'"'.$rk->search.'"'.$del.'"'.$rk->nation.'"'.$del.'"'.$rk->os.'"'.$del.'"'.$rk->browser.'"'.$del.'"'.$rk->searchengine.'"'.$del.'"'.$rk->spider.'"'.$del.'"'.$rk->feed.'"'."\n";

	}
	die();
}

function iriStatPressMain() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	
	# Tabella OVERVIEW
	$unique_color="#114477";
	$web_color="#3377B6";
	$rss_color="#f38f36";
	$spider_color="#83b4d8";
    $lastmonth = iri_StatPress_lastmonth();
    $thismonth = gmdate('Ym', current_time('timestamp'));
    $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
    $today = gmdate('Ymd', current_time('timestamp'));
    $tlm[0]=substr($lastmonth,0,4); $tlm[1]=substr($lastmonth,4,2);

	print "<div class='wrap'><h2>". __('Overview','statpress'). "</h2>";
	print "<table class='widefat'><thead><tr>
	<th scope='col'></th>
	<th scope='col'>". __('Total','statpress'). "</th>
	<th scope='col'>". __('Last month','statpress'). "<br /><font size=1>" . gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0])) ."</font></th>
	<th scope='col'>". __('This month','statpress'). "<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
	<th scope='col'>Target ". __('This month','statpress'). "<br /><font size=1>" . gmdate('M, Y', current_time('timestamp')) ."</font></th>
	<th scope='col'>". __('Yesterday','statpress'). "<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')-86400) ."</font></th>
	<th scope='col'>". __('Today','statpress'). "<br /><font size=1>" . gmdate('d M, Y', current_time('timestamp')) ."</font></th>
	</tr></thead>
	<tbody id='the-list'>";

	################################################################################################
	# VISITORS ROW
	print "<tr><td><div style='background:$unique_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Visitors','statpress'). "</td>";

	#TOTAL
	$qry_total = $wpdb->get_row("
		SELECT count(DISTINCT ip) AS visitors
		FROM $table_name
		WHERE feed=''
		AND spider=''
	");
	print "<td>" . $qry_total->visitors . "</td>\n";

	#LAST MONTH
	$qry_lmonth = $wpdb->get_row("
		SELECT count(DISTINCT ip) AS visitors
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date LIKE '" . $lastmonth . "%'
	");
	print "<td>" . $qry_lmonth->visitors . "</td>\n";

	#THIS MONTH
	$qry_tmonth = $wpdb->get_row("
		SELECT count(DISTINCT ip) AS visitors
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date LIKE '" . $thismonth . "%'
	");
	if($qry_lmonth->visitors <> 0) {
		$pc = round( 100 * ($qry_tmonth->visitors / $qry_lmonth->visitors ) - 100,1);
		if($pc >= 0) $pc = "+" . $pc;
		$qry_tmonth->change = "<code> (" . $pc . "%)</code>";
	}
	print "<td>" . $qry_tmonth->visitors . $qry_tmonth->change . "</td>\n";

	#TARGET
	$qry_tmonth->target = round($qry_tmonth->visitors / date("d", current_time('timestamp')) * 30);
	if($qry_lmonth->visitors <> 0) {
		$pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->visitors ) - 100,1);
		if($pt >= 0) $pt = "+" . $pt;
		$qry_tmonth->added = "<code> (" . $pt . "%)</code>";
	}
	print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

	#YESTERDAY
	$qry_y = $wpdb->get_row("
		SELECT count(DISTINCT ip) AS visitors
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date = '$yesterday'
	");
	print "<td>" . $qry_y->visitors . "</td>\n";

	#TODAY
	$qry_t = $wpdb->get_row("
		SELECT count(DISTINCT ip) AS visitors
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date = '$today'
	");
	print "<td>" . $qry_t->visitors . "</td>\n";
    print "</tr>";

	################################################################################################
	# PAGEVIEWS ROW
	print "<tr><td><div style='background:$web_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>". __('Pageviews','statpress'). "</td>";

	#TOTAL
	$qry_total = $wpdb->get_row("
		SELECT count(date) as pageview
		FROM $table_name
		WHERE feed=''
		AND spider=''
	");
	print "<td>" . $qry_total->pageview . "</td>\n";

	#LAST MONTH
	$prec=0;
	$qry_lmonth = $wpdb->get_row("
		SELECT count(date) as pageview
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date LIKE '" . $lastmonth . "%'
	");
	print "<td>".$qry_lmonth->pageview."</td>\n";

	#THIS MONTH
	$qry_tmonth = $wpdb->get_row("
		SELECT count(date) as pageview
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date LIKE '" . $thismonth . "%'
	");
	if($qry_lmonth->pageview <> 0) {
		$pc = round( 100 * ($qry_tmonth->pageview / $qry_lmonth->pageview ) - 100,1);
		if($pc >= 0) $pc = "+" . $pc;
		$qry_tmonth->change = "<code> (" . $pc . "%)</code>";
	}
	print "<td>" . $qry_tmonth->pageview . $qry_tmonth->change . "</td>\n";

	#TARGET
	$qry_tmonth->target = round($qry_tmonth->pageview / date("d", current_time('timestamp')) * 30);
	if($qry_lmonth->pageview <> 0) {
		$pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->pageview ) - 100,1);
		if($pt >= 0) $pt = "+" . $pt;
		$qry_tmonth->added = "<code> (" . $pt . "%)</code>";
	}
	print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

	#YESTERDAY
	$qry_y = $wpdb->get_row("
		SELECT count(date) as pageview
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date = '$yesterday'
	");
	print "<td>" . $qry_y->pageview . "</td>\n";

	#TODAY
	$qry_t = $wpdb->get_row("
		SELECT count(date) as pageview
		FROM $table_name
		WHERE feed=''
		AND spider=''
		AND date = '$today'
	");
	print "<td>" . $qry_t->pageview . "</td>\n";
	print "</tr>";
	################################################################################################
	# SPIDERS ROW
	print "<tr><td><div style='background:$spider_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>Spiders</td>";
	#TOTAL
	$qry_total = $wpdb->get_row("
		SELECT count(date) as spiders
		FROM $table_name
		WHERE feed=''
		AND spider<>''
	");
	print "<td>" . $qry_total->spiders . "</td>\n";
    #LAST MONTH
    $prec=0;
	$qry_lmonth = $wpdb->get_row("
		SELECT count(date) as spiders
		FROM $table_name
		WHERE feed=''
		AND spider<>''
		AND date LIKE '" . $lastmonth . "%'
	");
	print "<td>" . $qry_lmonth->spiders. "</td>\n";
	
	#THIS MONTH
	$prec=$qry_lmonth->spiders;
	$qry_tmonth = $wpdb->get_row("
		SELECT count(date) as spiders
		FROM $table_name
		WHERE feed=''
		AND spider<>''
		AND date LIKE '" . $thismonth . "%'
	");
	if($qry_lmonth->spiders <> 0) {
		$pc = round( 100 * ($qry_tmonth->spiders / $qry_lmonth->spiders ) - 100,1);
		if($pc >= 0) $pc = "+" . $pc;
		$qry_tmonth->change = "<code> (" . $pc . "%)</code>";
	}
	print "<td>" . $qry_tmonth->spiders . $qry_tmonth->change . "</td>\n";

	#TARGET
	$qry_tmonth->target = round($qry_tmonth->spiders / date("d", current_time('timestamp')) * 30);
	if($qry_lmonth->spiders <> 0) {
		$pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->spiders ) - 100,1);
		if($pt >= 0) $pt = "+" . $pt;
		$qry_tmonth->added = "<code> (" . $pt . "%)</code>";
	}
	print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

	#YESTERDAY
	$qry_y = $wpdb->get_row("
		SELECT count(date) as spiders
		FROM $table_name
		WHERE feed=''
		AND spider<>''
		AND date = '$yesterday'
	");
	print "<td>" . $qry_y->spiders . "</td>\n";
	
	#TODAY
	$qry_t = $wpdb->get_row("
		SELECT count(date) as spiders
		FROM $table_name
		WHERE feed=''
		AND spider<>''
		AND date = '$today'
	");
	print "<td>" . $qry_t->spiders . "</td>\n";
    print "</tr>";
	################################################################################################
	# FEEDS ROW
	print "<tr><td><div style='background:$rss_color;width:10px;height:10px;float:left;margin-top:4px;margin-right:5px;'></div>Feeds</td>";
	#TOTAL
	$qry_total = $wpdb->get_row("
		SELECT count(date) as feeds
		FROM $table_name
		WHERE feed<>''
		AND spider=''
	");
	print "<td>".$qry_total->feeds."</td>\n";

	#LAST MONTH
	$qry_lmonth = $wpdb->get_row("
		SELECT count(date) as feeds
		FROM $table_name
		WHERE feed<>''
		AND spider=''
		AND date LIKE '" . $lastmonth . "%'
	");
	print "<td>".$qry_lmonth->feeds."</td>\n";

	#THIS MONTH
	$qry_tmonth = $wpdb->get_row("
		SELECT count(date) as feeds
		FROM $table_name
		WHERE feed<>''
		AND spider=''
		AND date LIKE '" . $thismonth . "%'
	");
	if($qry_lmonth->feeds <> 0) {
		$pc = round( 100 * ($qry_tmonth->feeds / $qry_lmonth->feeds ) - 100,1);
		if($pc >= 0) $pc = "+" . $pc;
		$qry_tmonth->change = "<code> (" . $pc . "%)</code>";
	}
	print "<td>" . $qry_tmonth->feeds . $qry_tmonth->change . "</td>\n";

	#TARGET
	$qry_tmonth->target = round($qry_tmonth->feeds / date("d", current_time('timestamp')) * 30);
	if($qry_lmonth->feeds <> 0) {
		$pt = round( 100 * ($qry_tmonth->target / $qry_lmonth->feeds ) - 100,1);
		if($pt >= 0) $pt = "+" . $pt;
		$qry_tmonth->added = "<code> (" . $pt . "%)</code>";
	}
	print "<td>" . $qry_tmonth->target . $qry_tmonth->added . "</td>\n";

	$qry_y = $wpdb->get_row("
		SELECT count(date) as feeds
		FROM $table_name
		WHERE feed<>''
		AND spider=''
		AND date = '".$yesterday."'
	");
	print "<td>".$qry_y->feeds."</td>\n";

	$qry_t = $wpdb->get_row("
		SELECT count(date) as feeds
		FROM $table_name
		WHERE feed<>''
		AND spider=''
		AND date = '$today'
	");
	print "<td>".$qry_t->feeds."</td>\n";

    print "</tr></table><br />\n\n";
    
	################################################################################################
	################################################################################################
	# THE GRAPHS

	# last "N" days graph  NEW
	$gdays=get_option('statpress_daysinoverviewgraph'); if($gdays == 0) { $gdays=20; }
//	$start_of_week = get_settings('start_of_week');
	$start_of_week = get_option('start_of_week');
    print '<table width="100%" border="0"><tr>';
	$qry = $wpdb->get_row("
		SELECT count(date) as pageview, date
		FROM $table_name
		GROUP BY date HAVING date >= '".gmdate('Ymd', current_time('timestamp')-86400*$gdays)."'
		ORDER BY pageview DESC
		LIMIT 1
	");
	$maxxday=$qry->pageview;
	if($maxxday == 0) { $maxxday = 1; }
	# Y
	$gd=(90/$gdays).'%';
	for($gg=$gdays-1;$gg>=0;$gg--)
	{
		#TOTAL VISITORS
		$qry_visitors = $wpdb->get_row("
			SELECT count(DISTINCT ip) AS total
			FROM $table_name
			WHERE feed=''
			AND spider=''
			AND date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
		");
		$px_visitors = round($qry_visitors->total*100/$maxxday);

		#TOTAL PAGEVIEWS (we do not delete the uniques, this is falsing the info.. uniques are not different visitors!)
		$qry_pageviews = $wpdb->get_row("
			SELECT count(date) as total
			FROM $table_name
			WHERE feed=''
			AND spider=''
			AND date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
		");
		$px_pageviews = round($qry_pageviews->total*100/$maxxday);

		#TOTAL SPIDERS
		$qry_spiders = $wpdb->get_row("
			SELECT count(ip) AS total
			FROM $table_name
			WHERE feed=''
			AND spider<>''
			AND date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
		");
		$px_spiders = round($qry_spiders->total*100/$maxxday);

		#TOTAL FEEDS
		$qry_feeds = $wpdb->get_row("
			SELECT count(ip) AS total
			FROM $table_name
			WHERE feed<>''
			AND spider=''
			AND date = '".gmdate('Ymd', current_time('timestamp')-86400*$gg)."'
		");
		$px_feeds = round($qry_feeds->total*100/$maxxday);

		$px_white = 100 - $px_feeds - $px_spiders - $px_pageviews - $px_visitors;

		print '<td width="'.$gd.'" valign="bottom"';
		if($start_of_week == gmdate('w',current_time('timestamp')-86400*$gg)) { print ' style="border-left:2px dotted gray;"'; }  # week-cut
		print "><div style='float:left;height: 100%;width:100%;font-family:Helvetica;font-size:7pt;text-align:center;border-right:1px solid white;color:black;'>
		<div style='background:#ffffff;width:100%;height:".$px_white."px;'></div>
		<div style='background:$unique_color;width:100%;height:".$px_visitors."px;' title='".$qry_visitors->total." visitors'></div>
		<div style='background:$web_color;width:100%;height:".$px_pageviews."px;' title='".$qry_pageviews->total." pageviews'></div>
		<div style='background:$spider_color;width:100%;height:".$px_spiders."px;' title='".$qry_spiders->total." spiders'></div>
		<div style='background:$rss_color;width:100%;height:".$px_feeds."px;' title='".$qry_feeds->total." feeds'></div>
		<div style='background:gray;width:100%;height:1px;'></div>
		<br />".gmdate('d', current_time('timestamp')-86400*$gg) . ' ' . gmdate('M', current_time('timestamp')-86400*$gg) . "</div></td>\n";
	}
	print '</tr></table>';
	
	print '</div>';
# END OF OVERVIEW
####################################################################################################




	$querylimit="LIMIT 10";
	    
	# Tabella Last hits
	print "<div class='wrap'><h2>". __('Last hits','statpress'). "</h2><table class='widefat'><thead><tr><th scope='col'>". __('Date','statpress'). "</th><th scope='col'>". __('Time','statpress'). "</th><th scope='col'>IP</th><th scope='col'>". __('Country','statpress').'/'.__('Language','statpress'). "</th><th scope='col'>". __('Page','statpress'). "</th><th scope='col'>Feed</th><th></th><th scope='col' style='width:120px;'>OS</th><th></th><th scope='col' style='width:120px;'>Browser</th></tr></thead>";
	print "<tbody id='the-list'>";	

	$fivesdrafts = $wpdb->get_results("SELECT * FROM $table_name WHERE (os<>'' OR feed<>'') order by id DESC $querylimit");
	foreach ($fivesdrafts as $fivesdraft) {
		print "<tr>";
		print "<td>". irihdate($fivesdraft->date) ."</td>";
		print "<td>". $fivesdraft->time ."</td>";
		print "<td>". $fivesdraft->ip ."</td>";
		print "<td>". $fivesdraft->nation ."</td>";
		print "<td>". iri_StatPress_Abbrevia(iri_StatPress_Decode($fivesdraft->urlrequested),30) ."</td>";
		print "<td>". $fivesdraft->feed . "</td>";
		if($fivesdraft->os != '') {
			$img=str_replace(" ","_",strtolower($fivesdraft->os)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/os/$img'> </td>";
		} else {
			print "<td></td>";
		}
		print "<td>". $fivesdraft->os . "</td>";
		if($fivesdraft->browser != '') {
			$img=str_replace(" ","",strtolower($fivesdraft->browser)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/browsers/$img'></td>";
		} else {
			print "<td></td>";
		}
		print "<td>".$fivesdraft->browser."</td></tr>\n";
		print "</tr>";
	}
	print "</table></div>";
	
	
	# Last Search terms
	print "<div class='wrap'><h2>" . __('Last search terms','statpress') . "</h2><table class='widefat'><thead><tr><th scope='col'>".__('Date','statpress')."</th><th scope='col'>".__('Time','statpress')."</th><th scope='col'>".__('Terms','statpress')."</th><th scope='col'>". __('Engine','statpress'). "</th><th scope='col'>". __('Result','statpress'). "</th></tr></thead>";
	print "<tbody id='the-list'>";	
	$qry = $wpdb->get_results("SELECT date,time,referrer,urlrequested,search,searchengine FROM $table_name WHERE search<>'' ORDER BY id DESC $querylimit");
	foreach ($qry as $rk) {
		print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."'>".$rk->search."</a></td><td>".$rk->searchengine."</td><td><a href='".get_bloginfo('url')."/?".$rk->urlrequested."'>". __('page viewed','statpress'). "</a></td></tr>\n";
	}
	print "</table></div>";
	
	# Referrer
	print "<div class='wrap'><h2>".__('Last referrers','statpress')."</h2><table class='widefat'><thead><tr><th scope='col'>".__('Date','statpress')."</th><th scope='col'>".__('Time','statpress')."</th><th scope='col'>".__('URL','statpress')."</th><th scope='col'>".__('Result','statpress')."</th></tr></thead>";
	print "<tbody id='the-list'>";	
	$qry = $wpdb->get_results("SELECT date,time,referrer,urlrequested FROM $table_name WHERE ((referrer NOT LIKE '".get_option('home')."%') AND (referrer <>'') AND (searchengine='')) ORDER BY id DESC $querylimit");
	foreach ($qry as $rk) {
		print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."'>".iri_StatPress_Abbrevia($rk->referrer,80)."</a></td><td><a href='".get_bloginfo('url')."/?".$rk->urlrequested."'>". __('page viewed','statpress'). "</a></td></tr>\n";
	}
	print "</table></div>";


	# Last Agents
	print "<div class='wrap'><h2>".__('Last agents','statpress')."</h2><table class='widefat'><thead><tr><th scope='col'>".__('Agent','statpress')."</th><th scope='col'></th><th scope='col' style='width:120px;'>OS</th><th scope='col'></th><th scope='col' style='width:120px;'>Browser/Spider</th></tr></thead>";
	print "<tbody id='the-list'>";	
	$qry = $wpdb->get_results("SELECT agent,os,browser,spider FROM $table_name GROUP BY agent,os,browser,spider ORDER BY id DESC $querylimit");
	foreach ($qry as $rk) {
		print "<tr><td>".$rk->agent."</td>";
		if($rk->os != '') {
			$img=str_replace(" ","_",strtolower($rk->os)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/os/$img'> </td>";
		} else {
			print "<td></td>";
		}
		print "<td>". $rk->os . "</td>";
		if($rk->browser != '') {
			$img=str_replace(" ","",strtolower($rk->browser)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/browsers/$img'></td>";
		} else {
			print "<td></td>";
		}
		print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
	}
	print "</table></div>";


	# Last pages
	print "<div class='wrap'><h2>".__('Last pages','statpress')."</h2><table class='widefat'><thead><tr><th scope='col'>".__('Date','statpress')."</th><th scope='col'>".__('Time','statpress')."</th><th scope='col'>".__('Page','statpress')."</th><th scope='col' style='width:17px;'></th><th scope='col' style='width:120px;'>".__('OS','statpress')."</th><th style='width:17px;'></th><th scope='col' style='width:120px;'>".__('Browser','statpress')."</th></tr></thead>";
	print "<tbody id='the-list'>";	
	$qry = $wpdb->get_results("SELECT date,time,urlrequested,os,browser,spider FROM $table_name WHERE (spider='' AND feed='') ORDER BY id DESC $querylimit");
	foreach ($qry as $rk) {
		print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td>".iri_StatPress_Abbrevia(iri_StatPress_Decode($rk->urlrequested),60)."</td>";
		if($rk->os != '') {
			$img=str_replace(" ","_",strtolower($rk->os)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/os/$img'> </td>";
		} else {
			print "<td></td>";
		}
		print "<td>". $rk->os . "</td>";
		if($rk->browser != '') {
			$img=str_replace(" ","",strtolower($rk->browser)).".png";
			print "<td><IMG style='border:0px;width:16px;height:16px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/browsers/$img'></td>";
		} else {
			print "<td></td>";
		}
		print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
		
	}
	print "</table></div>";
	
	
	# Last Spiders
	print "<div class='wrap'><h2>".__('Last spiders','statpress')."</h2><table class='widefat'><thead><tr><th scope='col'>".__('Date','statpress')."</th><th scope='col'>".__('Time','statpress')."</th><th scope='col'>".__('Spider','statpress')."</th><th scope='col'>".__('Agent','statpress')."</th></tr></thead>";
	print "<tbody id='the-list'>";	
	$qry = $wpdb->get_results("SELECT date,time,agent,os,browser,spider FROM $table_name WHERE (spider<>'') ORDER BY id DESC $querylimit");
	foreach ($qry as $rk) {
		print "<tr><td>".irihdate($rk->date)."</td><td>".$rk->time."</td><td>".$rk->spider."</td><td> ".$rk->agent."</td></tr>\n";
	}
	print "</table></div>";
	
	
	print "<br />";
	print "&nbsp;<i>StatPress table size: <b>".iritablesize($wpdb->prefix . "statpress")."</b></i><br />";
	print "&nbsp;<i>StatPress current time: <b>".current_time('mysql')."</b></i><br />";
	print "&nbsp;<i>RSS2 url: <b>".get_bloginfo('rss2_url').' ('.iri_StatPress_extractfeedreq(get_bloginfo('rss2_url')).")</b></i><br />";
	
}	


function iri_StatPress_extractfeedreq($url) {
	list($null,$q)=explode("?",$url);
	list($res,$null)=explode("&",$q);
	return $res;
}

function iriStatPressDetails() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";

	$querylimit="LIMIT 10";

	# Top days
    iriValueTable2("date","Top days",5);

	# O.S.
    iriValueTable2("os","O.S.",10,"","","AND feed='' AND spider='' AND os<>''");

	# Browser
    iriValueTable2("browser","Browser",10,"","","AND feed='' AND spider='' AND browser<>''");	
	
	# Feeds
    iriValueTable2("feed","Feeds",5,"","","AND feed<>''");
    
	# SE
    iriValueTable2("searchengine","Search engines",10,"","","AND searchengine<>''");

	# Search terms
    iriValueTable2("search","Top search terms",20,"","","AND search<>''");

	# Top referrer
    iriValueTable2("referrer","Top referrer",10,"","","AND referrer<>'' AND referrer NOT LIKE '%".get_bloginfo('url')."%'");
 	
	# Languages
    iriValueTable2("nation","Countries/Languages",20,"","","AND nation<>'' AND spider=''");

	# Spider
    iriValueTable2("spider","Spiders",10,"","","AND spider<>''");

	# Top Pages
    iriValueTable2("urlrequested","Top pages",5,"","urlrequested","AND feed='' and spider=''");
	
	
	# Top Days - Unique visitors
    iriValueTable2("date","Top Days - Unique visitors",5,"distinct","ip","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */

    # Top Days - Pageviews
    iriValueTable2("date","Top Days - Pageviews",5,"","urlrequested","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */

    # Top IPs - Pageviews
    iriValueTable2("ip","Top IPs - Pageviews",5,"","urlrequested","AND feed='' and spider=''"); /* Maddler 04112007: required patching iriValueTable */
}


function iriStatPressSpy() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	
	# Spy
	$today = gmdate('Ymd', current_time('timestamp'));
	$yesterday = gmdate('Ymd', current_time('timestamp')-86400);
	print "<div class='wrap'><h2>".__('Spy','statpress')."</h2>";
	$sql="SELECT ip,nation,os,browser,agent FROM $table_name WHERE (spider='' AND feed='') AND (date BETWEEN '$yesterday' AND '$today') GROUP BY ip ORDER BY id DESC LIMIT 20";
	$qry = $wpdb->get_results($sql);
	
?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<div align="center">
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">
<?php
	foreach ($qry as $rk) {
		print "<tr><td colspan='2' bgcolor='#dedede'><div align='left'>";
		print "<IMG SRC='http://api.hostip.info/flag.php?ip=".$rk->ip."' border=0 width=18 height=12>";
		print " <strong><span><font size='2' color='#7b7b7b'>".$rk->ip."</font></span></strong> ";
		print "<span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('".$rk->ip."');>".__('more info','statpress')."</span></div>";
		print "<div id='".$rk->ip."' name='".$rk->ip."'>".$rk->os.", ".$rk->browser;
		if(get_option('statpress_cryptip')!='checked') {
			print "<br><iframe style='overflow:hide;border:0px;width:100%;height:30px;font-family:helvetica;paddng:0;' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=".$rk->ip."></iframe>";
		}
		print "<br><small>".gethostbyaddr($rk->ip)."</small>";
		print "<br><small>".$rk->agent."</small>";
		print "</div>";
		print "<script>document.getElementById('".$rk->ip."').style.display='none';</script>";
		print "</td></tr>";
		$qry2=$wpdb->get_results("SELECT * FROM $table_name WHERE ip='".$rk->ip."' AND (date BETWEEN '$yesterday' AND '$today') order by id LIMIT 10");
		foreach ($qry2 as $details) {
			print "<tr>";
			print "<td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>".irihdate($details->date)." ".$details->time."</strong></font></div></td>";
			print "<td><div><a href='".get_bloginfo('url')."/?".$details->urlrequested."' target='_blank'>".iri_StatPress_Decode($details->urlrequested)."</a>";
			if($details->searchengine != '') {
				print "<br><small>".__('arrived from','statpress')." <b>".$details->searchengine."</b> ".__('searching','statpress')." <a href='".$details->referrer."' target=_blank>".$details->search."</a></small>";
			} elseif($details->referrer != '' && strpos($details->referrer,get_option('home'))===FALSE) {
				print "<br><small>".__('arrived from','statpress')." <a href='".$details->referrer."' target=_blank>".$details->referrer."</a></small>";
			}
			print "</div></td>";
			print "</tr>\n";
		}
	}
?>
</table>
</div>
<?php
}



function iri_CheckIP($ip) {
	return ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip)) ? FALSE : TRUE;
}

function iriStatPressSearch($what='') {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	
	$f['urlrequested']=__('URL Requested','statpress');
	$f['agent']=__('Agent','statpress');
	$f['referrer']=__('Referrer','statpress');
	$f['search']=__('Search terms','statpress');
	$f['searchengine']=__('Search engine','statpress');
	$f['os']=__('Operative system','statpress');	
	$f['browser']="Browser";
	$f['spider']="Spider";
	$f['ip']="IP";
?>
	<div class='wrap'><h2><?php _e('Search','statpress'); ?></h2>
	<form method=get><table>
	<?php
		for($i=1;$i<=3;$i++) {
			print "<tr>";
			print "<td>".__('Field','statpress')." <select name=where$i><option value=''></option>";
			foreach ( array_keys($f) as $k ) {
				print "<option value='$k'";
				if($_GET["where$i"] == $k) { print " SELECTED "; }
				print ">".$f[$k]."</option>";
			}
			print "</select></td>";
			print "<td><input type=checkbox name=groupby$i value='checked' ".$_GET["groupby$i"]."> ".__('Group by','statpress')."</td>";
			print "<td><input type=checkbox name=sortby$i value='checked' ".$_GET["sortby$i"]."> ".__('Sort by','statpress')."</td>";
			print "<td>, ".__('if contains','statpress')." <input type=text name=what$i value='".$_GET["what$i"]."'></td>";
			print "</tr>";
		}
	?>
	</table>
	<br>
	<table>
	<tr>
		<td>
			<table>
				<tr><td><input type=checkbox name=oderbycount value=checked <?php print $_GET['oderbycount'] ?>> <?php _e('sort by count if grouped','statpress'); ?></td></tr>
				<tr><td><input type=checkbox name=spider value=checked <?php print $_GET['spider'] ?>> <?php _e('include spiders/crawlers/bot','statpress'); ?></td></tr>
				<tr><td><input type=checkbox name=feed value=checked <?php print $_GET['feed'] ?>> <?php _e('include feed','statpress'); ?></td></tr>
			</table>
		</td>
		<td width=15> </td>
		<td>
			<table>
				<tr>
					<td><?php _e('Limit results to','statpress'); ?>
						<select name=limitquery><?php if($_GET['limitquery'] >0) { print "<option>".$_GET['limitquery']."</option>";} ?><option>1</option><option>5</option><option>10</option><option>20</option><option>50</option></select>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td align=right><input type=submit value=<?php _e('Search','statpress'); ?> name=searchsubmit></td>
				</tr>
			</table>
		</td>
	</tr>		
	</table>	
	<input type=hidden name=page value='wp-statpress/statpress.php'><input type=hidden name=statpress_action value=search>
	</form><br>
<?php

 if(isset($_GET['searchsubmit'])) {
	# query builder
	$qry="";
	# FIELDS
	$fields="";
	for($i=1;$i<=3;$i++) {
		if($_GET["where$i"] != '') {
			$fields.=$_GET["where$i"].",";
		}
	}
	$fields=rtrim($fields,",");
	# WHERE
	$where="WHERE 1=1";
	if($_GET['spider'] != 'checked') { $where.=" AND spider=''"; }
	if($_GET['feed'] != 'checked') { $where.=" AND feed=''"; }
	for($i=1;$i<=3;$i++) {
		if(($_GET["what$i"] != '') && ($_GET["where$i"] != '')) {
			$where.=" AND ".$_GET["where$i"]." LIKE '%".$_GET["what$i"]."%'";
		}
	}
	# ORDER BY
	$orderby="";
	for($i=1;$i<=3;$i++) {
		if(($_GET["sortby$i"] == 'checked') && ($_GET["where$i"] != '')) {
			$orderby.=$_GET["where$i"].',';
		}
	}
		
	# GROUP BY
	$groupby="";
	for($i=1;$i<=3;$i++) {
		if(($_GET["groupby$i"] == 'checked') && ($_GET["where$i"] != '')) {
			$groupby.=$_GET["where$i"].',';
		}
	}
	if($groupby != '') {
		$groupby="GROUP BY ".rtrim($groupby,',');
		$fields.=",count(*) as totale";
		if($_GET['oderbycount'] == 'checked') { $orderby="totale DESC,".$orderby; }
	}
	
	if($orderby != '') { $orderby="ORDER BY ".rtrim($orderby,','); }
	

	$limit="LIMIT ".$_GET['limitquery'];

	# Results
	print "<h2>".__('Results','statpress')."</h2>";
	$sql="SELECT $fields FROM $table_name $where $groupby $orderby $limit;";
//	print "$sql<br>";
	print "<table class='widefat'><thead><tr>";
	for($i=1;$i<=3;$i++) { 
		if($_GET["where$i"] != '') { print "<th scope='col'>".ucfirst($_GET["where$i"])."</th>"; }
	}
	if($groupby != '') { print "<th scope='col'>".__('Count','statpress')."</th>"; }
	print "</tr></thead><tbody id='the-list'>";	
	$qry=$wpdb->get_results($sql,ARRAY_N);
	foreach ($qry as $rk) {
		print "<tr>";
		for($i=1;$i<=3;$i++) {
			print "<td>";
			if($_GET["where$i"] == 'urlrequested') { print iri_StatPress_Decode($rk[$i-1]); } else { print $rk[$i-1]; }
			print "</td>";
		}
		print "</tr>";
	}
	print "</table>";
	print "<br /><br /><font size=1 color=gray>sql: $sql</font></div>";
 }
	
}

function iri_StatPress_Abbrevia($s,$c) {
	$res=""; if(strlen($s)>$c) { $res="..."; }
	return substr($s,0,$c).$res;
	
}


function iri_StatPress_Decode($out_url) {
	if($out_url == '') { $out_url=__('Page','statpress').": Home"; }
	if(substr($out_url,0,4)=="cat=") { $out_url=__('Category','statpress').": ".get_cat_name(substr($out_url,4)); }
	if(substr($out_url,0,2)=="m=") { $out_url=__('Calendar','statpress').": ".substr($out_url,6,2)."/".substr($out_url,2,4); }
	if(substr($out_url,0,2)=="s=") { $out_url=__('Search','statpress').": ".substr($out_url,2); }
	if(substr($out_url,0,2)=="p=") {
		$post_id_7 = get_post(substr($out_url,2), ARRAY_A);
		$out_url = $post_id_7['post_title'];
	}
	if(substr($out_url,0,8)=="page_id=") {
		$post_id_7=get_page(substr($out_url,8), ARRAY_A);
		$out_url = __('Page','statpress').": ".$post_id_7['post_title'];
	}
	return $out_url;
}


function iri_StatPress_URL() {
    $urlRequested = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '' );
	if ( $urlRequested == "" ) { // SEO problem!
	    $urlRequested = (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '' );
	}
	if(substr($urlRequested,0,2) == '/?') { $urlRequested=substr($urlRequested,2); }
	if($urlRequested == '/') { $urlRequested=''; }
	return $urlRequested;
}


# Converte da data us to default format di Wordpress
function irihdate($dt = "00000000") {
	return mysql2date(get_option('date_format'), substr($dt,0,4)."-".substr($dt,4,2)."-".substr($dt,6,2));
}


function iritablesize($table) {
	global $wpdb;
	$res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
	foreach ($res as $fstatus) {
		$data_lenght = $fstatus->Data_length;
		$data_rows = $fstatus->Rows;
	}
	return number_format(($data_lenght/1024/1024), 2, ",", " ")." Mb ($data_rows records)";
}


function iriValueTable2($fld,$fldtitle,$limit = 0,$param = "", $queryfld = "", $exclude= "") {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	
	if ($queryfld == '') { $queryfld = $fld; }
	print "<div class='wrap'><table class='widefat'><thead><tr><th scope='col' style='width:400px;'><h2>$fldtitle</h2></th><th scope='col' style='width:100px;'>".__('Visits','statpress')."</th><th></th></tr></thead>";
	$rks = $wpdb->get_var("SELECT count($param $queryfld) as rks FROM $table_name WHERE 1=1 $exclude;"); 
	if($rks > 0) {
		$sql="SELECT count($param $queryfld) as pageview, $fld FROM $table_name WHERE 1=1 $exclude GROUP BY $fld ORDER BY pageview DESC";
		if($limit > 0) { $sql=$sql." LIMIT $limit"; }
		$qry = $wpdb->get_results($sql);
	    $tdwidth=450;
		
		// Collects data
		$data=array();
		foreach ($qry as $rk) {
			$pc=round(($rk->pageview*100/$rks),1);
			if($fld == 'nation') { $rk->$fld = strtoupper($rk->$fld); }
			if($fld == 'date') { $rk->$fld = irihdate($rk->$fld); }
			if($fld == 'urlrequested') { $rk->$fld = iri_StatPress_Decode($rk->$fld); }
        	$data[substr($rk->$fld,0,50)]=$rk->pageview;
		}
	}

	// Draw table body
	print "<tbody id='the-list'>";
	if($rks > 0) {  // Chart!
		if($fld == 'nation') {
			$chart=iriGoogleGeo("","",$data);
		} else {
			$chart=iriGoogleChart("","500x200",$data);
		}
		print "<tr><td></td><td></td><td rowspan='".($limit+2)."'>$chart</td></tr>";
		foreach ($data as $key => $value) {
    	   	print "<tr><td style='width:500px;overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>".$key;
        	print "</td><td style='width:100px;text-align:center;'>".$value."</td>";
			print "</tr>";
		}
	}
	print "</tbody></table></div><br>\n";
	
}


function iriGetLanguage($accepted) {
	return substr($accepted,0,2);
}


function iriGetQueryPairs($url){
$parsed_url = parse_url($url);
$tab=parse_url($url);
$host = $tab['host'];
if(key_exists("query",$tab)){
 $query=$tab["query"];
 return explode("&",$query);
}
else{return null;}
}


function iriGetOS($arg){
    $arg=str_replace(" ","",$arg);
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/os.dat');
	foreach($lines as $line_num => $os) {
		list($nome_os,$id_os)=explode("|",$os);
		if(strpos($arg,$id_os)===FALSE) continue;
    	return $nome_os; // riconosciuto
	}
    return '';
}


function iriGetBrowser($arg){
    $arg=str_replace(" ","",$arg);
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/browser.dat');
	foreach($lines as $line_num => $browser) {
		list($nome,$id)=explode("|",$browser);
		if(strpos($arg,$id)===FALSE) continue;
    	return $nome; // riconosciuto
	}
    return '';
}

function iriCheckBanIP($arg){
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/banips.dat');
	foreach($lines as $line_num => $banip) {
		if(strpos($arg,rtrim($banip,"\n"))===FALSE) continue;
    	return ''; // riconosciuto, da scartare
	}
    return $arg;
}

function iriGetSE($referrer = null){
	$key = null;
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/searchengines.dat');
	foreach($lines as $line_num => $se) {
		list($nome,$url,$key)=explode("|",$se);
		if(strpos($referrer,$url)===FALSE) continue;
		# trovato se
		$variables = iriGetQueryPairs(html_entity_decode($referrer));
		$i = count($variables);
		while($i--){
		   $tab=explode("=",$variables[$i]);
			   if($tab[0] == $key){return ($nome."|".urldecode($tab[1]));}
		}
	}
	return null;
}

function iriGetSpider($agent = null){
    $agent=str_replace(" ","",$agent);
	$key = null;
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/spider.dat');
	foreach($lines as $line_num => $spider) {
		list($nome,$key)=explode("|",$spider);
		if(strpos($agent,$key)===FALSE) continue;
		# trovato
		return $nome;
	}
	return null;
}


function iri_StatPress_lastmonth() {
  $ta = getdate(current_time('timestamp'));
    
  $year = $ta['year'];
  $month = $ta['mon'];
    
  --$month; // go back 1 month
    
  if( $month === 0 ): // if this month is Jan
    --$year; // go back a year
    $month = 12; // last month is Dec
  endif;
    
  // return in format 'YYYYMM'
  return sprintf( $year.'%02d', $month); 
}


function iri_StatPress_CreateTable() {
	global $wpdb;
	global $wp_db_version;
	$table_name = $wpdb->prefix . "statpress";
	$sql_createtable = "CREATE TABLE " . $table_name . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	date char(8),
	time char(8),
	ip char(15),
	urlrequested varchar(250),
	agent varchar(250),
	referrer varchar(250),
	search varchar(250),
	nation varchar(2),
	os varchar(30),
	browser varchar(32),
	searchengine varchar(16),
	spider varchar(32),
	feed varchar(8),
	user varchar(16),
	timestamp varchar(10),
	UNIQUE KEY id (id)
	);";
	if($wp_db_version >= 5540)	$page = 'wp-admin/includes/upgrade.php';  
								else $page = 'wp-admin/upgrade'.'-functions.php';
	require_once(ABSPATH . $page);
	dbDelta($sql_createtable);	
}

function iri_StatPress_is_feed($url) {
	if (stristr($url,get_bloginfo('rdf_url')) != FALSE) { return 'RDF'; }
	if (stristr($url,get_bloginfo('rss2_url')) != FALSE) { return 'RSS2'; }
	if (stristr($url,get_bloginfo('rss_url')) != FALSE) { return 'RSS'; }
	if (stristr($url,get_bloginfo('atom_url')) != FALSE) { return 'ATOM'; }
	if (stristr($url,get_bloginfo('comments_rss2_url')) != FALSE) { return 'COMMENT'; }
	if (stristr($url,get_bloginfo('comments_atom_url')) != FALSE) { return 'COMMENT'; }
	if (stristr($url,'wp-feed.php') != FALSE) { return 'RSS2'; }
	if (stristr($url,'/feed/') != FALSE) { return 'RSS2'; }
	return '';
}

function iriStatAppend() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	global $userdata;
	global $_STATPRESS;
    get_currentuserinfo();
	$feed='';
	
	// Time
	$timestamp  = current_time('timestamp');
	$vdate  = gmdate("Ymd",$timestamp);
	$vtime  = gmdate("H:i:s",$timestamp);

	// IP
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if(iriCheckBanIP($ipAddress) == '') { return ''; }
	if(get_option('statpress_cryptip')=='checked') {
		$ipAddress = crypt($ipAddress,'statpress');
	}
	
	// URL (requested)
	$urlRequested=iri_StatPress_URL();
	if (eregi(".ico$", $urlRequested)) { return ''; }
	if (eregi("favicon.ico", $urlRequested)) { return ''; }
	if (eregi(".css$", $urlRequested)) { return ''; }
	if (eregi(".js$", $urlRequested)) { return ''; }
	if (stristr($urlRequested,"/wp-content/plugins") != FALSE) { return ''; }
	if (stristr($urlRequested,"/wp-content/themes") != FALSE) { return ''; }
	if (stristr($urlRequested,"/wp-admin/") != FALSE) { return ''; }
	$urlRequested=mysql_real_escape_string($urlRequested);
	
	$referrer = (isset($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER']) : '');
	$referrer=mysql_real_escape_string($referrer);
	
	$userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? htmlentities($_SERVER['HTTP_USER_AGENT']) : '');
	$userAgent=mysql_real_escape_string($userAgent);
	
	$spider=iriGetSpider($userAgent);
	
   	if(($spider != '') and (get_option('statpress_donotcollectspider')=='checked')) { return ''; }
    
   	if($spider != '') {
	    $os=''; $browser='';
	} else {
		// Trap feeds
		$feed=iri_StatPress_is_feed(get_bloginfo('url').$_SERVER['REQUEST_URI']);
		// Get OS and browser
		$os=iriGetOS($userAgent);
		$browser=iriGetBrowser($userAgent);
		list($searchengine,$search_phrase)=explode("|",iriGetSE($referrer));
	}
	// Country (ip2nation table) or language
	$countrylang="";
	if($wpdb->get_var("SHOW TABLES LIKE 'ip2nation'") == 'ip2nation') {
		$sql='SELECT * FROM ip2nation WHERE ip < INET_ATON("'.$ipAddress.'") ORDER BY ip DESC LIMIT 0,1';
		$qry = $wpdb->get_row($sql);
		$countrylang=$qry->country;
	}
	if($countrylang == '') {
		$countrylang=iriGetLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}
	// Auto-delete visits if...
	if(get_option('statpress_autodelete') != '') {
		$t=gmdate("Ymd",strtotime('-'.get_option('statpress_autodelete')));
		$results =	$wpdb->query( "DELETE FROM " . $table_name . " WHERE date < '" . $t . "'");
	}
    if ((!is_user_logged_in()) OR (get_option('statpress_collectloggeduser')=='checked')) {
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			iri_StatPress_CreateTable();
		}
		$insert = "INSERT INTO " . $table_name .
            " (date, time, ip, urlrequested, agent, referrer, search,nation,os,browser,searchengine,spider,feed,user,timestamp) " .
            "VALUES ('$vdate','$vtime','$ipAddress','$urlRequested','".addslashes(strip_tags($userAgent))."','$referrer','" .
            addslashes(strip_tags($search_phrase))."','".$countrylang."','$os','$browser','$searchengine','$spider','$feed','$userdata->user_login','$timestamp')";
		$results = $wpdb->query( $insert );
	}
}


function iriStatPressUpdate() {
	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	
	$wpdb->show_errors();

	print "<div class='wrap'><table class='widefat'><thead><tr><th scope='col'><h2>".__('Updating...','statpress')."</h2></th><th scope='col' style='width:150px;'>".__('Size','statpress')."</th><th scope='col' style='width:100px;'>".__('Result','statpress')."</th><th></th></tr></thead>";
	print "<tbody id='the-list'>";

	# check if ip2nation .sql file exists
	if(file_exists(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/ip2nation.sql')) {
		print "<tr><td>ip2nation.sql</td>";
		$FP = fopen (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/ip2nation.sql', 'r' ); 
		$READ = fread ( $FP, filesize (ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/ip2nation.sql') ); 
		$READ = explode ( ";\n", $READ ); 
		foreach ( $READ as $RED ) { 
			if($RES != '') { $wpdb->query($RED); }
		} 
		print "<td>".iritablesize("ip2nation")."</td>";
		print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";
	}

	# update table
	print "<tr><td>Struct $table_name</td>";
	iri_StatPress_CreateTable();
	print "<td>".iritablesize($wpdb->prefix."statpress")."</td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";
	
	# Update Feed
	print "<tr><td>Feeds</td>";
    $wpdb->query("UPDATE $table_name SET feed='';");
    # not standard
    $wpdb->query("UPDATE $table_name SET feed='RSS2' WHERE urlrequested LIKE '%/feed/%';");
    $wpdb->query("UPDATE $table_name SET feed='RSS2' WHERE urlrequested LIKE '%wp-feed.php%';");
	# standard blog info urls
	$s=iri_StatPress_extractfeedreq(get_bloginfo('comments_atom_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='COMMENT' WHERE INSTR(urlrequested,'$s')>0;");
	}
	$s=iri_StatPress_extractfeedreq(get_bloginfo('comments_rss2_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='COMMENT' WHERE INSTR(urlrequested,'$s')>0;");
	}
	$s=iri_StatPress_extractfeedreq(get_bloginfo('atom_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='ATOM' WHERE INSTR(urlrequested,'$s')>0;");
	}
	$s=iri_StatPress_extractfeedreq(get_bloginfo('rdf_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='RDF'  WHERE INSTR(urlrequested,'$s')>0;");
	}
	$s=iri_StatPress_extractfeedreq(get_bloginfo('rss_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='RSS'  WHERE INSTR(urlrequested,'$s')>0;");
	}
	$s=iri_StatPress_extractfeedreq(get_bloginfo('rss2_url'));
	if($s != '') {
	    $wpdb->query("UPDATE $table_name SET feed='RSS2' WHERE INSTR(urlrequested,'$s')>0;");
	}

	$wpdb->query("UPDATE $table_name SET feed = '' WHERE isnull(feed);");	

	print "<td></td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";

	# Update OS
	print "<tr><td>OSes</td>";
    $wpdb->query("UPDATE $table_name SET os = '';");
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/os.dat');
	foreach($lines as $line_num => $os) {
		list($nome_os,$id_os)=explode("|",$os);
		$qry="UPDATE $table_name SET os = '$nome_os' WHERE os='' AND replace(agent,' ','') LIKE '%".$id_os."%';";
		$wpdb->query($qry);
	}
	print "<td></td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";

	
	# Update Browser
	print "<tr><td>Browsers</td>";
    $wpdb->query("UPDATE $table_name SET browser = '';");
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/browser.dat');
	foreach($lines as $line_num => $browser) {
		list($nome,$id)=explode("|",$browser);
		$qry="UPDATE $table_name SET browser = '$nome' WHERE browser='' AND replace(agent,' ','') LIKE '%".$id."%';";
		$wpdb->query($qry);
	}
	print "<td></td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";


	# Update Spider
	print "<tr><td>Spiders</td>";
    $wpdb->query("UPDATE $table_name SET spider = '';");
	$lines = file(ABSPATH.'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/def/spider.dat');
	foreach($lines as $line_num => $spider) {
		list($nome,$id)=explode("|",$spider);
		$qry="UPDATE $table_name SET spider = '$nome',os='',browser='' WHERE spider='' AND replace(agent,' ','') LIKE '%".$id."%';";
		$wpdb->query($qry);
	}
	print "<td></td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";


	# Update Search engine
	print "<tr><td>Search engines</td>";
	$wpdb->query("UPDATE $table_name SET searchengine = '', search='';");
	$qry = $wpdb->get_results("SELECT id, referrer FROM $table_name");
	foreach ($qry as $rk) {
		list($searchengine,$search_phrase)=explode("|",iriGetSE($rk->referrer));
		if($searchengine <> '') {
			$q="UPDATE $table_name SET searchengine = '$searchengine', search='".addslashes($search_phrase)."' WHERE id=".$rk->id;
			$wpdb->query($q);
		}
	}
	print "<td></td>";
	print "<td><IMG style='border:0px;width:20px;height:20px;' SRC='/wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/ok.gif'></td></tr>";

	
	print "</tbody></table></div><br>\n";
	$wpdb->hide_errors();
}


function StatPress_Widget($w='') {

}

function StatPress_Print($body='') {
	print iri_StatPress_Vars($body);
}


function iri_StatPress_Vars($body) {
   	global $wpdb;
	$table_name = $wpdb->prefix . "statpress";
	if(strpos(strtolower($body),"%visits%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as pageview FROM $table_name WHERE date = '".gmdate("Ymd",current_time('timestamp'))."' and spider='' and feed='';");
		$body = str_replace("%visits%", $qry[0]->pageview, $body);
	}
	if(strpos(strtolower($body),"%totalvisits%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as pageview FROM $table_name WHERE spider='' and feed='';");
		$body = str_replace("%totalvisits%", $qry[0]->pageview, $body);
	}
	if(strpos(strtolower($body),"%thistotalvisits%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as pageview FROM $table_name WHERE spider='' and feed='' AND urlrequested='".iri_StatPress_URL()."';");
		$body = str_replace("%thistotalvisits%", $qry[0]->pageview, $body);
	}
	if(strpos(strtolower($body),"%since%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT date FROM $table_name ORDER BY date LIMIT 1;");
		$body = str_replace("%since%", irihdate($qry[0]->date), $body);
	}
	if(strpos(strtolower($body),"%os%") !== FALSE) {
        $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
   	   	$os=iriGetOS($userAgent);
       	$body = str_replace("%os%", $os, $body);
    }
	if(strpos(strtolower($body),"%browser%") !== FALSE) {
		$browser=iriGetBrowser($userAgent);
   	   	$body = str_replace("%browser%", $browser, $body);
   	}
	if(strpos(strtolower($body),"%ip%") !== FALSE) { 	
   	    $ipAddress = $_SERVER['REMOTE_ADDR'];
   	   	$body = str_replace("%ip%", $ipAddress, $body);
   	}
	if(strpos(strtolower($body),"%visitorsonline%") !== FALSE) { 	
		$to_time = current_time('timestamp');
		$from_time = strtotime('-4 minutes', $to_time);
		$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as visitors FROM $table_name WHERE spider='' and feed='' AND timestamp BETWEEN $from_time AND $to_time;");
   	   	$body = str_replace("%visitorsonline%", $qry[0]->visitors, $body);
   	}
	if(strpos(strtolower($body),"%usersonline%") !== FALSE) { 	
		$to_time = current_time('timestamp');
		$from_time = strtotime('-4 minutes', $to_time);
		$qry = $wpdb->get_results("SELECT count(DISTINCT(ip)) as users FROM $table_name WHERE spider='' and feed='' AND user<>'' AND timestamp BETWEEN $from_time AND $to_time;");
   	   	$body = str_replace("%usersonline%", $qry[0]->users, $body);
   	}
	if(strpos(strtolower($body),"%toppost%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT urlrequested,count(*) as totale FROM $table_name WHERE spider='' AND feed='' AND urlrequested LIKE '%p=%' GROUP BY urlrequested ORDER BY totale DESC LIMIT 1;");
		$body = str_replace("%toppost%", iri_StatPress_Decode($qry[0]->urlrequested), $body);
	}
	if(strpos(strtolower($body),"%topbrowser%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT browser,count(*) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY browser ORDER BY totale DESC LIMIT 1;");
		$body = str_replace("%topbrowser%", iri_StatPress_Decode($qry[0]->browser), $body);
	}
	if(strpos(strtolower($body),"%topos%") !== FALSE) {
		$qry = $wpdb->get_results("SELECT os,count(*) as totale FROM $table_name WHERE spider='' AND feed='' GROUP BY os ORDER BY totale DESC LIMIT 1;");
		$body = str_replace("%topos%", iri_StatPress_Decode($qry[0]->os), $body);
	}
	return $body;
}


function iri_StatPress_TopPosts($limit=5, $showcounts='checked') {
   	global $wpdb;
   	$res="\n<ul>\n";
	$table_name = $wpdb->prefix . "statpress";
	$qry = $wpdb->get_results("SELECT urlrequested,count(*) as totale FROM $table_name WHERE spider='' AND feed='' AND urlrequested LIKE '%p=%' GROUP BY urlrequested ORDER BY totale DESC LIMIT $limit;");
	foreach ($qry as $rk) {
		$res.="<li><a href='?".$rk->urlrequested."'>".iri_StatPress_Decode($rk->urlrequested)."</a></li>\n";
		if(strtolower($showcounts) == 'checked') { $res.=" (".$rk->totale.")"; }
	}
	return "$res</ul>\n";
}


function widget_statpress_init($args) {
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	// Multifunctional StatPress pluging
	function widget_statpress_control() {
		$options = get_option('widget_statpress');
		if ( !is_array($options) )
			$options = array('title'=>'StatPress', 'body'=>'Visits today: %visits%');
		if ( $_POST['statpress-submit'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['statpress-title']));
			$options['body'] = stripslashes($_POST['statpress-body']);
			update_option('widget_statpress', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$body = htmlspecialchars($options['body'], ENT_QUOTES);
		// the form
		echo '<p style="text-align:right;"><label for="statpress-title">' . __('Title:') . ' <input style="width: 250px;" id="statpress-title" name="statpress-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="statpress-body"><div>' . __('Body:', 'widgets') . '</div><textarea style="width: 288px;height:100px;" id="statpress-body" name="statpress-body" type="textarea">'.$body.'</textarea></label></p>';
		echo '<input type="hidden" id="statpress-submit" name="statpress-submit" value="1" /><div style="font-size:7pt;">%totalvisits% %visits% %thistotalvisits% %os% %browser% %ip% %since% %visitorsonline% %usersonline% %toppost% %topbrowser% %topos%</div>';
	}
	function widget_statpress($args) {
	    extract($args);
		$options = get_option('widget_statpress');
		$title = $options['title'];
		$body = $options['body'];
    	echo $before_widget;
    	print($before_title . $title . $after_title);
		print iri_StatPress_Vars($body);
	    echo $after_widget;
    }
   	register_sidebar_widget('StatPress', 'widget_statpress');
	register_widget_control(array('StatPress','widgets'), 'widget_statpress_control', 300, 210);

    // Top posts
    function widget_statpresstopposts_control() {
		$options = get_option('widget_statpresstopposts');
		if ( !is_array($options) ) {
			$options = array('title'=>'StatPress TopPosts', 'howmany'=>'5', 'showcounts'=>'checked');
		}
		if ( $_POST['statpresstopposts-submit'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['statpresstopposts-title']));
			$options['howmany'] = stripslashes($_POST['statpresstopposts-howmany']);
			$options['showcounts'] = stripslashes($_POST['statpresstopposts-showcounts']);
			if($options['showcounts'] == "1") {$options['showcounts']='checked';}
			update_option('widget_statpresstopposts', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
		$showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
		// the form
		echo '<p style="text-align:right;"><label for="statpresstopposts-title">' . __('Title','statpress') . ' <input style="width: 250px;" id="statpress-title" name="statpresstopposts-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="statpresstopposts-howmany">' . __('Limit results to','statpress') . ' <input style="width: 100px;" id="statpresstopposts-howmany" name="statpresstopposts-howmany" type="text" value="'.$howmany.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="statpresstopposts-showcounts">' . __('Visits','statpress') . ' <input id="statpresstopposts-showcounts" name="statpresstopposts-showcounts" type=checkbox value="checked" '.$showcounts.' /></label></p>';
		echo '<input type="hidden" id="statpress-submitTopPosts" name="statpresstopposts-submit" value="1" />';
	}
	function widget_statpresstopposts($args) {
	    extract($args);
		$options = get_option('widget_statpresstopposts');
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$howmany = htmlspecialchars($options['howmany'], ENT_QUOTES);
		$showcounts = htmlspecialchars($options['showcounts'], ENT_QUOTES);
    	echo $before_widget;
    	print($before_title . $title . $after_title);
		print iri_StatPress_TopPosts($howmany,$showcounts);
	    echo $after_widget;
    }
	register_sidebar_widget('StatPress TopPosts', 'widget_statpresstopposts');
	register_widget_control(array('StatPress TopPosts','widgets'), 'widget_statpresstopposts_control', 300, 110);
}


load_plugin_textdomain('statpress', 'wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/locale');

add_action('admin_menu', 'iri_add_pages');
add_action('plugins_loaded', 'widget_statpress_init');
add_action('send_headers', 'iriStatAppend');  //add_action('wp_head', 'iriStatAppend');
add_action('init','iri_checkExport');

register_activation_hook(__FILE__,'iri_StatPress_CreateTable');

?>
