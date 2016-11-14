<?php
/**
 * Displays Options form for editing WassUp's settings and FAQ
 *
 * @package WassUp Real-time Analytics
 * @subpackage settings.php
 * @author Helene Duncker (http://helenesit.com)
 *
 */
//no direct request for this plugin module
if(!defined('ABSPATH')|| empty($GLOBALS['wp_version'])|| !empty($_SERVER['SCRIPT_FILENAME'])&& realpath(preg_replace('/\\\\/','/',__FILE__))===realpath($_SERVER['SCRIPT_FILENAME'])){
	if(!headers_sent()){header('Location: /?p=404page&err=wassup403');exit;
	}elseif(function_exists('wp_die')){wp_die("Bad Request: ".esc_attr(wp_kses(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI']),array())));exit;
	}else{die("Bad Request: ".htmlspecialchars(preg_replace('/(&#37;|&amp;#37;|%)(?:[01][0-9A-F]|7F)/i','',$_SERVER['REQUEST_URI'])));exit;}
}
/**
 * wassup_optionsView
 *  - form to view and change Wassup's wp_option settings 
 * @param integer
 * @return void
 */
function wassup_optionsView($tab=0) {
	global $wpdb,$wp_version,$current_user,$wassup_options,$wdebug_mode;

	$GMapsAPI_signup="https://developers.google.com/maps/documentation/javascript/get-api-key#key"; //v3 key signup
	$adminemail = get_bloginfo('admin_email');
	$alert_msg = "";
	$alertstyle = 'color:red; background-color:#ffd;';
	$wassup_table=$wassup_options->wassup_table;
	$wassup_meta_table = $wassup_table . "_meta";
	$table_engine = "";
	$table_collation = "";
	//$wp_min_memory=40;	//since WordPress 3.5+
	$wp_min_memory=64;	//40MB caused out of mem. errors in WP 3.8+
	if(!is_object($current_user) || empty($current_user->ID)){
		$user=wp_get_current_user();
	}
	$goodstyle = 'color:green;';
	$disabledstyle = 'color:#999;';
	$disabled='disabled="DISABLED"';
	$checked='checked="CHECKED"';

	if ($wassup_options->wassup_remind_flag == 2) {
		$alert_msg = '<p style="color:red;font-weight:bold;">'.__('ATTENTION! Your WassUp table have reached the maximum value you set, I disabled the alert, you can re-enable it here.','wassup').'</p>';
		$wassup_options->wassup_remind_flag = 0;
		$wassup_options->saveSettings();
	}
	//get current wassup table size
	$data_rows = 0;
	$data_lenght = 0;
	if (wassupDb::table_exists($wassup_table)) {
		$fstatus = wassupDb::table_status($wassup_table);
		if (!empty($fstatus) && is_object($fstatus)) {
			//New in v1.9: db size includes index size
			$data_lenght=$fstatus->Data_length+$fstatus->Index_length;
			$data_rows = (int) $fstatus->Rows;
			if (isset($fstatus->Engine)) {
				$table_engine = $fstatus->Engine;
			} elseif (isset($fstatus->Type)) {
				$table_engine = $fstatus->Type;
			}
			$table_collation = (isset($fstatus->Collation)? $fstatus->Collation: '');
		}
	} else { ?>
		<span style="<?php echo $alertstyle; ?>"><br /><strong><?php echo __('IMPORTANT').': WassUp '.__("table empty or does not exist!","wassup"); ?></strong></span>
<?php	}
	$wwidgets= __('Visitors Online or Top Stats','wassup');
	$wwidgets_link='<a href="'.admin_url("widgets.php").'">'.__('Widgets menu','wassup').'</a>';
?>
	<p><?php echo sprintf(__("You can add a sidebar Widget with some useful statistics information by activating the %s widget from the %s.","wassup"), $wwidgets,$wwidgets_link);?></p>
	<p style="padding:10px 0 10px 0;"><?php _e('Select the options you want for WassUp plugin','wassup'); ?></p><?php
	if(empty($tab) || (!is_numeric($tab) && $tab!="donate" && $tab!="faq")){
		if(isset($_POST['delete_now'])) $tab=3;
		elseif(isset($_POST['submit-options'])) $tab=1;
		elseif(isset($_POST['submit-options2'])) $tab=2;
		elseif(isset($_POST['submit-options3'])) $tab=3;
		elseif(isset($_POST['submit-options4'])) $tab=4;
		else{
			$tab=1;
			if(!empty($_REQUEST['tab'])){
				if (is_numeric($_REQUEST['tab']) && $_REQUEST['tab']>0 && $_REQUEST['tab']<7) $tab = (int)$_REQUEST['tab'];
				elseif($_REQUEST['tab']=="donate" || $_REQUEST['tab']=="faq") $tab=$_REQUEST['tab'];
			}
		}
	}
	if ($wdebug_mode) {
		echo "\n<!-- ";
		echo "\n tab=$tab &nbsp; wassup_table=$wassup_table &nbsp; wassup_meta_table=$wassup_meta_table";
		echo "\n POST values \$_POST:";
		print_r($_POST);
		if(!empty($fstatus)) {
			echo "\n fstatus=";
			print_r($fstatus);
		}
		echo "-->\n";
	}
	//New in v1.9: show uninstall tab for multisite/subdomain sites and for Wordpress 2.x
	$has_uninstall_tab=false;
	$wassup_network_settings=array();
	if(version_compare($wp_version,"3.0","<") || (is_multisite() &&  is_subdomain_install() && !is_main_site())) $has_uninstall_tab=true;
	echo "\n"; ?>
	<form name="wassupsettings" id="wassupsettings" action="" method="post">
	<?php
	//New in v1.9: wp_nonce field added to settings for referer validation and security
	wp_nonce_field('wassupsettings-'.$current_user->ID);
	echo "\n";?>
	<div class="ui-tabs" id="tabcontainer">
		<ul class="ui-tabs-nav">
		<li id="opt-tab1" class="optionstab<?php if($tab=="1")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-1"><span><?php _e("General Setup","wassup");?></span></a></li>
		<li id="opt-tab2" class="optionstab<?php if($tab=="2")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-2"><span><?php _e("Filters & Exclusions","wassup")?></span></a></li>
		<li id="opt-tab3" class="optionstab <?php if($tab=="3")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-3"><span><?php _e("Manage Files & Data","wassup");?></span></a></li><?php
	if($has_uninstall_tab){
		echo "\n";?>
		<li id="opt-tab4" class="optionstab <?php if($tab=="4")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-4"><span><?php _e("Uninstall","wassup");?></span></a></li><?php
	}
	//New in v1.9: new "donate" and "faq" tabs
	echo "\n";?>
		<li id="opt-tab-faq" class="optionstab faqtab<?php if($tab=="faq" || $tab=="5")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-5"><span>F.A.Q.&nbsp;</span></a></li>
		<li id="opt-tab-donate" class="optionstab donatetab<?php if($tab=="donate" || $tab=="6")echo ' ui-tabs-active';?>"><a href="#wassup_opt_frag-6"><span><?php _e("Donate","wassup");?></span></a></li>
		</ul>

	<div id="wassup_opt_frag-1" class="optionspanel<?php if ($tab == "1") echo ' tabselected'; ?>"><br/><?php
	//New in v1.9: multisite options
	$wassup_network_settings=array();
	if(is_multisite() && (is_network_admin() || (is_main_site() && is_super_admin()))){
		$wassup_network_settings=get_site_option('wassup_network_settings');
		echo "\n";?>
		<h2><?php _e('Networkwide Settings','wassup');?></h2>
		<p class="noindent-opt"><?php echo __("Multisite settings that applies to all subsites in the network.","wassup");?></p>
		<input type="hidden" name="_network_settings" value="1"/>
		<h3><?php _e("Network Statistics Recording","wassup");?></h3>
		<p class="description"><?php echo __("Enables Wassup visitor tracking on all subsites in network. Do NOT disable unless upgrading plugin.","wassup");?></p>
		<p><input type="checkbox" name="network_active" value="1" <?php if(!empty($wassup_network_settings['wassup_active'])) echo $checked;?> /> <strong><?php _e('Enable Statistics Recording for network.','wassup');?></strong><br/>
		<span class="opt-note"><?php echo " ".__("Can be overridden on individual subsites to disable statistics recording.","wassup");?></span><?php
		if(!empty($wassup_network_settings['wassup_table'])){?><br/>
		<h3><?php _e("Network Subsites Options","wassup");?></h3>
		<p><input type="checkbox" name="wassup_menu" value="1" <?php if(!empty($wassup_network_settings['wassup_menu'])) echo $checked;?> /> <strong><?php _e("Show Wassup's main admin menu to subsite administrators.","wassup");?></strong><br/>
		<span class="opt-note"><?php echo " ".__("Uncheck to disable Wassup main menu and Wassup-options submenu for all users except Network administrator (super-admin). Dashboard submenu \"Wassup-stats\" and dashboard widget display are unaffected.","wassup");?></span></p><?php
		}?><br/>
		<hr/>
		<h2><?php _e('Site Settings','wassup');?></h2>
		<p class="noindent-opt"><?php echo __("Main site settings/default settings for network subsites.","wassup");?></p><?php
	} //end if multisite
?>
		<h3><?php _e('Statistics Recording','wassup');?></h3>
		<p class="description"><?php echo __("By default, Wassup collects, analyzes, and stores new visitor hits and checks each record for spam and malware activity.","wassup");?></p>
		<p><input type="checkbox" name="wassup_active" value="1" <?php if($wassup_options->wassup_active == 1) echo $checked;?> /> <strong><?php _e('Enable statistics recording','wassup');?></strong><br/>
		<span class="opt-note"><?php
		if (!is_multisite()) echo " ".__("Do NOT disable unless upgrading or troubleshooting plugin problems.","wassup");
		else echo " ".__("Do NOT disable unless troubleshooting plugin problems.","wassup");
		?></span>
		</p>
		<p class="checkbox-indent"><input type="checkbox" name="wassup_spamcheck" value="1" <?php if($wassup_options->wassup_spamcheck == 1) echo $checked;?> /> <strong><?php _e('Enable spam and malware detection on records','wassup');?></strong><br/>
		<span class="opt-note"><?php echo " ".__("For identification only. Does NOT stop attacks nor protect your site.","wassup");?></span>
		</p><br/>
		<h3><?php _e('User Permissions'); ?></h3>
		<p class="description"><?php echo __("Grants readonly access to Wassup-stats dashboard submenu and to the dashboard widget for users with the selected role or higher. Only administrators have access to Wassup main menu and Wassup Options panel to edit settings and delete data.","wassup");?></p>
		<p><strong><?php _e('Set minimum user level that can view WassUp stats','wassup'); ?></strong>:
		<select name="wassup_userlevel">
		<?php $wassup_options->showFieldOptions("wassup_userlevel"); ?>
		</select>
		<?php echo "<nobr>(".__('default administrator','wassup').")</nobr>";?>
		</p><br/>
		<h3><?php _e('Screen resolution','wassup');?></h3>
		<p class="description"><?php echo __("Adjusts chart size and resets the max-width/truncation point of long texts.","wassup");?></span>
		<p class="indent-opt"><strong><?php _e('Your default screen resolution (in pixels)','wassup');?></strong>:
		<select name='wassup_screen_res' style="width:90px;">
		<?php $wassup_options->showFieldOptions("wassup_screen_res");?>
		</select>
		</p><br/>
		<h3><?php _e('Dashboard Widget','wassup'); ?></h3>
		<p><input type="checkbox" name="wassup_dashboard_chart" value="1" <?php if($wassup_options->wassup_dashboard_chart==1) echo $checked; ?> /> <strong><?php _e('Enable widget/small chart in admin dashboard','wassup'); ?></strong>
		</p><br/>
		<h3><?php _e('Spy Visitors Settings','wassup'); ?></h3><?php
		$disabled="";
		$checked="";
		$not_required=" ".__("not required","wassup");
		if ($wassup_options->wassup_geoip_map == 1) {
			//New in v1.9: Api key not required for Google!Maps v3
			$checked= 'checked="CHECKED"';
			if (!empty($wassup_options->wassup_googlemaps_key))$api_key=esc_attr(strip_tags(html_entity_decode($wassup_options->wassup_googlemaps_key)));
			else $api_key = $not_required;
		} else {
			$checked = 'onclick=\'jQuery("#wassup_googlemaps_key").removeAttr("disabled");\'';
			$api_key = $not_required;
			$disabled =' disabled="DISABLED" style="color:#99a;"';
		}
		echo "\n";?>
		<p class="indent-opt"> <input type="checkbox" name="wassup_geoip_map" value="1" <?php echo $checked; ?> />
		<strong><?php _e('Display a GEO IP Map in the spy visitors view','wassup'); ?></strong></p><?php
		echo "\n"; ?>
		<p class="checkbox-indent"><strong>Google Maps API <?php _e("key","wassup"); ?>:</strong> <input type="text" name="wassup_googlemaps_key" id="wassup_googlemaps_key" size="40" value=<?php echo '"'.esc_attr($api_key).'"'.$disabled.' onfocus="this.value=(this.value==\''.$not_required.'\') ? \'\' : this.value;" onblur="this.value=(this.value==\'\') ? \''.$not_required.'\' : this.value;"'; ?> />  - <a href="<?php echo $GMapsAPI_signup; ?>" target="_blank"><?php _e("signup for your key","wassup"); ?></a>
		<br/>&nbsp; <?php  echo __('For usage exceeding 25000 daily map views per domain.','wassup');
		$checked='checked="CHECKED"';
		$disabled='disabled="DISABLED"'; ?>
		</p><br/>

		<h3><?php _e('Visitor Detail Settings','wassup'); ?></h3>
		<p> <strong><?php _e('Show visitor details from the last','wassup'); ?></strong>:
		<select name='wassup_time_period'>
		<?php $wassup_options->showFieldOptions("wassup_time_period"); ?>
		</select>
		</p>
		<p><strong><?php _e('Time format 12/24 Hour','wassup'); ?></strong>:
		&nbsp; 12h <input type="radio" name="wassup_time_format" value="12" <?php if($wassup_options->wassup_time_format == 12) echo $checked; ?> />
		&nbsp; &nbsp; 24h <input type="radio" name="wassup_time_format" value="24" <?php if($wassup_options->wassup_time_format == 24) echo $checked; ?> />
		</p>
		<p> <strong><?php _e('Filter visitor details for','wassup'); ?></strong>: 
		<select name='wassup_default_type'>
		<?php $wassup_options->showFieldOptions("wassup_default_type"); ?>
		</select>
		</p>
		<p class="indent-opt"><input type="checkbox" name="wassup_chart" value="1" <?php if(!empty($wassup_options->wassup_chart)) echo $checked; ?> /> <strong><?php _e('Display line chart in detail view','wassup'); ?></strong></p>
		<p class="checkbox-indent"><strong><?php _e('Line chart type - how many axes?','wassup'); ?></strong> <select name='wassup_chart_type'> <?php $wassup_options->showFieldOptions("wassup_chart_type"); ?> </select></p>
		<p><strong><?php echo __('Set how many minutes wait for automatic page refresh','wassup'); ?></strong>:
		<input type="text" name="wassup_refresh" size="2" value="<?php echo (int)$wassup_options->wassup_refresh;?>" /> <?php _e('minutes','wassup');
		echo ' <nobr>('.__('default 3, 0=no refresh','wassup').')</nobr>';?>
		</p>
		<p> <strong><?php _e('Number of items per page','wassup'); ?></strong>:
		<select name='wassup_default_limit'>
		<?php $wassup_options->showFieldOptions("wassup_default_limit"); ?>
		</select>
		</p><br />
		<h3><?php _e('Top Stats Lists','wassup'); ?></h3>
		<p class="description"><?php echo __("Customize Top stats by selected criteria below.","wassup").' '.__("Stats are in descending order from highest count and known spam and malware attempts are excluded from counts.","wassup");
		//Since v1.8.3: toplimit, top_nospider, toppostid (top articles) options added
		$top_ten = maybe_unserialize($wassup_options->wassup_top10);
		if (!is_array($top_ten)) {	//in case corrupted
			$top_ten = $wassup_options->defaultSettings("top10");
		}
		$show_on_front=get_option('show_on_front');?></p>
		<p><strong> <?php _e("Length size limit of top lists", "wassup");?></strong>: <input type="text" name="toplimit" size="2" value="<?php
		if (empty($top_ten["toplimit"])) echo "10";
		else echo (int)$top_ten['toplimit']; ?>" /> (<?php _e("default 10","wassup"); ?>)
		</p>
		<p class="indent-opt"><strong><?php _e("Choose one or more items to list in Top Stats", "wassup"); ?></strong> (<?php _e("over 5 selections may cause horizontal scrolling","wassup"); ?>):<br />
		<div class="topstats-opt">
		<div class="topstats-col">
		<input type="checkbox" name="topsearch" value="1" <?php if($top_ten['topsearch'] == 1) echo $checked; ?> /> <?php _e("Top Searches", "wassup"); ?><br />
		<input type="checkbox" name="topreferrer" value="1" <?php if($top_ten['topreferrer'] == 1) echo $checked; ?> /> <?php _e("Top Referrers", "wassup"); ?><strong>&sup1;</strong><br />
		<input type="checkbox" name="toppostid" value="1" <?php if(!empty($top_ten['toppostid'])) echo $checked; ?> /> <?php _e("Top Articles", "wassup");if($show_on_front=="page") echo '<strong>&sup2;</strong>';?><br />
		</div>
		<div class="topstats-col">
		<input type="checkbox" name="toprequest" value="1" <?php if($top_ten['toprequest'] == 1) echo $checked; ?> /> <?php _e("Top Requests", "wassup"); ?><br />
		<input type="checkbox" name="topbrowser" value="1" <?php if($top_ten['topbrowser'] == 1) echo $checked; ?> /> <?php _e("Top Browsers", "wassup"); ?> <br />
		<input type="checkbox" name="topos" value="1" <?php if($top_ten['topos'] == 1) echo $checked; ?> /> <?php _e("Top OS", "wassup"); ?> <br />
		</div>
		<div class="topstats-col">
		<input type="checkbox" name="toplocale" value="1" <?php if($top_ten['toplocale'] == 1) echo $checked; ?> /> <?php _e("Top Locales", "wassup"); ?><br />
		<input type="checkbox" name="topvisitor" value="1" <?php if(!empty($top_ten['topvisitor'])) echo $checked; ?> /> <?php _e("Top Visitors", "wassup"); ?><br />
		<br />
		</div>
		</div>
		</p><p style="clear:left;"></p>
		<p class="indent-opt"><strong>&sup1;<?php _e("Exclude the following website domains from Top Referrers","wassup");?></strong> (<?php _e("applies to top stats view and widgets","wassup");?>):<br />
		<span style="padding-left:10px;display:block;clear:left;">
		<textarea name="topreferrer_exclude" rows="2" style="width:66%;"><?php echo esc_attr($top_ten['topreferrer_exclude']); ?></textarea></span>
		<span class="opt-note"><?php  echo __("comma separated value","wassup")." (ex: mydomain2.net, mydomain2.info). ". __("List whole domains only. Wildcards and partial domains will be ignored.","wassup"). " ";
		_e("Don't list your website domain defined in WordPress","wassup"); ?>.</span><br/>
		</p><br/><?php
		echo "\n";
		if($show_on_front=="page"){?>
		<p class="indent-opt"><strong>&sup2;<?php _e("Exclude site front page from Top Articles","wassup");?></strong>: <input type="checkbox" name="top_nofrontpage" value="1" <?php if(!empty($top_ten['top_nofrontpage'])) echo $checked;?>/> (<?php _e("applies to top stats view and widgets","wassup");?>)
		</p><br/><?php
		}else{?>
		<input type="hidden" name="top_nofrontpage" value="0"/><?php
		}
		echo "\n";?>
		<p class="indent-opt"> <input type="checkbox" name="top_nospider" value="1" <?php if(!empty($top_ten['top_nospider'])) echo $checked; ?> />
		<strong> <?php _e("Exclude all spider records from Top Stats", "wassup"); ?></strong>
		</p>
		<br /><br />
		<p class="submit"><input type="submit" name="submit-options" id="submit-options" class="submit-opt button button-left button-primary" value="<?php _e('Save Settings','wassup');?>"  onclick="jQuery('#submit-options').val('Saving...');" />&nbsp;<input type="reset" name="reset" class="reset-opt button button-secondary" value="<?php _e('Reset','wassup');?>" /> - <input type="submit" name="reset-to-default" class="default-opt button button-caution wassup-button" value="<?php _e("Reset to Default", "wassup");?>" /></p>
		<p class="opt-prev-next"><a href="<?php echo admin_url('admin.php?page=wassup-options&tab=2');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p></p><br />
	</div>

	<div id="wassup_opt_frag-2" class="optionspanel<?php if ($tab == "2") echo ' tabselected'; ?>">
		<h3><?php _e('Recording Filters and Exclusions','wassup');?></h3>
		<p class="description"><?php echo __("Use the filter checkboxes and exclusion input fields below to customize Wassup's statistics recording so that only the data that you need for your site analyses are stored.","wassup");?>
		</p><br/>
		<h3><?php echo __("Visitor Type Filters:","wassup"); ?></h3>
		<p style="padding-top:0;"><strong> &nbsp; <?php echo __("Checkbox to enable recording by type of \"visitor\"", "wassup");?></strong><br/>
		<span style="padding-left:25px;padding-top:0;margin-top:0;display:block;clear:left;">
		<input type="checkbox" name="wassup_anonymous" value="1" <?php echo $checked." ".$disabled.' style="'.$disabledstyle.'"';?> /> <?php _e("Record regular visitors","wassup");?><br/>
		<input type="checkbox" name="wassup_loggedin" value="1" <?php if($wassup_options->wassup_loggedin == 1) echo $checked;?> /> <?php _e("Record logged in users", "wassup");?><br />
		<input type="checkbox" name="wassup_admin" value="1" <?php if($wassup_options->wassup_admin == 1) echo $checked;?> /> <?php _e("Record logged in administrators", "wassup");?><br />
		<input type="checkbox" name="wassup_spider" value="1" <?php if($wassup_options->wassup_spider == 1) echo $checked;?> /> <?php _e("Record spiders and bots", "wassup");?><br />
		</span>
		</p>
		<h3><?php echo __("Spam and Malware Filters:","wassup"); ?></h3>
		<p style="padding-top:0;"><strong> &nbsp; <?php echo __('Checkbox to enable recording of each type of "spam"','wassup'); ?></strong><br />
		<span style="padding-left:25px;padding-top:0;margin-top:0;display:block;clear:left;">
		<input type="checkbox" name="wassup_spam" value="1" <?php if($wassup_options->wassup_spam == 1) echo $checked; ?> /> <?php _e('Record Akismet comment spam attempts','wassup'); ?> (<?php _e('check if an IP has previous comments as spam','wassup'); ?>)<br />
		<input type="checkbox" name="wassup_refspam" value="1" <?php if($wassup_options->wassup_refspam == 1) echo $checked; ?> /> <?php _e('Record referrer spam attempts','wassup'); ?><br />
		<input type="checkbox" name="wassup_hack" value="1" <?php if($wassup_options->wassup_hack == 1) echo $checked; ?> /> <?php _e("Record admin break-in/hacker attempts", "wassup") ?><br />
		<input type="checkbox" name="wassup_attack" value="1" <?php if($wassup_options->wassup_attack == 1) echo $checked; ?> /> <?php _e("Record attack/exploit attempts (libwww-perl agent)", "wassup") ?><br />
		</span>
		</p><br />
		<h3><?php _e('Recording Exceptions','wassup');?></h3>
		<p class="description"><?php _e("You can exclude a single visitor (by IP, hostname or username) or you can exclude a specific URL request from being stored in WassUp records.","wassup");
		echo " ".__("Note that recording exceptions lower overall statistics counts and excessive exclusions can affect page load speed on slow host servers.","wassup");?>
		</p>
		<h3 class="indent-opt"><?php echo __("Exclude by IP","wassup");?></h3>
		<p style="padding-top:0;padding-bottom:0;"><strong><?php echo __('Enter source IPs to omit from recording','wassup');?></strong>:
		<br /><span style="padding-left:10px;display:block;clear:left;">
		<textarea name="wassup_exclude" rows="2" style="width:60%;"><?php echo esc_url($wassup_options->wassup_exclude);?></textarea></span>
		<span class="opt-note"><?php echo __("comma separated value (ex: 127.0.0.1, 10.0.0.1, etc...).","wassup")." ".__("A single wildcard (*) can be placed after the last '.' or '::' position for range exclusions (ex: 10.10.100.*, 192.168.*).","wassup");?></span>
		</p><br/>
		<h3 class="indent-opt"><?php echo __("Exclude by Hostname","wassup");?></h3>
		<p style="padding-top:0;padding-bottom:0;"><strong><?php echo __('Enter source hostnames to omit from recording','wassup');?></strong>:
		<br /><span style="padding-left:10px;display:block;clear:left;">
		<textarea name="wassup_exclude_host" rows="2" style="width:60%;"><?php echo esc_attr($wassup_options->wassup_exclude_host);?></textarea></span>
		<span class="opt-note"><?php echo __("comma separated value (ex: host1.domain.com, host2.domain.net, etc...).", "wassup")." ".__("A single wildcard (*) can be placed before the first '.' for domain network exclusions (ex: *.spamdomain.com, *.hackers.malware.net).","wassup");?></span>
		</p><br/>
		<h3 class="indent-opt"><?php echo __("Exclude by Username","wassup");?></h3>
		<p style="padding-top:0;"><strong><?php echo __('Enter usernames to omit from recording','wassup');?></strong>:
		<br /><span style="padding-left:10px;display:block;clear:left;">
		<textarea name="wassup_exclude_user" rows="2" style="width:60%;"><?php echo esc_attr($wassup_options->wassup_exclude_user);?></textarea></span>
		<span class="opt-note"><?php _e("comma separated value, enter a registered user's login name (ex: bobmarley, enyabrennan, etc.)", "wassup");?></span>
		</p><br/>
		<h3 class="indent-opt"><?php echo __("Exclude by URL request","wassup");?></h3>
		<p style="padding-top:0;"><strong><?php echo __('Enter URLs of page/post/feed to omit from recording','wassup');?></strong>:
		<br /><span style="padding-left:10px;display:block;clear:left;">
		<textarea name="wassup_exclude_url" rows="2" style="width:60%;"><?php echo esc_url($wassup_options->wassup_exclude_url);?></textarea></span>
		<span class="opt-note"><?php _e("comma separated value, don't enter entire url, only the last path or some word to exclude (ex: /category/wordpress, 2007, etc...)", "wassup");?></span>
		</p><br />
		<p class="submit"><input type="submit" name="submit-options2" id="submit-options2" class="submit-opt button button-left button-primary" value="<?php _e('Save Settings','wassup');?>" onclick="jQuery('#submit-options2').val('Saving...');" />&nbsp;<input type="reset" name="reset" class="reset-opt button button-secondary" value="<?php _e('Reset','wassup');?>" /> - <input type="submit" name="reset-to-default" class="default-opt button button-caution wassup-button" value="<?php _e("Reset to Default", "wassup");?>" /></p>
		<p class="opt-prev-next"><a href="<?php echo admin_url('admin.php?page=wassup-options&tab=1');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php echo admin_url('admin.php?page=wassup-options&tab=3');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br />
	</div>

	<div id="wassup_opt_frag-3" class="optionspanel<?php if ($tab == "3") echo ' tabselected'; ?>">
		<h3><?php _e("Table Management Options","wassup");?></h3>
<?php		//New in v1.9: include index size in table size
		$tusage=($data_lenght/1024/1024);
		if ($wassup_options->is_USAdate())$tusagef=number_format($tusage, 1);
		else $tusagef=number_format($tusage,2,","," ");
		$alertmb=(int)$wassup_options->wassup_remind_mb;
		if(empty($alertmb)) $alertmb=100;?>
		<h3 class="indent-opt"><?php _e('Select actions for table growth','wassup'); ?>:</h3>
		<p class="description"><?php _e("WassUp table grows very fast, especially if your site is frequently visited. I recommend you delete old records sometimes.","wassup");
		echo " ".__('You can delete all Wassup records now (Empty Table), you can set an automatic delete option to delete selected old records daily, and you can manually delete selected old records once (Delete NOW).','wassup');
		echo " ".__("If you haven't database space problems, you can leave the table as is.","wassup"); ?></p>
		<p class="indent-opt"><?php echo __('Current WassUp table usage is','wassup').': <strong>';
		if((int)$tusage >= $alertmb)echo '<span style="'.$alertstyle.'">'.$tusagef.'</span>';
		else echo $tusagef;
		echo '</strong> Mb ('.$data_rows.' '.__('records','wassup').')';?></p>
		<?php print $alert_msg; ?>
		<p class="indent-opt"><input type="checkbox" name="wassup_remind_flag" value="1" <?php if($wassup_options->wassup_remind_flag==1) echo $checked;?>>
		<strong><?php _e('Alert me','wassup'); ?></strong> (<?php _e('email to','wassup'); ?>: <strong><?php print $adminemail; ?></strong>) <?php _e('when table reaches','wassup'); ?> <input type="text" name="wassup_remind_mb" size="3" value="<?php echo (int)$wassup_options->wassup_remind_mb; ?>"> Mb</p><?php
		//New in v1.9: more delete filters for auto delete
?>
		<h3 class="indent-opt"><?php _e("Delete old records","wassup");?>:</h3><?php
		$exporturl=wp_nonce_url(admin_url('admin.php?page=wassup-options&tab=3&export=1'),'wassupexport-'.$current_user->ID);?>
		<p class="indent-opt description"><?php
		echo sprintf(__("Before deleting, you can save Wassup data in SQL 'export' format onto your local computer by clicking %s below.","wassup"), '<a class="export-wassup" href="'.$exporturl.'">'.__('export table','wassup').'</a>');
		echo " ".__("Reload this screen after export to view export messages.", "wassup");?></p>
		<p> &nbsp;<label for="do_delete_auto"><input type="checkbox" name="do_delete_auto" id="do_delete_auto" value="1" <?php if ($wassup_options->delete_auto!="never") echo $checked;?>/> <strong><?php _e("Automatically delete","wassup");?></strong>: </label>
		<select name="delete_filter"><?php $wassup_options->showFieldOptions("delete_filter"); ?></select> 
		<nobr><?php _e("records older than", "wassup"); ?>
		<select name="delete_auto"><?php $wassup_options->showFieldOptions("delete_auto"); ?></select> &nbsp;<?php _e("daily","wassup"); ?></nobr>
		</p>
		<p> &nbsp;<label for="do_delete_manual"><input type="checkbox" name="do_delete_manual" id="do_delete_manual" value="1" /> <strong><?php _e("Manually delete","wassup");?></strong>:</label>
		<select name="delete_filter_manual"><?php $wassup_options->showFieldOptions("delete_filter","all");?></select>
		<nobr><?php _e("records older than", "wassup"); ?>
		<select name="delete_manual"><?php $wassup_options->showFieldOptions("delete_auto","never");?></select>&nbsp; <?php _e("once","wassup");?></nobr>
		</p><?php
		//New in v1.9: Delete by record ID# - for use with export
		$last_export_id=wassupDb::get_wassupmeta($wassup_table,'_export_recid-'.$current_user->ID);
		if (empty($last_export_id) || !is_numeric($last_export_id))
			$last_export_id=0;?>
		<p> &nbsp;<label for="do_delete_recid"><input type="checkbox" name="do_delete_recid" id="do_delete_recid" value="1" /> <strong><?php _e("Delete all records up to record ID#","wassup");?></strong>:</label>
		<input type="text" name="delete_recid" id="delete_recid" value="<?php if (!empty($_POST['delete_recid']) && is_numeric($_POST['delete_recid'])) echo $_POST['delete_recid']; else echo '0';?>" /> <nobr>(<?php echo __("Last exported record ID#:","wassup")." ".$last_export_id;?>)</nobr>
		</p>
		<p class="indent-opt"> &nbsp;<label for="do_delete_empty"><input type="checkbox" name="do_delete_empty" id="do_delete_empty" value="1"/> <strong><?php _e('Empty table','wassup');?></strong></label>
 (<a class="export-wassup" href="<?php echo $exporturl;?>"><?php _e('export table in SQL format','wassup');?></a>)
		</p>
		<p style="margin-top:20px;">
		<input type="button" name="delete_now" class="submit-opt button button-danger wassup-hot-button" value="<?php _e('Delete NOW','wassup'); ?>" onclick="submit();"/><br/><span>&nbsp;<nobr><?php _e("Action is NOT undoable!", "wassup");?></nobr></span>
		</p>
		<br/>
		<h3><?php _e("Table Optimization","wassup");?>:</h3>
		<input type="hidden" name="wassup_dbengine" value="<?php echo $table_engine;?>"/>
		<p class="indent-opt description"><?php
		//New in v1.9: checkbox to turn off automatic optimization plus date content is now used for optimization schedule
		$msg="";?><span id="info-optimize" class="opt-info"><?php
		echo __("By default, WassUp tables are automatically optimized weekly and after each bulk deletion. This helps keep WassUp running fast, but it can sometimes cause slowdowns especially when there is a corrupt record in the table.","wassup")." ";
		echo __("You can cancel automatic optimization by unchecking the box below.","wassup");?></span> <?php
		//check if table is optimizable...some innodb is not
		$is_optimizable_table=true;
		$tengine=strtolower($table_engine);
		if($tengine !="myisam" && $tengine !="archive"){
			$is_optimizable_table=wassupDb::is_optimizable_table($wassup_table);
		}
		if(empty($wassup_options->wassup_optimize) && !$is_optimizable_table) echo '<em>'.__("Your table engine does NOT support the \"optimize\" command.","wassup").'</em>'."\n";?>
		</p>
		<p><label for="wassup_optimize_on">&nbsp; <input type="checkbox" name="wassup_optimize_on" value="1" <?php 
		if(!empty($wassup_options->wassup_optimize)){
			 echo $checked;
		}elseif(!$is_optimizable_table){
			echo " $disabled";
		}?>/> <strong><?php _e("Enable automatic table optimization","wassup");?></strong></label><br/><?php 
		$optimize_schedule="";
		if (empty($wassup_options->wassup_optimize)){
			$optimize_schedule="never";
		}else{
			$timenow=((int)(current_time('timestamp')/100))*100;
			$hours_left=((int)$wassup_options->wassup_optimize - $timenow)/3600;
			if($hours_left < -24) {
				$optimize_schedule=__("is overdue","wassup");
			}elseif ($hours_left < 24 ){
				$optimize_schedule=__("today","wassup");
			} else {
				$days_left=(int)($hours_left/24);
				if ($days_left < 7) $optimize_schedule=sprintf(__("%d days","wassup"),(int)$days_left);
				else $optimize_schedule=__("1 week","wassup");
			}
			echo "\n".'<!-- today='.$timenow.' &nbsp;optimize='.$wassup_options->wassup_optimize.' &nbsp; optimize-today(in hours)='.$hours_left.' -->';
		}?>
		<span class="opt-note"><?php
		if($optimize_schedule != "never"){
			echo " &nbsp; ".sprintf(__("Next scheduled optimization is: %s (approximately)","wassup"), '<strong>'.$optimize_schedule.'</strong>');
		}else{
			echo " &nbsp; ".sprintf(__("Next scheduled optimization is: %s","wassup"), '<strong>'.__("never","wassup").'</strong>');
		}?></span>
		</p>
		<br/>
		<h3><?php _e('Data Storage Methods','wassup');?></h3><?php
		/* cache table is no longer optional (required)
		<h3>Cache storage option</h3>
		<p><input type="checkbox" name="wassup_cache" value="1" <?php
		if ($wassup_options->wassup_cache == 1 ) {
			echo $checked;
		}elseif ($sql_meta_table_name!=$wassup_meta_table) {
			echo $disabled;
		}?>/> 
		<strong><?php echo __('Enable cache for storing some remote API data locally in WassUp table','wassup'); ?></strong><br/>
		<span class="opt-note"><?php _e('Reduces the number of requests to remote API servers and improves WassUp page load speed.','wassup'); ?></span>
		</p><br/>
		*/
		$msg="";
		echo "\n";?>
		<h3 class="indent-opt">MySQL <?php echo __("Delayed Insert","wassup");?>:</h3>
		<p class="description"><span id="info-delayedinsert" class="opt-info"><?php 
		echo __("When possible, WassUp uses the \"Delayed insert\" method of saving records in MySQL to store new visitor records. This method helps keep Wassup running fast on high-volume sites and during volume spikes on all sites. However, it can be inefficient on low-volume sites and sometimes host administrators disable it on shared servers.","wassup")." ";
		echo __("You can turn off \"delayed insert\" by unchecking the box below.","wassup");?></span> <?php
		$delayed_style="";
		if(strstr($tengine,"isam")===false && strstr($tengine,"archive")===false){
			if(empty($wassup_options->delayed_insert)){
				echo '<em>'.__("This method is unavailable for your storage engine type.","wassup").'</em>';
				$delayed_style= ' '.$disabled.' style="background-color:#ddd;'.$disabledstyle.'"';
			}else{
				$delayed_style=' style="'.$alertstyle.'"';
			}
		}else{
			$delayed_queue_size=wassupDb::get_db_setting("delayed_queue_size");
			$max_delayed_threads=wassupDb::get_db_setting("max_delayed_threads");
			if(!is_numeric($delayed_queue_size) || (int)$delayed_queue_size==0 || (int)$max_delayed_threads==0){
				if(empty($wassup_options->delayed_insert)){
					echo '<em>'.__("This method is disabled on your host server.","wassup").'</em>';
					$delayed_style= ' '.$disabled.' style="background-color:#ddd;'.$disabledstyle.'"';
				}else{
					$delayed_style=' style="'.$alertstyle.'"';
				}
			}
		}?></p>
		<p class="indent-opt"><label for="delayed_insert">&nbsp; <input type="checkbox" name="delayed_insert" value="1" <?php if(!empty($wassup_options->delayed_insert))echo $checked;?><?php echo $delayed_style;?>/> <strong><?php _e("Store new visitor records with \"delayed insert\"","wassup");?></strong></label>
		</p>
		<br/>
		<p class="submit"><input type="submit" name="submit-options3" id="submit-options3" class="submit-opt button button-left button-primary wassup-button" value="<?php _e('Save Settings','wassup');?>" onclick="jQuery('#submit-options3').val('Saving...');" />&nbsp;<input type="reset" name="reset" class="reset-opt button" value="<?php _e('Reset','wassup');?>" /> - <input type="submit" name="reset-to-default" class="default-opt button button-caution" value="<?php _e("Reset to Default", "wassup");?>" /></p>
		<br/>
		<div id="info-sysinfo" class="description">
		<hr/>
		<h3><?php _e("Server Settings and Memory Resources","wassup"); ?></h3>
		<p style="color:#555; margin-top:0; padding-top:0;"><?php echo sprintf(__('For information only. Some values may be adjustable in startup files: %s','wassup'),__('"wp_config.php", "php.ini" and "my.ini"','wassup'));?>.</p>
		<p class="sys-settings"><strong>WassUp <?php _e('Version'); ?></strong>: <?php echo WASSUPVERSION; ?></p>
		<ul class="varlist">
		<li><strong>WassUp <?php _e('Table name','wassup');?></strong>: <?php echo $wassup_options->wassup_table;?></li>
		<li><strong>WassUp <?php _e('Table Charset/collation','wassup');?></strong>: <?php
		if (!empty($table_collation)) echo $table_collation;
		else _e("unknown","wassup");?></li><?php echo "\n";
		if (!empty($table_engine)) {?>
		<li><strong>WassUp <?php _e('Table engine','wassup');?></strong>: <?php echo $table_engine;
		}?></li>
		<li><strong>Wassup <?php _e('Upgrade date','wassup');?></strong>: <?php if(!empty($wassup_options->wassup_upgraded) && is_numeric($wassup_options->wassup_upgraded)) echo date("Y-m-d H:i:s",$wassup_options->wassup_upgraded);else _e("unknown","wassup");?></li>
		</ul>
		<p class="sys-settings"><strong>WordPress <?php _e('Version','wassup'); ?></strong>: <?php echo $wp_version; ?></p>
		<ul class="varlist"><?php
		//New in v1.9: Show when multisite network is enabled
		$is_multisite=false;
		if (function_exists('is_multisite')) {
			echo "\n"; ?>
		<li><strong>WordPress <?php _e('Multisite network','wassup');?></strong>:<?php
			if (is_multisite()) {
				$is_multisite=true;
				echo ' '.__("on","wassup");
			} else {
				echo ' '.__("off","wassup");?></li><?php
			}
			echo "\n";
		} ?>
		<li><strong>WordPress <?php _e('Character set','wassup'); ?></strong>: <?php echo get_option('blog_charset'); ?></li>
		<li><strong>WordPress <?php _e('Language','wassup'); ?></strong>: <?php echo get_bloginfo('language'); ?></li>
		<li><strong>WordPress Cache</strong>: <?php 
		if (!defined('WP_CACHE') || WP_CACHE===false || trim(WP_CACHE)==="") {
			echo __("not set","wassup");
		} else {
			echo ' <span style="'.$alertstyle.'">';
			if (WP_CACHE === true) echo __("on","wassup");
			else echo "WP_CACHE";
			echo '</span>';
		}
		?></li>
		<li><strong>WordPress <?php _e('Memory Allocation','wassup');?></strong>: <?php
		//New in v1.9: display wordpress memory size
		$memory_limit=ini_get('memory_limit');
		if (defined('WP_MEMORY_LIMIT'))
			$wp_memory=WP_MEMORY_LIMIT;
		else
			$wp_memory=$memory_limit;
		$mem=0;
		if (preg_match('/^(\d+)(\s?\w)?/',$wp_memory,$match)>0) {
			$mem = (int)$match[1]; 
			if (!empty($match[2]) && strtolower($match[2])=='g')
				$mem = (int)$match[1]*1024;
			if ($mem >= $wp_min_memory) {
				echo $mem . 'M';
			}elseif($mem < 40){
				if(version_compare($wp_version,"3.5",">=")|| $mem < 32)
				 	echo '<span style="'.$alertstyle.'">'.$mem.'M</span>';
				else echo $mem . 'M';
			}elseif ($mem < 64 && (version_compare($wp_version,"3.8",">=")|| $is_multisite)){
				echo '<span style="'.$alertstyle.'">'.$mem.'M</span>';
			}else{
				echo $mem . 'M';
			}
		} else {
			echo $wp_memory;
		} ; ?></li>
		<li><strong>WordPress <?php 
		$WPtimezone = get_option('timezone_string');
		if (!empty($WPtimezone))echo __('Timezone');
		else echo __('Time Offset','wassup');?></strong>: <?php
		if (!empty($WPtimezone)) {
			echo $WPtimezone;
			$wpoffset = (current_time('timestamp') - time())/3600;
		}else{
			$wpoffset = get_option("gmt_offset");
		}
		if ($wpoffset !== false && $wpoffset != "") {
			echo ' UTC ';
			if ((int)$wpoffset >= 0) { echo '+'.$wpoffset; }
			else { echo $wpoffset; }
		}
		echo ' '.__('hours').' ('.gmdate(get_option('time_format'),(time()+($wpoffset*3600))).')'; ?></li>
		<li><strong>WordPress <?php _e("Host Timezone","wassup");?></strong>: <?php
		$host_timezone = $wassup_options->getHostTimezone(true);
		if (!empty($host_timezone)) {
			if (is_array($host_timezone)) {
				echo $host_timezone[0]. " (UTC $host_timezone[1])";
			} else {
				echo $host_timezone;
			}
		}else{
			echo __("unknown");
		}?></li>
		<li><strong>WordPress <?php _e('Host Server','wassup'); ?></strong>: <?php
		$sys_server = "";
		if (!empty($_SERVER['SERVER_SOFTWARE'])) {
			$sys_server = $_SERVER['SERVER_SOFTWARE'];
		}
		if (empty($sys_server) || $sys_server == "Apache") {
			if (defined('PHP_OS') && PHP_OS != 'Apache') {
				$sys_server = PHP_OS;
			} else {
				$sys_server = php_uname();
			}
			if ((empty($sys_server) || $sys_server == "Apache") && function_exists('apache_get_version')) { 
				$sys_server = apache_get_version();
			}
		}
		if (!empty($sys_server)) echo $sys_server;
		else _e("unknown","wassup");
		?></li>
		<li><strong>WordPress <?php _e('Browser Client','wassup'); ?></strong>: <?php
		echo " <!-- ";
		if(!class_exists('UADetector'))
			include_once(WASSUPDIR."/lib/uadetector.class.php");
		$browser = new UADetector;
		echo " -->";
		if (!empty($browser->name) && $browser->agenttype == "B") {
			echo $browser->name." ".$browser->version;
			if ($browser->is_mobile) echo " on ".$browser->os;
		} else _e("unknown","wassup");
		?></li>
		</ul>
		<p class="sys-settings"><strong>PHP <?php _e('Version'); ?></strong>: <?php echo PHP_VERSION; ?></p>
			<ul class="varlist"><?php
		//New in v1.9: 'safe_mode' deprecated in PHP 5.3 and removed in 5.4
		$safe_mode="off";
		if (version_compare(PHP_VERSION, '5.3', '<')) { 
			echo "\n"; ?>
		<li><strong>PHP <?php _e("Safe Mode", "wassup"); ?></strong>: <?php
			$safe_mode= strtolower(ini_get("safe_mode"));
			if ($safe_mode == "on") echo __("on","wassup");
			else echo __("off","wassup");
		?></li><?php
		} ?>
		<li><strong>PHP <?php _e("File Open Restrictions", "wassup"); ?></strong> (open_basedir): <?php
			$open_basedir=ini_get('open_basedir');
			if (empty($open_basedir))
				echo __("off","wassup");
			else
				echo __("on","wassup") . '<!-- '.$open_basedir.' -->';
		?></li>
		<li><strong>PHP <?php _e("URL File Open", "wassup"); ?></strong> (allow_url_fopen): <?php
			$allow_url_fopen=ini_get('allow_url_fopen');
			if ($allow_url_fopen) _e("on", "wassup");
			else _e("off","wassup");
		?></li>
		<li><strong>PHP <?php _e("Disabled functions", "wassup");?></strong>: <?php
		//New in v1.9: list of disabled PHP functions
		if ($safe_mode != "on") {
			$disabled_funcs=ini_get('disable_functions');
			if (!empty($disabled_funcs)) {
				$darray=explode(',',$disabled_funcs);
				array_walk($darray,'trim');
				sort($darray);
				if ($darray[0]=='') array_shift($darray);
				$disabled_funcs=preg_replace('/(^|[ ,])(error_reporting|ini_get|ini_set|set_time_limit|shell_exec)([ ,]|$)/','\1<span style="'.$alertstyle.'">\2</span>\3',implode(', ', $darray));
				echo '<br/><span style="display:block; padding-left:20px;">'.$disabled_funcs.'</span>';
			}
			else _e("none","wassup");?></li><?php
		}
		echo "\n"; ?>
		<li><strong>PHP <?php _e("Memory Allocation","wassup"); ?></strong>: <?php
			$mem=0;
			$memory_use=0;
			if (function_exists('memory_get_usage')) {
				$memory_use=round(memory_get_usage()/1024/1024,2);
			}
			//$memory_limit = ini_get('memory_limit'); //set for WP memory alloc above
			if (preg_match('/^(\d+){1,4}(\w?)/',$memory_limit,$matches) > 0) {
				$mem=(int)$matches[1];
				if ( $mem < 128 && $matches[2] == "M") echo '<span style="'.$alertstyle.'">'.$memory_limit."</span>";
				else echo $memory_limit;
			} else {
				$memory_limit=0; _e("unknown","wassup");
			}
		?></li>
		<li><strong>PHP <?php _e("Memory Usage","wassup"); ?></strong>: <?php
			if (!empty($mem) && ($mem-$memory_use) < 2)
				echo '<span style="'.$alertstyle.'">'.$memory_use."M</span>";
			elseif ($memory_use >0)
				echo $memory_use."M";
			else _e("unknown","wassup");
		?></li>
	   	<li><strong>PHP <?php _e("Script Timeout Limit","wassup"); ?></strong>: <?php
			$max_execute = ini_get("max_execution_time");
			if (!empty($max_execute)) { echo $max_execute." ".__("seconds","wassup"); }
			else { _e("unknown","wassup"); }
		?></li>
	   	<li><strong>PHP <?php _e("Browser Capabilities File","wassup"); ?></strong> (browscap): <?php	
			$browscap=ini_get("browscap");
			if($browscap=="") echo __("not set","wassup");
			else echo basename($browscap);
		?></li>
	   	<li><strong>PHP Curl</strong>: <?php	
			if (!function_exists('curl_init')) { _e("not installed","wassup"); } 
			else { _e("installed","wassup"); }
		?></li>
		<li><strong>PHP <?php
		//different from Host server TZ since Wordpress 2.8.3+
			$php_offset = (int)date('Z')/(60*60);
			if (version_compare(PHP_VERSION, '5.1', '>=')) {
				$php_timezone = date('e'); //PHP 5.1+
			} else {
				$php_timezone = date('T');
			}
			if (!empty($php_timezone) && $php_timezone != "UTC") {
				_e('Timezone'); ?></strong>: <?php
				echo "$php_timezone ";
			} else {
				_e("Time Offset","wassup"); ?></strong>: <?php
			}
			if ($php_offset < 0) {
				echo  "UTC $php_offset ".__('hours');
			} else {
				echo  "UTC +$php_offset ".__('hours');
			}
			if (!empty($WPtimezone)&& version_compare($wp_version,'2.8.3','>=')) {
				echo ' <small> ('.__("as modified in Wordpress","wassup").')</small>';
			}?></li>
		</ul><?php
		//###MySQL server settings
		$sql_version = $wpdb->get_var("SELECT version() as version");
		if (!empty($sql_version) && version_compare($sql_version, '4.1', '>=')) {
			$sql_conf = @$wpdb->get_results("SELECT @@max_user_connections AS max_connections, @@global.time_zone AS tzglobal, @@session.time_zone AS tzsession, @@session.collation_connection AS char_collation, @@session.wait_timeout AS wait_timeout, @@global.connect_timeout AS connect_timeout, @@global.key_buffer_size as index_buffer, @@global.innodb_buffer_pool_size AS innodb_buffer_size, @@session.read_buffer_size AS read_buffer, @@have_query_cache AS have_query_cache, @@global.query_cache_size AS query_cache_size, @@global.query_cache_type AS query_cache_type, @@global.query_cache_limit AS query_cache_limit, @@global.delayed_queue_size AS delayed_queue_size, @@global.delayed_insert_timeout AS delayed_insert_timeout, @@global.max_delayed_threads AS max_delayed_threads, @@session.storage_engine AS storage_engine");
		}
		if (!empty($sql_conf) && is_array($sql_conf)) { 
			$sql_max_connections= isset($sql_conf[0]->max_connections)? (int)$sql_conf[0]->max_connections : 0;
			$sql_tzglobal = isset($sql_conf[0]->tzglobal)? $sql_conf[0]->tzglobal : "";
			$sql_timezone = isset($sql_conf[0]->tzsession)? $sql_conf[0]->tzsession : $sql_tzglobal;
			$sql_collation = isset($sql_conf[0]->char_collation)? $sql_conf[0]->char_collation : "";
			$sql_wait_timeout = isset($sql_conf[0]->wait_timeout)? $sql_conf[0]->wait_timeout : "";
			$sql_connect_timeout = isset($sql_conf[0]->connect_timeout)? $sql_conf[0]->connect_timeout : "";
			$sql_indexbuffer = isset($sql_conf[0]->index_buffer)? $sql_conf[0]->index_buffer : "";
			$sql_buffersize = isset($sql_conf[0]->innodb_buffer_size)? $sql_conf[0]->innodb_buffer_size : "";
			$sql_readbuffer = isset($sql_conf[0]->read_buffer)? $sql_conf[0]->read_buffer : "";
			$sql_query_cache_enabled = isset($sql_conf[0]->have_query_cache)? strtolower($sql_conf[0]->have_query_cache) : "off";
			$sql_query_cache = isset($sql_conf[0]->query_cache_size)? $sql_conf[0]->query_cache_size : "";
			$sql_cache_type = isset($sql_conf[0]->query_cache_type)? strtolower($sql_conf[0]->query_cache_type) : "";
			$sql_cache_limit = isset($sql_conf[0]->query_cache_limit)? $sql_conf[0]->query_cache_limit : "";
			$sql_delayed_queue = isset($sql_conf[0]->delayed_queue_size)? $sql_conf[0]->delayed_queue_size : "";
			$sql_delayed_timeout = isset($sql_conf[0]->delayed_insert_timeout)? $sql_conf[0]->delayed_insert_timeout : "";
			$sql_delayed_threads = isset($sql_conf[0]->max_delayed_threads)? $sql_conf[0]->max_delayed_threads : "";
			$sql_engine = isset($sql_conf[0]->storage_engine)? $sql_conf[0]->storage_engine : "";
			if ($wdebug_mode) {
				echo "\n<!--  MySQL variables \$sql_conf:";
				print_r($sql_conf);
				echo "-->\n";
			}
		} else {
			//for old MySQL versions (pre 4.1)
			$sql_vars = $wpdb->get_results("SHOW VARIABLES");
			foreach ($sql_vars AS $var) {
				if ($var->Variable_name == "max_user_connections") {
					$sql_max_connections= (int)$sql_conf[0]->max_connections;
				} elseif ($var->Variable_name == "timezone") {
					$sql_timezone = $var->Value;
				} elseif ($var->Variable_name == "time_zone") {
					$sql_timezone = $var->Value;
				} elseif ($var->Variable_name == "connect_timeout") {
					$sql_connect_timeout = $var->Value;
				} elseif ($var->Variable_name == "wait_timeout") {
					$sql_wait_timeout = $var->Value;
				} elseif ($var->Variable_name == "key_buffer_size") {
					$sql_indexbuffer = $var->Value;
				} elseif ($var->Variable_name == "innodb_buffer_pool_size") {
					$sql_buffersize = $var->Value;
				} elseif ($var->Variable_name == "read_buffer_size") {
					$sql_readbuffer = $var->Value;
				} elseif ($var->Variable_name == "have_query_cache") {
					$sql_query_cache_enabled = strtolower($var->Value);
				} elseif ($var->Variable_name == "query_cache_size") {
					$sql_query_cache = $var->Value;
				} elseif ($var->Variable_name == "query_cache_type") {
					$sql_cache_type = strtolower($var->Value);
				} elseif ($var->Variable_name == "query_cache_limit") {
					$sql_cache_limit = $var->Value;
				} elseif ($var->Variable_name == "delayed_queue_size") {
					$sql_delayed_queue = $var->Value;
				} elseif ($var->Variable_name == "delayed_insert_timeout") {
					$sql_delayed_timeout = $var->Value;
				} elseif ($var->Variable_name == "max_delayed_threads") {
					$sql_delayed_threads = $var->Value;
				} elseif ($var->Variable_name == "storage_engine") {
					$sql_engine = $var->Value;
				} elseif (empty($sql_engine) && $var->Variable_name == "table_type") {
					$sql_engine = $var->Value;
				}
			}
			if ($wdebug_mode) {
				echo "\n<!--  MySQL variables \$sql_vars:";
				print_r($sql_vars);
				echo "-->\n";
			}
		} ?>
		<p class="sys-settings"><strong>MySQL <?php _e('Version'); ?></strong>: <?php if (!empty($sql_version)) { echo $sql_version; } else { _e("unknown","wassup"); } ?>
		<ul class="varlist">
		<li><strong>MySQL <?php _e('Storage Engine','wassup'); ?></strong>: <?php
		if (!empty($sql_engine)) { 
			echo $sql_engine;
			if (empty($table_engine)) $table_engine=$sql_engine;
		} elseif (!empty($table_engine)) {
			echo $table_engine;
		} else { 
			_e("unknown","wassup");
		} ?></li>
		<li><strong>MySQL <?php _e('Charset/collation','wassup'); ?></strong>: <?php if (!empty($sql_collation)) {
			echo $sql_collation;
		} else {
			$sql_charset = wassupDb::mysql_client_encoding();
			if (!empty($sql_charset)) { 
				echo $sql_charset;
			} else { _e("unknown","wassup"); }
		}
		?></li>
		<li><strong>MySQL <?php _e('Max User Connections','wassup'); ?></strong>: <?php 
		if(isset($sql_max_connections) && is_numeric($sql_max_connections)){
			if($sql_max_connections >0){
				if($sql_max_connections < 48)
					echo '<span style="'.$alertstyle.'">'.$sql_max_connections.'</span> ('.__("possibly too small","wassup").')';
				else echo (int)$sql_max_connections;
			}else{
				echo __("unlimited/up to server maximum","wassup");
			}
		} else { 
			_e("unknown","wassup");
		}?></li>
		<li><strong>MySQL <?php _e('Query Cache Allocation','wassup'); ?></strong>: <?php 
		if (preg_match('/^(on|yes|1|true)$/i',$sql_query_cache_enabled)>0) {
			if (is_numeric($sql_query_cache) && (int)$sql_query_cache >0) {
				$cache_size = round((int)$sql_query_cache/1024/1024);
				if ($cache_size >256) {
					if ($sql_cache_type != "2" && stristr($sql_cache_type,'demand')===false) 
						echo '<span style="'.$alertstyle.'">'.$cache_size.'M </span> ('.__("possibly too big, reduces available RAM.","wassup").')';
					else
						echo $cache_size.'M';
				} else {
					echo $cache_size . "M";
				}
				if ($sql_cache_type == "2" || stristr($sql_cache_type,'demand')!==false) {
					echo " (".__("on demand","wassup").")";
				} elseif (is_numeric($sql_cache_limit)) {
					echo "</li>\n\t\t<li><strong>MySQL ".__("Cached Query Limit","wassup")."</strong>: ".round((int)$sql_cache_limit/1024/1024) .'M</nobr>';
				}
			} else {
				echo $sql_query_cache." (".__("disabled","wassup").")";
			}
		} else { 
			_e("disabled","wassup");
		} ?></li>
		<li><strong>MySQL <?php _e('Index Buffer','wassup'); ?></strong>: <?php
		if (empty($table_engine) || stristr($table_engine,"myisam")!==false) {
			//key_buffer is MyISAM parameter only

			if (is_numeric($sql_indexbuffer)) {
				if ((int)$sql_indexbuffer >0)
					echo (round((int)$sql_indexbuffer/1024/1024)) . "M (key buffer)";
				else
					echo $sql_indexbuffer." (".__("disabled","wassup").")";
			} else {
				_e("unknown","wassup");
			}
		} elseif (stristr($table_engine,"innodb")!==false) {
			//InnoDB uses "innodb_buffer_pool_size"
			if (is_numeric($sql_buffersize)) {
				if ((int)$sql_buffersize >0)
					echo (round((int)$sql_buffersize/1024/1024)) . "M (buffer pool size)";
				else
					echo $sql_buffersize." (".__("disabled","wassup").")";
			} else {
				_e("unknown","wassup");
			}
		} elseif (is_numeric($sql_indexbuffer)) {
			if ((int)$sql_indexbuffer >0)
				echo (round((int)$sql_indexbuffer/1024/1024)) . "M";
			else
				echo $sql_indexbuffer." (".__("disabled","wassup").")";
		} else {
			_e("unknown","wassup");
		} ?></li>
		<li><strong>MySQL <?php _e('Read Buffer','wassup'); ?></strong>: <?php 
		if (is_numeric($sql_readbuffer)) {
			if ((int)$sql_readbuffer >0) {
				echo (round((int)$sql_readbuffer/1024/1024)) . "M";
			} else {
				echo $sql_readbuffer." (".__("disabled","wassup").")";
			}
		} else { 
			_e("unknown","wassup");
		} ?></li>
		<li><strong>MySQL <?php 
		if (empty($table_engine) || stristr($table_engine,"isam")!==false) {
			if (!is_numeric($sql_delayed_queue) || (int)$sql_delayed_queue == 0 || (int)$sql_delayed_threads == 0) {
				echo __("Delayed Insert","wassup").'</strong>: '.__("disabled","wassup");
			} elseif (is_numeric($sql_delayed_threads) && (int)$sql_delayed_threads >0) {
				echo __("Delayed Insert Queue","wassup"); ?></strong>: <?php
				echo (int)$sql_delayed_queue ." ".__("rows","wassup");
				if (isset($sql_delayed_timeout)) {
					echo "</li>\n\t\t<li><strong>MySQL ".__("Delayed Handler Timeout","wassup")."</strong>: ";
					if ((int)$sql_delayed_timeout >60)
						echo ($sql_delayed_timeout/60)." ".__("minutes");
					elseif ((int)$sql_delayed_timeout >25)
						echo $sql_delayed_timeout." ".__("seconds");
					else
						echo '<span style="'.$alertstyle.'">'.$sql_delayed_timeout.'</span> '.("seconds");
				}
			} else {
				echo __("Delayed Insert Queue","wassup").'</strong>: '.__("unknown","wassup");
			}
		} else { 
			if (stristr($table_engine,"innodb")!==false)
				echo __("Delayed Insert","wassup").'</strong>: '.__("not supported","wassup");
			elseif (!is_numeric($sql_delayed_queue) || (int)$sql_delayed_queue == 0)
				echo __("Delayed Insert","wassup").'</strong>: '.__("disabled","wassup");
			else
				echo __("Delayed Insert Queue","wassup").'</strong>: '.__("unknown","wassup");
		} ?></li>
		<li><strong>MySQL <?php _e("Wait Timeout","wassup"); ?></strong>: <?php
		if (is_numeric($sql_wait_timeout)) {
			echo $sql_wait_timeout." ".__("seconds","wassup");
		} else { 
			_e("unknown","wassup");
		}
		?></li>
		<li><strong>MySQL <?php _e('Timezone'); ?></strong>: <?php 
		if (empty($sql_timezone)) $sql_timezone="SYSTEM";
		if ($sql_timezone == "SYSTEM" && !empty($host_timezone)) {
			if (is_array($host_timezone)) {
				$mysql_tz = $host_timezone[0];
			} else {
				$mysql_tz = $host_timezone;
			}
		} else {
			$mysql_tz = wassupDb::get_db_setting('timezone');
		}
		$mysqloffset = wassupDb::get_db_setting('tzoffset');
		if ($sql_timezone != $mysql_tz) {
			echo $sql_timezone.' ('.$mysql_tz.' UTC '.(int)($mysqloffset/3600).')';
		} else {
			echo $sql_timezone.' (UTC '.(int)($mysqloffset/3600).')';
		}
		if (version_compare($wp_version,'2.8.3','>='))
			echo ' <small> ('.__("may be different from PHP offset","wassup").')</small>';
		?></li>
		</ul>
		<br />
		</div><!-- /sysinfo -->
		<p class="opt-prev-next"><a href="<?php echo admin_url('admin.php?page=wassup-options&tab=2');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php if($has_uninstall_tab)echo admin_url('admin.php?page=wassup-options&tab=4');else echo admin_url('admin.php?page=wassup-options&tab=sys');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br />
	<!-- <p class="opt-prev-next"><a href="<?php if($has_uninstall_tab)echo admin_url('admin.php?page=wassup-options&tab=4');else echo admin_url('admin.php?page=wassup-options&tab=3');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php echo admin_url('admin.php?page=wassup-options&tab=faq');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br /> -->
	</div>
<?php
	if($has_uninstall_tab){?>
	<div id="wassup_opt_frag-4" class="optionspanel<?php if ($tab == "4") echo ' tabselected';?>">
		<h3><?php _e('Want to uninstall WassUp?', 'wassup') ;?></h3>
		<p><?php _e('No problem. Before you deactivate this plugin, check the box below to cleanup any data that was collected by WassUp that could be left behind.', 'wassup') ;?></p><br />
		<p><input type="checkbox" name="wassup_uninstall" value="1" <?php if ($wassup_options->wassup_uninstall == 1 ) echo 'checked="CHECKED"'; ?> /> <strong><?php _e('Permanently remove WassUp data and settings.','wassup'); ?></strong></p>
		<?php if ($wassup_options->wassup_uninstall == 1) { ?>
			<span style="font-size:95%;font-weight:bold; margin-left:20px;<?php echo $alertstyle; ?>"><span style="text-decoration:blink;padding-left:5px;"><?php _e("WARNING","wassup"); ?>! </span><?php _e("All WassUp data and settings will be DELETED upon deactivation of this plugin.","wassup"); ?></span><br />
		<?php } ?>
		<p><?php echo sprintf(__("This action cannot be undone. Before uninstalling WassUp, you should backup your Wordpress database first. WassUp data is stored in the table %s.", "wassup"),'<strong>'.$wassup_options->wassup_table.'</strong>');?></p>

		<br /><p><?php echo sprintf(__("To help improve this plugin, we would appreciate your feedback at %s.","wassup"),'<a href="http://www.wpwp.org">www.wpwp.org</a>');?></p>
		<br /><br />
		<p class="submit"><input type="submit" name="submit-options4" id="submit-options4" class="submit-opt button button-left button-primary" value="<?php _e('Save Settings','wassup');?>" onclick="jQuery('#submit-options4').val('Saving...');"/>&nbsp;<input type="reset" name="reset" value="<?php _e('Reset','wassup');?>" class="reset-opt button button-secondary" /> - <input type="submit" name="reset-to-default" class="default-opt button button-caution wassup-button" value="<?php _e("Reset to Default", "wassup");?>" /></p>
		<p class="opt-prev-next"><a href="<?php echo admin_url('admin.php?page=wassup-options&tab=3');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php echo admin_url('admin.php?page=wassup-options&tab=faq');?>"><?php echo __("Next","wassup").'&larr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br />
	</div><?php
	} //if has_uninstall_tab
?>
	<div id="wassup_opt_frag-5" class="optionspanel faqpanel<?php if($tab=="faq" || $tab=="5") echo ' tabselected';?>">
		<h3><?php echo __("Frequently Asked Questions","wassup");?></h3>
		<ol>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I add WassUp's chart to my admin dashboard?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Check the box for \"Enable widget/small chart in admin dashboard\" under %s tab.","wassup"),'<span class="code">WassUp >> '.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I display WassUp widgets on my site?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo __("From Wordpress widgets panel, drag the \"WassUp Online\" widget or the \"Wassup Top Stats\" widget from the list of available widgets on the left into your theme's \"Sidebar\" or \"Footer\" area on the right or use the Customizer to add Wassup widgets interactively.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("My Wordpress theme is not widget ready. Is it possible to display WassUp widgets on my site?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo __("Yes. Insert the template tag \"wassup_sidebar()\" into your theme's \"sidebar.php\" file to display Wassup widgets as a single combined widget on your site.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I view the real-time visitor geolocation map in WassUp?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Check the box for \"Display a GEO IP Map in spy visitors view\" in %s tab and save, then navigate to %s panel to see the map.","wassup"),'<span class="code">WassUp >> '.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>','<span class="code">WassUp >><nobr>'.__("SPY Visitors","wassup").'</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("The map has vanished and I get the message: \"Google has disabled use of the Maps API for this application\". How do I fix this?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Erase the contents of \"Google Maps API Key\" field under %s tab. Since WassUp 1.8.4, an API key is not required for viewing Google!Maps API v3 maps in WassUp.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I exclude a visitor from being recorded?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Navigate to %s tab and enter a visitor's username, IP address, or hostname into the appropriate text area for that \"Recording Exclusion\" type.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Filters & Exclusions","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I stop (temporarily) WassUp from recording new visits on my site?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Uncheck the box for \"Enable statistics recording\" under %s tab.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("In Wordpress multisite, how do I stop (temporarily) WassUp from recording new visits on all sites in the network?","wassup");?></span><br/>
		<strong>A#1:</strong> <span class="faq-answer"><?php echo sprintf(__("If plugin is \"network activated\", login as network admin, go to the Network admin dashboard, navigate to %s tab and Uncheck the box for \"Enable Statistics Recording for network\" and save.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >>['.__("General Setup","wassup").']</span>');?></span><br/>
		<strong>A#2:</strong> <span class="faq-answer"><?php echo sprintf(__("If plugin is NOT \"network activated\", login as network admin, go to the main site/parent domain admin dashboard, navigate to %s tab, then Uncheck the box for \"Enable Statistics Recording for network\" and save.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span>
		</li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("No data is being displayed; or \"Visitor Details\" panel show 0 records for the last 24 hours. How do I fix this?","wassup");?></span><br/>
		<strong>A #1:</strong> <span class="faq-answer"><?php echo sprintf(__("Check the box for \"Enable statistics recording\" setting under %s tab and save.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span><br/>
		<strong>A #2:</strong> <span class="faq-answer"><?php echo sprintf(__("Click the [Reset to Default] button under %s tab.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span><br/>
		<strong>A #3:</strong> <span class="faq-answer"><?php echo sprintf(__("Navigate to %s tab and uncheck the \"MySQL Delayed Insert\" setting and save.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Manage File & Data","wassup").']</nobr></span>');?></span><br/>
		<strong>A #4:</strong> <span class="faq-answer"><?php echo __("Deactivate and Re-activate Wassup from Wordpress Plugins panel.","wassup");?></span><br/>
		<strong>A #5:</strong> <span class="faq-answer"><?php echo sprintf(__("If you have access to MySql/phpMyAdmin on your host server, run the MySql command %s to repair and release any locks on wassup table. Note that wassup table name may be different in other Wordpress setups.","wassup"),'<code>REPAIR TABLE '.$wassup_options->wassup_table.'</code>');?></span><br/>
		<strong>A #6:</strong> <span class="faq-answer"><?php echo __("As a last resort, uninstall WassUp cleanly (delete data and files) and reinstall it.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("My popular web site is hosted on a shared server with restrictive database size limits. How do I prevent WassUp's table from growing too big for my allocated quota?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("Navigate to %s tab and enable the setting for \"Auto Delete\" of old records and/or check the box to receive an email alert when the table size limit is exceeded.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Manage File & Data","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("WassUp visitor counts are much lower than actual for my website. Why is there a discrepancy and how do I fix it?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo __("Low visitor count is likely caused by page caching on your website. WassUp is incompatible with static page caching plugins such as WP Supercache, WP Cache, and Hyper Cache. To fix, uninstall your cache plugin or switch to a different (javascript-based) statistics plugin.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("Is there any caching plugin that works with WassUp?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("%s is the only caching plugin verified to work with WassUp.","wassup"),'<a href="http://wordpress.org/extend/plugins/wp-widget-cache/">WP Widget Cache</a>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How can I make Wassup run faster?","wassup");?></span><br/>
		<strong>A #1:</strong> <span class="faq-answer"><?php echo sprintf(__("Keep Wassup table size small with automatic deletions or periodic manual delete of old records under %s tab.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Manage File & Data","wassup").']</nobr></span>');?></span><br/>
		<strong>A #2:</strong> <span class="faq-answer"><?php echo __("If using the \"Top Stats\" widget on your site, set refresh frequency to 15 minutes or higher.","wassup");?></span><br/>
		<strong>A #3:</strong> <span class="faq-answer"><?php echo sprintf(__("Reduce the number of recording exclusions (by ip/hostname/username/url) under %s tab.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Filters & Exclusions","wassup").']</nobr></span>');?></span><br/>
		<strong>A #4:</strong> <span class="faq-answer"><?php 
		$files='<span class="code">badhosts.txt,  badhosts-intl.txt,'.__("and","wassup").' lib/akismet.class.php</span>';
		echo sprintf(__("Delete the files %s from Wassup plugin folder to force reduce I/O usage in spam/malware detection.","wassup"),$files);?></span><br/>
		<strong>A #5:</strong> <span class="faq-answer"><?php echo sprintf(__("Stop all spam/malware detection by unchecking \"Enable Spam and malware detection on records\" under %s tab.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("Why does WassUp stats sometimes show more page views than actual pages clicked by a person?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo __("\"Phantom\" page views can occur when a user's browser does automatic feed retrieval, link pre-fetching, a page refresh, or automatically adds your website to it's \"Top sites\" window (Safari). WassUp tracks these because they are valid requests from the browser and are sometimes indistinguishable from user link clicks.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I upgrade WassUp safely when my site has frequent visitors?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo sprintf(__("To upgrade WassUp when your site is busy, you must first disable statistics recording manually under %s tab, then do the plugin upgrade, and afterwards re-enable recording manually when the upgrade is complete and the plugin is active.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("General Setup","wassup").']</nobr></span>');?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("An unspecified error occurred during plugin upgrade. What do I do next?","wassup");?></span><br/>
		<strong>A:</strong> <span class="faq-answer"><?php echo __("Wait a few minutes. Do NOT re-attempt to upgrade nor try to activate the plugin again! An activation error with no explanation is probably due to your browser timing out, not an upgrade failure. WassUp continues it's upgrade in the background and will activate automatically when it is done. After a few minutes (5-10) has passed, revisit Wordpress admin Plugins panel and verify that Wassup plugin has activated.","wassup");?></span></li>
		<li><strong>Q:</strong> <span class="faq-question"><?php echo __("How do I uninstall WassUp cleanly?","wassup");?></span><br/>
		<strong>A #1:</strong> <span class="faq-answer"><?php echo __("From a single Wordpress site: navigate to Wordpress Plugins panel and deactivate WassUp plugin. Then, on the same page, click the \"delete\" link below WassUp name. This deletes both data and files permanently.","wassup");?></span><br/>
		<strong>A #2:</strong> <span class="faq-answer"><?php echo __("From Wordpress multisite Network admin panel: navigate to Plugins panel and deactivate WassUp plugin. If the plugin is not \"network activated\", navigate to the main site/parent domain Plugins panel and deactivate Wassup plugin there, then return to Network admin Plugins panel. Click the \"delete\" link below WassUp name. This deletes both data and files permanently from the main site/parent domain and deletes Wassup data from all the subsites in the network.","wassup");?></span><br/>
		<strong>A #3:</strong> <span class="faq-answer"><?php echo sprintf(__("From a subsite in Wordpress multisite: navigate to %s tab and check the box for \"Permanently remove WassUp data and settings\" and save. Next, go to the subsite's Plugins panel and deactivate WassUp plugin. This deletes the subsite's data permanently. No files are deleted (not needed).","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >><nobr>['.__("Uninstall","wassup").']</nobr></span>');?></span><br/>
		<strong>A #4:</strong> <span class="faq-answer"><?php echo sprintf(__("From a Wordpress 2.x site: navigate to %s tab and check the box for \"Permanently remove WassUp data and settings\" and save. Next, go to Wordpress Plugins panel and deactivate WassUp plugin. This deletes the data permanently. To delete the plugin files from Wordpress 2.x, use an ftp client software on your PC or login to your host server's \"cpanel\" and use \"File Manager\" to delete the folder \"wassup\" from the %s directory on your host server.","wassup"),'<span class="code">WassUp >>'.__("Options","wassup").' >>['.__("Uninstall","wassup").']</span>','<code>/wordpress/wp-content/plugins/</code>');?></span></li>
		</ol>
		<p class="legend"><?php echo sprintf(__("Visit the %s to find more answers to your WassUp questions.","wassup"),'<a href="http://wordpress.org/support/plugin/wassup">'.__("Plugin Forum","wassup").'</a>');?></p>
		<br />
		<p class="opt-prev-next"><a href="<?php if($has_uninstall_tab)echo admin_url('admin.php?page=wassup-options&tab=4');else echo admin_url('admin.php?page=wassup-options&tab=3');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php echo admin_url('admin.php?page=wassup-options&tab=donate');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br />
	</div>
	<div id="wassup_opt_frag-6" class="optionspanel donatepanel<?php if($tab=="donate" || $tab=="6") echo ' tabselected';?>">
		<h3><?php _e("How you can donate","wassup"); ?></h3>
		<p><?php echo __("If you like this plugin, please consider making a donation to help keep it's development active.","wassup");?></p>
		<div class="donate-block">
			<div id="donate-paypal" class="donate-box"><strong><?php echo sprintf(__("Donate by %s","wassup"),'PayPal');?></strong>: <a href="http://www.wpwp.org" title="Donate" target="_blank"><img src="<?php echo WASSUPURL.'/img/btn_donateCC_LG.gif';?>"/></a></div>
			<div id="donate-bitcoin" class="donate-box"><strong><?php echo sprintf(__("Donate %s","wassup"), "BitCoins");?></strong>:  <a id="BCdonate" href="#"><img src="<?php echo WASSUPURL.'/img/donate_64.png';?>" style="width:200px;height:50px;"/></a><br/>
				<div id="bc_placeholder" style="display:none;">
					<span><?php echo __("Send your bitcoin donation to this address","wassup");?></span>:
					<img class="bc-addr" src="http://helenesit.com/multimedia/images/bc-donate-addr<?php echo rand(1,3);?>.png" align="center" alt="15ohMGD6dg233Tfem2S7CdAoW8jC5WMW5T"/>
				</div>
			</div>
		</div>
		<div class="donate-block">
		<?php
		//DONOR LISTS or a DONATION CAMPAIGN can be added here or in a separate file, "donate.php" or as an external link or iframe.
		if (file_exists(WASSUPDIR."/lib/donate.php")) include_once(WASSUPDIR."/lib/donate.php");?>
		</div>
		<br />
		<p class="opt-prev-next"><a href="<?php echo admin_url('admin.php?page=wassup-options&tab=faq');?>"><?php echo '&larr;'.__("Prev","wassup");?></a> &nbsp; &nbsp; <a href="<?php echo admin_url('admin.php?page=wassup-options&tab=1');?>"><?php echo __("Next","wassup").'&rarr;';?></a> &nbsp; &nbsp; <a href="#wassupsettings" onclick="wScrollTop();return false;"><?php echo __("Top","wassup").'&uarr;';?></a></p><br />
	</div>
	</div><!-- /#tabcontainer -->
	</form>
	<br />
<script type="text/javascript">jQuery("a#BCdonate").toggle(function(){jQuery('div#bc_placeholder').slideDown("slow");},function(){jQuery('div#bc_placeholder').slideUp("slow");return false;});</script>
<?php
	echo "\n";
} //end wassup_optionsView
?>
