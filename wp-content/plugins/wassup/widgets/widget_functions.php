<?php
/**
 * Functions for displaying WassUp aside widgets and wassup_sidebar template tag
 *
 * @package WassUp Real-time Analytics
 * @subpackage widget-functions.php module
 * @since:	v1.9
 * @author:	Helene D. <http://helenesit.com>
 */
//no direct request for this plugin module
$wfile=preg_replace('/\\\\/','/',__FILE__); //for windows
if(!defined('ABSPATH')|| empty($GLOBALS['wp_version'])|| preg_match('#'.preg_quote(basename($wfile)).'#',$_SERVER['PHP_SELF'])|| !empty($_SERVER['SCRIPT_FILENAME'])&& realpath($wfile)===realpath($_SERVER['SCRIPT_FILENAME'])){
	if(!headers_sent()){header('Location: /?p=404page&err=wassup403');exit;
	}elseif(function_exists('wp_die')){wp_die("Bad Request: ".esc_attr(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI']),array())));exit;
	}else{die("Bad Request: ".htmlspecialchars(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI'])));exit;}
	exit;
}
unset($wfile);

/**
 * Retrieve widget html from Wassup cache using widget_id as 'wassup key' indentifier
 *
 * @param string(2) $widget_id, $cache_key
 * @return string $html
 */
function wassup_widget_get_cache($widget_id,$cache_key){
	global $wdebug_mode;
	$html=false;
	if(empty($widget_id))$widget_id="wassup_widget";
	if($wdebug_mode) echo "\n<!-- checking for $widget_id cache... -->";
	if(!empty($cache_key)){
		$html=html_entity_decode(wassupDb::get_wassupmeta($widget_id,$cache_key)); //html is encoded in table
	}
	return $html;
}
/**
 * Save widget html to Wassup cache using widget_id as 'wassup key' indentifier
 *
 * @param string(3),integer ($html,$widget_id,$cache_key,$refresh)
 * @return string $cacheid
 */
function wassup_widget_save_cache($html,$widget_id,$cache_key,$refresh=60){
	global $wp_version;
	$cacheid=0;
	if(!empty($html)){
		if(empty($widget_id))$widget_id="wassup_widget";
		if(empty($cache_key))$cache_key="_online";
		if(empty($refresh) || !is_numeric($refresh))$refresh=60;
		$expire=time()+ (int)$refresh;
		//$html=esc_html($html);
		$cacheid=wassupDb::update_wassupmeta($widget_id,$cache_key,$html,$expire);
	}
	return $cacheid;
}

/**
 * Purge widget from cache using widget_id as 'wassup key' indentifier
 *
 * @param string $widget_id
 * @return void
 */
function wassup_widget_clear_cache($widget_id){
	if(empty($widget_id))$widget_id="wassup_widget";
	$cdel=wassupDb::delete_wassupmeta("",$widget_id,"*");
}

/**
 * Adds Wassup widget stylesheet tag to site
 *
 * -adds styles for monospaced numbers and background colors.
 * -$embed arg substitues stylesheet tag with an echoed output for inline styling in 'wassup_sidebar' template tag.
 *
 * @param boolean $embed
 * @return void
 */
function wassup_widget_css($embed=false){
	global $wdebug_mode;
	$vers=WASSUPVERSION;
	if($wdebug_mode) $vers.= 'b'.rand(0,9999);
	if(empty($embed)){
		echo "\n";?>
<link rel="stylesheet" href="<?php echo WASSUPURL.'/css/wassup-widget.css?ver='.$vers;?>" type="text/css" /><?php
	}else{
		echo "\n";?>
<style type="text/css" media="all"><?php 
	echo "\n";
	include WASSUPDIR.'/css/wassup-widget.css'; ?>
</style><?php
	}//end if embed
}

/** Embeds form styles in admin head for widget control styling */
function wassup_widget_form_css(){
	global $wp_version,$wdebug_mode;
	$vers=WASSUPVERSION;
	if($wdebug_mode)$vers.='b'.rand(0,9999);
	if(!function_exists('wp_enqueue_style')){?>
<link rel="stylesheet" href="<?php echo WASSUPURL.'/css/wassup.css?ver='.$vers;?>" type="text/css" /><?php
		echo "\n";
	}
	//some override styles for Wordpress 2.2-3.8
  	if(version_compare($wp_version,'3.8','<')){?>
<style type="text/css"><?php
		if(version_compare($wp_version,'2.8','<')) echo "\n".'.wassup-widget-ctrl{margin:-15px -25px 0;}';
		else echo "\n".'.wassup-widget-ctrl{margin:-10px -11px 0;}';
		echo "\n".'.wassup-widget-ctrl td{padding:0;line-height:1.1em;}'."\n";?>
</style><?php
		echo "\n";
	}
}

/**
 * Returns html for wassup tag line to add to widget footer
 *
 * @param none
 * @return string $html
 */
function wassup_widget_foot_meta(){
	$html='
	<p class="wassup-marque">'.__("powered by","wassup").' <a href="http://www.wpwp.org/" title="WassUp '.__("Real Time Visitors Tracking","wassup").'">WassUp</a></p>';
	return "$html";
}

/**
 * Display list of current Visitors Online counts
 *
 * @todo - add 'show loggedin avatars" as an option
 * @param array $instance
 * @return string $html
 */
function wassup_widget_get_online_counts($instance=array()){
	global $wpdb,$wassup_options,$wdebug_mode;
	$html="";
	$defaults=array('online_total'=>1,'online_loggedin'=>0,'online_comauth'=>0,'online_anony'=>0,'online_other'=>0,'show_usernames'=>0,'show_avatars'=>0,'show_flags'=>0);
	if(empty($instance)|| !is_array($instance)) $instance=$defaults;
	else $instance=wp_parse_args($instance,$defaults);
	$wonline_table=$wassup_options->wassup_table . "_tmp";
	$currenttot=0;
	$currentlogged=0;
	$currentcomm=0;
	$currentanony=0;
	$currentother=0;
	$currentnames=false;
	$currentflags=false;
	if(!empty($instance['online_total'])|| !empty($instance['online_loggedin'])|| !empty($instance['online_comauth'])|| !empty($instance['online_anonymous'])|| !empty($instance['online_other'])|| !empty($instance['show_usernames'])|| !empty($instance['show_avatars'])|| !empty($instance['show_flags'])){
		$to_date = current_time('timestamp');
		//variable timeframes for online counts: spiders for 1 min, regular visitors for 3 minutes, logged-in users for 10 minutes
		$from_date = $to_date - 10*60;	//-10 minutes
		$whereis=sprintf("`timestamp`>'%d' AND (`username`!='' OR `timestamp`>'%d' OR (`timestamp`>'%d' AND `spider`=''))",$from_date,$to_date - 1*60,$to_date - 3*60);
		//for multisite/network activation
		$multisite_whereis="";
		if($wassup_options->network_activated_plugin() && !empty($GLOBALS['current_blog']->blog_id)){
			$multisite_whereis = sprintf(" AND `subsite_id`=%s",$GLOBALS['current_blog']->blog_id);
		}
		$whereis .= $multisite_whereis;
		if(!class_exists('WassupItems'))include_once(WASSUPDIR ."/lib/main.php");
		$TotWid=new WassupItems($wonline_table,"","",$whereis);
		$currenttot=$TotWid->totrecords;
		if($wdebug_mode) echo "\n<!-- counting online visitors ... -->";
		if($currenttot>0){
			$currenttot=$TotWid->calc_tot("count",null,null,"DISTINCT");
			if(!empty($instance['online_loggedin']))
				$currentlogged=$TotWid->calc_tot("count",null,"AND `username`!=''","DISTINCT");
			if(!empty($instance['online_comauth']))
				$currentcomm=$TotWid->calc_tot("count",null,"AND `comment_author`!='' AND `username`='' AND `spam`='0'","DISTINCT");
			if(!empty($instance['online_anonymous']))
				$currentanony=$TotWid->calc_tot("count",null,"AND `username`='' AND `comment_author`='' AND `spider`='' AND `spam`='0'","DISTINCT");
			if(!empty($instance['online_other']))
				$currentother=$TotWid->calc_tot("count",null,"AND `username`='' AND ((`comment_author`='' AND `spider`!='') OR `spam`!='0')","DISTINCT");
			//get usernames (and avatars - TODO)
			$qry="";
			if($currentlogged >0 && !empty($instance['show_usernames'])){
				$qry=sprintf("SELECT DISTINCT `username` FROM $wonline_table WHERE `timestamp`>'%d' AND `username`!='' AND `username`!='admin' AND`spam`='0' %s ORDER BY `username`",$to_date-10*60,$multisite_whereis);
				$currentnames=$wpdb->get_col($qry);
				if($wdebug_mode){
					if(empty($currentnames) || is_wp_error($currentnames)){
						$currentnames=false;
						echo "\n".'<!-- No results from $qry='.$qry.' -->';
					}else{
						echo "\n".'<!-- '.count($currentnames).' results found from $qry='.$qry.' -->';
					}
				}
			}
			//get country flags
			if(!empty($instance['show_flags'])){
				$qry=sprintf("SELECT count(DISTINCT `wassup_id`) as top_count, UPPER(`language`) as top_item, max(`timestamp`) AS visit_timestamp FROM $wonline_table WHERE %s AND `language`!='' GROUP BY 2 ORDER BY 1 DESC, 3 DESC",$whereis);
				$currentflags=$wpdb->get_results($qry);
				if($wdebug_mode){
					if(empty($currentflags)|| is_wp_error($currentflags)){
						$currentflags=false;
						echo "\n".'<!-- No results from $qry='.$qry.' -->';
					}else{
						echo "\n".'<!-- '.count($currentflags).' results found from $qry='.$qry.' -->';
					}
				}
			}
		} //end if currentot
	}
	if(!empty($instance['online_total'])){
		if($currenttot==0)$currenttot=1; //at least 1 person is online or widget request wouldn't happen
		// if(is_user_logged_in()&& !empty($instance['online_loggedin'])&& $currentlogged==0)
		//	$currentlogged=1;
	}else{
		$currenttot=0;
	}
	if($currenttot>0 || $currentlogged>0 || $currentcomm>0 || $currentanony>0 || $currentother>0 || !empty($currentnames)|| !empty($currentflags)){
		$ulclass="nobullet";
		if(!empty($instance['ulclass'])) $ulclass.=' '.$instance['ulclass'];
		$html .='
	<ul class="'.$ulclass.'">';
		$currlen=strlen("$currenttot");
		$indent=7;
		if((int)$currenttot>0){
			$html .='
	<li><strong class="online-count online-total">'.$currenttot.'</strong> ';
			if($currenttot==1)$html .=__('Visitor online','wassup');
			else $html .=__('Visitors online','wassup');
			$html .="</li>";
		}
		if((int)$currentlogged>0){
			$indent="";
			if($currlen >=3 && strlen($currentlogged) <3)
				$indent=' style="margin-left:'.((($currlen - strlen($currentlogged))*5)+$currlen).'px;"';
			$html .='
	<li><strong class="online-count online-loggedin"'.$indent.'>'.$currentlogged.'</strong> ';
			if($currentlogged==1)$html .=__('Logged-in user','wassup');
			else $html .=__('Logged-in users','wassup');
			$html .="</li>";
 		}
		if((int)$currentcomm>0){
			$indent="";
			if($currlen >=3 && strlen($currentcomm) <3)
				$indent=' style="margin-left:'.((($currlen - strlen($currentcomm))*5)+$currlen).'px;"';
			$html .='
	<li><strong class="online-count online-comauth"'.$indent.'>'.$currentcomm.'</strong> ';
			if($currentcomm==1)$html .=__('Comment author','wassup');
			else $html .=__('Comment authors','wassup');
			$html .="</li>";
		}
		if((int)$currentanony>0){
			$indent="";
			if($currlen >=3 && strlen($currentanony) <3)
				$indent=' style="margin-left:'.((($currlen - strlen($currentanony))*5)+$currlen).'px;"';
			$html .='
	<li><strong class="online-count online-user"'.$indent.'>'.$currentanony.'</strong> ';
			if($currentanony==1)$html .=__('Regular visitor','wassup');
			else $html .=__('Regular visitors','wassup');
			$html .="</li>";
		}
		if((int)$currentother>0){
			$indent="";
			if($currlen >=3 && strlen($currentother) <3)
				$indent=' style="margin-left:'.((($currlen - strlen($currentother))*5)+$currlen).'px;"';
			$html .='
	<li><strong class="online-count online-spider"'.$indent.'>'.$currentother.'</strong> ';
			if($currentother==1)$html .=__('Other','wassup');
			else $html .=__('Others','wassup');
			$html .="</li>";
		}
		if(!empty($currentnames)){
			$html .= '
	<li>';
			natcasesort($currentnames);
			$html .='
		<p class="online-loggedin">'.implode('&nbsp;&middot;&nbsp;',array_unique($currentnames)).'</p></li>';
		}
		if(!empty($currentflags)){
			$html .='
	<li><p class="wassup-flag">';
			$fc=count($currentflags);
			$i=0;
			foreach ($currentflags as $loc) {
				$i++;
				$flag='/img/flags/'.$loc->top_item.'.png';
				if(is_readable(WASSUPDIR.$flag)){
					$html .= ' <nobr><img src="'.WASSUPURL.$flag.'" class="icon" alt="'.$loc->top_item.'"/>'.$loc->top_count.'</nobr>';
				}else{
					$html .= strtoupper($loc->top_item).'-'.$loc->top_count;
				}
				if($i < $fc)$html .= '&nbsp;&middot;&nbsp;';
			}
			$html .='</p></li>';
		}
		$html .='
	</ul>';
	}
	return "$html";
} //end wassup_widget_get_online_counts

/**
 * Display list of latest top stats items
 *
 * @param string $item,integer(4) ($limit,$chars,$from_date,$show_counts)
 * @return string $html
 */
function wassup_widget_get_topstat($item,$limit,$chars,$from_date,$show_counts=0){
	global $wpdb,$wassup_options,$wdebug_mode;
	$html="";
	if($limit >0){
		//exclude spiders from widget data (spam already excluded in wGetStats)
		$top_results=array();
		if(!function_exists('wGetStats'))include_once(WASSUPDIR ."/lib/main.php");
		$wpurl=strtolower(rtrim(wassupURI::get_wphome()));
		$blogurl=strtolower(rtrim(wassupURI::get_sitehome()));
		$top_condition=" `timestamp`>='".$from_date."' AND `spider`=''";
		//for multisite/network activation
		$multisite_condition="";
		if($wassup_options->network_activated_plugin() && !empty($GLOBALS['current_blog']->blog_id)){
			$multisite_condition = sprintf(" AND `subsite_id`=%d",(int)$GLOBALS['current_blog']->blog_id);
		}
		$top_condition .= $multisite_condition;
		$scol=$item;
		$top_sql="";
		if($item =='articles'){
			$scol="url_wpid";
		}elseif($item =='searches'){
			$scol="search";
			//omit google secure search keywords "(not provided)" in top condition
			$top_condition .=" AND `search`!='_notprovided_'";
		}elseif($item =='requests'){
			$scol="urlrequested";
			//exclude 404 requests, wp-login.php, /wp-admin/wp-includes/wp-content, and robots.txt/sitemap.xml/browserconfig.xml from widget
			if($wpurl == $blogurl){
				$top_condition.=" AND `urlrequested` NOT LIKE '[%' AND `urlrequested` NOT LIKE '/wp-login.php%' AND `urlrequested` NOT LIKE '/wp-admin/%' AND `urlrequested` NOT LIKE '/wp-includes/%' AND `urlrequested` NOT LIKE '/wp-content/%' AND `urlrequested` NOT LIKE '%/robots.txt' AND `urlrequested` NOT LIKE '%/browserconfig.xml' AND `urlrequested` NOT LIKE '%/sitemap.xml'";
			}else{
				$top_condition.=" AND `urlrequested` NOT LIKE '[%' AND `urlrequested` NOT LIKE '%/wp-login.php%' AND `urlrequested` NOT LIKE '%/wp-admin/%' AND `urlrequested` NOT LIKE '%/wp-includes/%' AND `urlrequested` NOT LIKE '%/wp-content/%' AND `urlrequested` NOT LIKE '%/robots.txt' AND `urlrequested` NOT LIKE '%/browserconfig.xml' AND `urlrequested` NOT LIKE '%/sitemap.xml'";
			}
		}elseif($item =='locale'){
			$scol="language";
		}
		$top_sql=wGetStats($scol,$limit,$top_condition,"sql");
		if(!empty($top_sql)) $top_results=$wpdb->get_results($top_sql);
		$ndigits=1;
		if (!empty($top_results) && count($top_results)>0) {
			$ndigits = strlen("{$top_results[0]->top_count}");
			if($ndigits >4)$ndigits=1; //don't pad large#
			$liclass="";
			if(!empty($show_counts))$liclass=' class="stat-count"';
			if($wdebug_mode){
				echo "\n\t".'<!-- '.count($top_results).' results from query '.$top_sql.' -->';
			}
		foreach($top_results as $wtop){
			$top_count='';
			if(!empty($show_counts)){
				$top_count=wPadNum($wtop->top_count,$ndigits);
			}
			$html .='
	<li'.$liclass.'>';
			if($scol=="language"){
				$flag='/img/flags/'.esc_attr($wtop->top_item).'.png';
				if(is_readable(WASSUPDIR.$flag)){
					$flagsrc=WASSUPURL.$flag;
					$html .='<nobr>'.$top_count.'<span class="top-item"><img class="icon" src="'.$flagsrc.'" alt=""/> '.esc_attr($wtop->top_item).'</span></nobr>';
				}else{
					$html .='<nobr>'.$top_count.'<span class="top-item">'.esc_attr($wtop->top_item).'</span></nobr>';
				}
			}elseif($scol=="url_wpid" || $scol=="search"){
				if(!empty($wtop->top_link)) $html .=$top_count.'<span class="top-item"><a href="'.wassupURI::cleanURL($wtop->top_link).'" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</a></span>';
				else $html .=$top_count.'<span class="top-item">'.esc_attr($wtop->top_item).'</span>';
			}elseif($scol=="urlrequested"){
				if(!empty($wtop->top_link)){
					$html .=$top_count.'<span class="top-url"><a href="'.wassupURI::cleanURL($wtop->top_link).'" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</a></span>';
				}else{
					$html .=$top_count.'<span class="top-url" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</span>';
				}
			}elseif($scol=="referrers"){
				if(!empty($wtop->top_link)){
					$favicon_img="";
					$rurl=parse_url($wtop->top_link);
					if(!empty($rurl['host']) && preg_match('/\.[a-z]{2,4}$/',$rurl['host'])>0) $favicon_img='<img src="http://www.google.com/s2/favicons?domain='.$rurl['host'].'" class="favicon"> ';
					$html .=$top_count.'<span class="top-url"><a href="'.wassupURI::cleanURL($wtop->top_link).'" title="'.esc_attr($wtop->top_item).'">'.$favicon_img.esc_attr($wtop->top_item).'</a></span>';
				}else{
					$html .=$top_count.'<span class="top-url" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</span>';
				}
			}elseif($chars >0 && strlen($wtop->top_item)>$chars){
				if(!empty($wtop->top_link))$html .=$top_count.'<span class="top-item"><a href="'.wassupURI::cleanURL($wtop->top_link).'" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</a></span>';
				else $html .=$top_count.'<span class="top-item" title="'.esc_attr($wtop->top_item).'">'.stringShortener($wtop->top_item,$chars).'</span>';
			}elseif(!empty($wtop->top_link)){
				$html .=$top_count.'<span class="top-url"><a href="'.wassupURI::cleanURL($wtop->top_link).'" title="'.esc_attr($wtop->top_item).'">'.esc_attr($wtop->top_item).'</a></span>';
			}else{
				$html .=$top_count.'<span class="top-item">'.esc_attr($wtop->top_item).'</span>';
			}
			//if(empty($show_counts))$html .= '</span>';
			$html .='</li>';
		} //end foreach
		}elseif($wdebug_mode){
			echo "\n".'<!-- No results for '.esc_attr($item).' on query $top_sql='.$top_sql.' found! -->';
		} //end if top_results
	} //end if limit
	return $html;
} //end wassup_widget_get_topstat

/**
 * Return the gettext version of a wassup stats item for widgets' headings
 *
 * @param string $statitem, string $heading
 * @return string $gettext
 */
function wassup_widget_stat_gettext($statitem,$heading=""){
	if(empty($heading)) $heading=__("Top","wassup");
	if($statitem=="articles")$gettext=sprintf(__("%s articles","wassup"),$heading);
	elseif($statitem=="searches")$gettext=sprintf(__("%s searches","wassup"),$heading);
	elseif($statitem=="referrers")$gettext=sprintf(__("%s referrers","wassup"),$heading);
	elseif($statitem=="requests")$gettext=sprintf(__("%s requests","wassup"),$heading);
	elseif($statitem=="browsers")$gettext=sprintf(__("%s browsers","wassup"),$heading);
	elseif($statitem=="os")$gettext=sprintf(__("%s OS","wassup"),$heading);
	elseif($statitem=="locale")$gettext=sprintf(__("%s locale","wassup"),$heading);
	else $gettext=$statitem;
	return $gettext;
}
?>
