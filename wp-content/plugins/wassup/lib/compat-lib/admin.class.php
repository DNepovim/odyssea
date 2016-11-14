<?php
/**
 * Defines admin dashboard widget and chart...with PHP4/backward compatible code
 *
 * @package WassUp Real-time Analytics
 * @subpackage compat/admin.class.php module
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

if (!class_exists('wassup_Dashboard_Widgets')){
/**
 * Static class container for WassUp dashboard widgets functions
 * @since v1.9
 * @author Helene D. - 2014-11-05
 */
class wassup_Dashboard_Widgets{
	function init(){
		global $wp_version,$wassup_options;
		$dashwidget_access=$wassup_options->get_access_capability();
		if(!empty($dashwidget_access)&& current_user_can($dashwidget_access)){
			//load Wassup modules as needed
			if(!class_exists('WassupItems')) require_once(WASSUPDIR."/lib/main.php");
			if(version_compare($wp_version,'2.7','<')){
				add_action('activity_box_end',array(__CLASS__,'dash_chart'));
			}else{
				add_action('admin_head',array(__CLASS__,'add_dash_css'),20);
				if(is_network_admin()){
					wp_add_dashboard_widget('wassup-dashwidget1','Visitors Summary',array(__CLASS__,'dash_widget1'));
				}else{
					add_meta_box('wassup-dashwidget1','Visitors Summary',array(__CLASS__,'dash_widget1'),'dashboard','side','high');
				}
			}
		}
	}
	function remove_dash_widget($widgetid="wassup-dashwidget1"){
		remove_meta_box($widgetid,'dashboard','side');
	}
	function add_dash_css(){
		global $wdebug_mode;

		$vers=WASSUPVERSION;
		if($wdebug_mode)$vers.='b'.rand(0,9999);
		echo "\n";?>
<link rel="stylesheet" href="<?php echo WASSUPURL.'/css/wassup.css?ver='.$vers;?>" type="text/css" /><?php
	}
	/**
	 * print a chart in the dashboard for WP < 2.7
	 */
	function dash_chart(){
		global $wpdb,$wassup_options;
		$wassup_table=$wassup_options->wassup_table;
		$wassupfolder=plugin_basename(WASSUPDIR);
		$chart_type = ($wassup_options->wassup_chart_type >0)? $wassup_options->wassup_chart_type: "2";
		$to_date = current_time("timestamp");
		$ctime = 1;
		$date_from = $to_date - (int)(($ctime*24)*3600);
		$whereis="";
		$Chart = New WassupItems($wassup_table,$date_from,$to_date,$whereis);
		$chart_url="";
		if($Chart->totrecords >1){
			$chart_url = $Chart->TheChart($ctime,"400","125","",$chart_type,"bg,s,efebef|c,lg,90,edffff,0,efebef,0.8","dashboard");
		}?>
	<h3>WassUp <?php _e('Stats','wassup'); ?> <cite><a href="admin.php?page=<?php echo $wassupfolder; ?>"><?php _e('More','wassup'); ?> &raquo;</a></cite></h3>
	<div id="wassup-dashchart" class="placeholder" align="center">
		<img src="<?php echo $chart_url; ?>" alt="WassUp <?php _e('visitor stats chart','wassup'); ?>"/>
	</div>
<?php
	} //end dash_chart

	/**
	 * Output WassUp main dashboard widget
	 */
	function dash_widget1(){
		global $wpdb,$wp_version,$wassup_options,$wdebug_mode;

		$wassup_table=$wassup_options->wassup_table;
		$wassup_tmp_table=$wassup_table."_tmp";
		$chart_type=($wassup_options->wassup_chart_type >0)?$wassup_options->wassup_chart_type:"2";
		$res=((int)$wassup_options->wassup_screen_res-160)/2;
		$to_date=current_time("timestamp");
		$ctime=1;
		$date_from=$to_date - (int)(($ctime*24)*3600);
		$whereis="";
		if(is_multisite() && $wassup_options->network_activated_plugin()){
			if(!is_network_admin() && !empty($GLOBALS['current_blog']->blog_id)) $whereis .=sprintf(" AND `subsite_id`=%d",(int)$GLOBALS['current_blog']->blog_id);
		}
		$Chart=New WassupItems($wassup_table,$date_from,$to_date,$whereis);
		$chart_url="";
		if($Chart->totrecords >1){
			$chart_url=$Chart->TheChart($ctime,$res,"180","",$chart_type,"bg,s,f3f5f5|c,lg,90,edffff,0,f3f5f5,0.8","dashboard");
		}
		$max_char_len=40;
		echo "\n";?>
	<div class="wassup-dashbox"<?php
		 if(version_compare($wp_version,"3.5","<")) echo ' style="margin:-10px;"';
		 elseif(version_compare($wp_version,"3.8","<")) echo ' style="margin:-10px -12px -10px -10px;"';?>>
		<cite><a href="<?php echo admin_url('index.php?page=wassup-stats');?>"><?php _e('More Stats','wassup');?> &raquo;</a></cite><?php
		echo "\n";
		//Show chart...
		if(!empty($chart_url)){?>
		<div class="wassup-dashitem no-bottom-border">
			<p id="wassup-dashchart" class="placeholder" align="center" style="margin:0 auto;padding:0;"><img src="<?php echo$chart_url.'" alt="[img: WassUp '.__('visitor stats chart','wassup').']';?>"/></p>
		</div><?php
			echo "\n";
		}
		//Show online count...
		$currenttot=0;
		if(!empty($wassup_options->wassup_active)){
			//New in v1.9: variable timeframes for online counts: spiders for 1 min, regular visitors for 3 minutes, logged-in users for 10 minutes
			$to_date=current_time('timestamp');
			$from_date=$to_date - 10*60;	//-10 minutes
			$sql=sprintf("SELECT wassup_id, max(timestamp) as max_timestamp, `ip`, urlrequested, `referrer`, searchengine, spider, `username`, comment_author, language, spam FROM $wassup_tmp_table WHERE `timestamp`>'%d' AND (`username`!='' OR `timestamp`>'%d' OR (`timestamp`>'%d' AND `spider`='')) %s GROUP BY wassup_id ORDER BY max_timestamp DESC",$from_date,$to_date - 1*60,$to_date - 3*60,$whereis);
			$qryC=$wpdb->get_results($sql);
			if(!empty($qryC)){
			 	if(is_array($qryC)) $currenttot=count($qryC);
				elseif(is_wp_error($qryC)) $error_msg=" error# ".$qryC->get_error_code().": ".$qryC->get_error_message()."\nSQL=".esc_attr($sql)."\n";
			}
			if($wdebug_mode){
				echo "\n<!-- ";
				if(!empty($error_msg)){
					echo "wassup_Dashboard_Widgets ERROR: ".$error_msg;
				}elseif($currenttot >0){
					echo "&nbsp; &nbsp; qryC=";
					print_r($qryC);
				}
				echo "\n-->";
			}
		}
		if($currenttot > 0){ ?>
		<div class="wassup-dashitem no-top-border">
			<h5><?php echo '<strong>'.$currenttot."</strong>".__("Visitors online","wassup");?></h5><?php
			echo "\n";?>
		</div>
		<div class="wassup-dashitem"><?php
			$Ousername=array();
			$Ocomment_author=array();
			$prev_url="";
			$prev_wassupid="";
			$char_len=$max_char_len;
			$siteurl=wassupURI::get_sitehome();
			$wpurl=wassupURI::get_wphome();
			foreach($qryC as $cv){
				//don't show duplicates
			if(($cv->urlrequested!=$prev_url || $cv->wassup_id!=$prev_wassupid)){
				$prev_url=$cv->urlrequested;
				$prev_wassupid=$cv->wassup_id;
				if ($wassup_options->wassup_time_format == 24) $timed = gmdate("H:i:s", $cv->max_timestamp);
				else $timed = gmdate("h:i:s a", $cv->max_timestamp);
				$ip=wassup_clientIP($cv->ip);
				$referrer="";
				if($cv->referrer !='' && stristr($cv->referrer,$wpurl)!=$cv->referrer && stristr($cv->referrer,$siteurl)!=$cv->referrer){
					if ($cv->searchengine !="")$referrer=wassupURI::se_link($cv->referrer,$char_len,$cv->spam);
					else $referrer=wassupURI::referrer_link($cv->referrer,$cv->urlrequested,$char_len,$cv->spam);
				}
				$requrl=wassupURI::url_link($cv->urlrequested,$char_len,$cv->spam);
				if($cv->username!="" || $cv->comment_author!=""){
				if($cv->username!=""){
					$Ousername[]=esc_attr($cv->username);
					if(!empty($cv->comment_author))$Ocomment_author[]=esc_attr($cv->comment_author);
				}elseif($cv->comment_author!=""){
					$Ocomment_author[]=esc_attr($cv->comment_author);
				}
				}
				//don't show admin requests to users
				if(preg_match('#\/wp\-(admin|includes|content)\/#',$cv->urlrequested)==0 || current_user_can('manage_options')){
					echo "\n";?>
			<p><strong><?php echo esc_attr($timed);?></strong> &middot; <?php echo esc_attr($ip); ?> &rarr; <?php echo $requrl;
				if(!empty($referrer)) echo '<br />'.__("Referrer","wassup").': <span class="widgetref">'.$referrer.'</span>';?></p><?php
				}
			} //end if cv->urlrequested
			} //end foreach qryC
			echo "\n";?>
		</div><?php
			if(count($Ousername)>0){
				natcasesort($Ousername);
				echo "\n";?>
		<div class="wassup-dashitem<?php if(count($Ocomment_author)==0)echo ' no-bottom-border';?>"><p><?php
				echo __('Registered users','wassup').': <span class="loggedin">'.implode('</span> &middot; <span class="loggedin">',array_unique($Ousername)).'</span>';?></p></div><?php
			} 
			if(count($Ocomment_author)>0){
				natcasesort($Ocomment_author);
				echo "\n";?>
		<div class="wassup-dashitem no-bottom-border"><p><?php
				echo __('Comment authors','wassup').': <span class="commentaut">'.implode('</span> &middot; <span class="commentaut">',$Ocomment_author).'</span>';?></p></div><?php
			}
		}elseif(!empty($wassup_options->wassup_active)){ ?>
		<div class="wassup-dashitem no-top-border no-bottom-border">
			<h5><strong>1</strong> <?php _e("Visitor online","wassup");?></h5>
		</div><?php

		}else{ ?>
		<div class="wassup-dashitem no-top-border no-bottom-border">
			<p><?php echo "&nbsp; ".__("No online data!","wassup");?></p>
		</div><?php
		} //end if currentot>0
		echo "\n";?>
		<div class="wassup-dashitem no-top-border no-bottom-border"><span class="wassup-marque"><?php echo __("powered by","wassup").' <a href="http://www.wpwp.org/" title="WassUp - '.__("Real Time Visitors Tracking","wassup").'">WassUp</a>';?></span></div>
	</div><!-- /wassup-dashbox --><?php
		$wdebug_mode=false; //turn off debug after display of widget due to ajax conflict.
	} //end dash_widget1
} //end Class wassup_Dashboard_Widgets
} //end if class_exists
