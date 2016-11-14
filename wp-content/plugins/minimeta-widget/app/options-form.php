<?php
// don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');

function _subpagecharakter($pages,$pageid) { //functon for subpages char
	foreach ($pages as $page) {
		if ($page->post_parent!=0 and $page->ID==$pageid) {
			_subpagecharakter($pages,$page->post_parent);
			echo '&#8212; ';
		}
	}
}

$adminlinks=get_option('minimeta_adminlinks');
$options_widgets = get_option('minimeta_widget_options');
if (empty($mmconfigid))
	$mmconfigid=$_REQUEST['mmconfigid'];
if(!empty($minimeta_options_text)) 
	echo '<div id="message" class="updated fade"><p>'.$minimeta_options_text.'</p></div>'; 

?>


<div class="wrap">
	<div id="icon-themes" class="icon32"><br /></div>
<h2><?php _e('MiniMeta Widget', 'MiniMetaWidget'); ?></h2>


<form action="" method="post">
<?php wp_nonce_field('MiniMeta-options'); ?>

<div id="poststuff" class="metabox-holder has-right-sidebar"> 
	<div class="inner-sidebar">
		<div id="side-sortables" class="meta-box-sortables">

			<table class="widefat" cellspacing="0">
				<thead>
				<tr>
			<?php print_column_headers($page_hook); ?>
				</tr>
				</thead>
				
				<tfoot>
				<tr>
			<?php print_column_headers($page_hook, false); ?>
				</tr>
				</tfoot>	
			
				<tbody id="the-list" class="list:post"> 
				<?php
				$item_columns = get_column_headers($page_hook);
				$hidden = get_hidden_columns($page_hook);
				
				if (is_array($options_widgets)) { 
					foreach ($options_widgets as $optionid => $optionvalues) {
					?><tr id="job-<?PHP echo $optionid;?>" valign="top"><?PHP 
					foreach($item_columns as $column_name=>$column_display_name) {
						$class = "class=\"column-$column_name\"";

						$style = '';
						if ( in_array($column_name, $hidden) )
							$style = ' style="display:none;"';

						$attributes = "$class$style";
						
						switch($column_name) {
							case 'cb':
								echo '<th scope="row" class="check-column"><input type="checkbox" name="mmconfigids[]" value="'. esc_attr($optionid) .'" /></th>';
								break;
							case 'id':
								echo "<td $attributes>".$optionid."</td>"; 
								break;
							case 'name':
								echo "<td $attributes><strong><a href=\"".wp_nonce_url('admin.php?page=minimeta-widget&mmconfigid='.$optionid, 'mmconfig')."\" title=\"".__('Edit:','backwpup').$$optionvalues['optionname']."\">".esc_html($optionvalues['optionname'])."</a></strong>";
								$actions = array();
								$actions['edit'] = "<a href=\"" . wp_nonce_url('themes.php?page=minimeta-widget&mmconfigid='.$optionid, 'mmconfig') . "\">" . __('Edit') . "</a>";
								$actions['copy'] = "<a href=\"" . wp_nonce_url('themes.php?page=minimeta-widget&action=copy&mmconfigid='.$optionid, 'mmconfig_'.$optionid) . "\">" . __('Copy','MiniMetaWidget') . "</a>";
								$actions['delete'] = "<a class=\"submitdelete\" href=\"" . wp_nonce_url('themes.php?page=minimeta-widget&action=delete&mmconfigid='.$optionid, 'delete-mmconfig_'.$optionid) . "\" onclick=\"if ( confirm('" . esc_js(__("You are about to delete this config. \n  'Cancel' to stop, 'OK' to delete.","MiniMetaWidget")) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
								$action_count = count($actions);
								$i = 0;
								echo '<br /><div class="row-actions">';
								foreach ( $actions as $action => $linkaction ) {
									++$i;
									( $i == $action_count ) ? $sep = '' : $sep = ' | ';
									echo "<span class='$action'>$linkaction$sep</span>";
								}
								echo '</div>';
								echo '</td>';
								break;
						}
					}
					echo "\n    </tr>\n";
					}
				}
				?></tbody> 
			</table> 
			
			<div class="clear"><br /></div> 
			
			<div id="jobschedule" class="postbox">
				<h3 class="hndle"><span><?PHP _e('Usage','backwpup'); ?></span></h3>
				<div class="inside">
					<?php _e('1. Create a Widget Config.', 'MiniMetaWidget'); ?><br />
					<?php _e('2. Configure your Widget Config as you wont.', 'MiniMetaWidget'); ?><br />
					<?php _e('3. Place a Widget from WordPress Widgets or in Theme via PHP and select a Widget Config.', 'MiniMetaWidget'); ?><br />
					<?php _e('4. Ready.', 'MiniMetaWidget'); ?><br />
					&nbsp;<br />
					<strong><?php _e('Code too place a Widget via PHP:', 'MiniMetaWidget'); ?></strong><br />
					<code> &lt;?PHP if (function_exists('MiniMetaWidgetSidebar')) MiniMetaWidgetSidebar(<?php _e('Name of Widget Config', 'MiniMetaWidget'); ?>); ?&gt; </code><br />
					&nbsp;<br />				
				</div>
			</div>				
			
		</div>
	</div>
	<div class="has-sidebar" >
		<div id="post-body-content" class="has-sidebar-content">
			
			<div id="jobschedule" class="postbox">
				<h3 class="hndle"><span><?PHP _e('Config Settings','backwpup'); ?></span></h3>
				<div class="inside">

					<?PHP
					 //default options
					if (empty($mmconfigid) or !is_array($options_widgets[$mmconfigid])) { 
						//generate New number
						$mmconfigid=wp_create_nonce(mt_rand(10, 30));
						while (is_array($options_widgets[$mmconfigid])) {
							$mmconfigid=wp_create_nonce(mt_rand(10, 30));
						}
						// def. Opdions
						$options_widgets[$mmconfigid]['optionname']=htmlentities(stripslashes(__('New', 'MiniMetaWidget')));
						$options_widgets[$mmconfigid]['in'][0]['part']='title';
						$options_widgets[$mmconfigid]['in'][1]['part']='linkseiteadmin';
						$options_widgets[$mmconfigid]['in'][2]['part']='linkloginlogout';
						$options_widgets[$mmconfigid]['in'][3]['part']='linkrss';
						$options_widgets[$mmconfigid]['in'][4]['part']='linkcommentrss';
						$options_widgets[$mmconfigid]['in'][5]['part']='linkwordpress';
						$options_widgets[$mmconfigid]['in'][6]['part']='actionwpmeta';
						$options_widgets[$mmconfigid]['out'][0]['part']='title';
						$options_widgets[$mmconfigid]['out'][1]['part']='linkregister';
						$options_widgets[$mmconfigid]['out'][2]['part']='linkloginlogout';
						$options_widgets[$mmconfigid]['out'][3]['part']='linkrss';
						$options_widgets[$mmconfigid]['out'][4]['part']='linkcommentrss';
						$options_widgets[$mmconfigid]['out'][5]['part']='linkwordpress';
						$options_widgets[$mmconfigid]['out'][6]['part']='actionwpmeta';
						$options_widgets[$mmconfigid]['general']['pagesnot']['notselected']=true;
					}
					?>


					<input type="hidden" name="mmconfigid" value="<?php echo $mmconfigid; ?>" />
					<input type="hidden" name="widget-options[<?php echo $mmconfigid; ?>][order][in]" id="orderingin" value="in[]=0&in[]=1&in[]=2&in[]=3&in[]=4&in[]=5&in[]=6&in[]=7&in[]=8&in[]=9&in[]=10&in[]=11&in[]=12&in[]=13&in[]=14&in[]=15&in[]=16&in[]=17&in[]=18&in[]=19&in[]=20" />
					<input type="hidden" name="widget-options[<?php echo $mmconfigid; ?>][order][out]" id="orderingout" value="out[]=0&out[]=1&out[]=2&out[]=3&out[]=4&out[]=5&out[]=6&out[]=7&out[]=8&out[]=9&out[]=10&out[]=11&out[]=12&out[]=13&out[]=14&out[]=15&out[]=16&out[]=17&out[]=18&out[]=19&out[]=20" />
					<input type="submit" name="Submit" class="button-primary alignright" id="Submit" value="<?php _e('Save Changes'); ?>" />
					
					<span class="alignleft"><?php _e('Config Name:', 'MiniMetaWidget'); ?> <input type="text" title="<?php _e('Config Name'); ?>" name="widget-options[<?php echo $mmconfigid; ?>][optionname]" value="<?php echo $options_widgets[$mmconfigid]['optionname']; ?>" size="30" /></span>
					
					<?php 
					$parts=MiniMetaWidgetParts::parts();
					for ($z=0;$z<=1;$z++) {
						//set param for in and out
						if ($z==0) {
							$loginout='in';
							$fuctionplace=3;
							$title=__('Show when Logged in:','MiniMetaWidget');
						}
						if ($z==1) {
							$loginout='out';
							$fuctionplace=4;
							$title=__('Show when Logged out:','MiniMetaWidget');
						}
						//make sorting list
						unset($orderparts);
						for ($i=0;$i<sizeof($options_widgets[$mmconfigid][$loginout]);$i++) {
							$orderparts[]=$options_widgets[$mmconfigid][$loginout][$i]['part'];
						}
						foreach ($parts as $partname => $partvalues) {
							if ($partvalues[$fuctionplace]) 
								if (!in_array($partname,$orderparts)) 
									$orderparts[]=$partname;
						}
						?>
						<div class="widget-log<?php echo $loginout; ?>">
						<h4 style="text-align:center;"><?php echo $title; ?></h4>
						<div class="widget-log<?php echo $loginout; ?>-list" id="widget-log<?php echo $loginout; ?>-list">
						<?PHP  	
						for ($orderingid=0;$orderingid<=sizeof($orderparts);$orderingid++) {
							$optionsnumber='';
							for ($i=0;$i<=sizeof($options_widgets[$mmconfigid][$loginout]);$i++) {
								if ($orderparts[$orderingid]==$options_widgets[$mmconfigid][$loginout][$i]['part']) $optionsnumber=$i;
							}
							if ($parts[$orderparts[$orderingid]][$fuctionplace]) { ?>
							<div class="widget-log<?php echo $loginout; ?>-item if-js-closed" id="<?php echo $loginout; ?>_<?php echo $orderingid; ?>">
								<h4 class="widget-log<?php echo $loginout; ?>-title"><span><input class="checkbox-active" type="checkbox" <?php checked($options_widgets[$mmconfigid][$loginout][$optionsnumber]['part'],$orderparts[$orderingid]); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][<?php echo $loginout; ?>][<?php echo $orderingid; ?>][active]" /> <?php echo $parts[$orderparts[$orderingid]][0]; ?></span><br class="clear" /></h4>
								<input type="hidden"  name="widget-options[<?php echo $mmconfigid; ?>][<?php echo $loginout; ?>][<?php echo $orderingid; ?>][part]" value="<?php echo $orderparts[$orderingid]; ?>" />
								<?PHP if ($parts[$orderparts[$orderingid]][2]) {?>
								<div class="widget-log<?php echo $loginout; ?>-control">
									<?php				
									$options=$options_widgets[$mmconfigid][$loginout][$optionsnumber]['args'];
									$options['ordering']=$orderingid;
									$options['loginout']=$loginout;
									$options['optionname']=$mmconfigid;
									call_user_func($parts[$orderparts[$orderingid]][2], $options);
									?>
								</div>
								<?PHP } ?>
							</div>
						<?PHP	}
						} ?>
						</div>
						</div>
					<?PHP } ?>
				
					<br class="clear" />	
					<div class="widget-general">
						<h4 style="text-align:center;"><?php echo _e('General Settings:','MiniMetaWidget'); ?></h4>
						<div class="widget-general-list">
							<div class="widget-general-item if-js-closed">
								<h4 class="widget-general-title"><span><?php _e('Stylesheet','MiniMetaWidget'); ?></span> <br class="clear" /></h4>
								<div class="widget-general-control">
									&lt;ul
									style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['style']['ul'])); ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][style][ul]" />&quot;
									class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['class']['ul'])); ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][class][ul]" />&quot;
									&gt;<br />
									&lt;li
									style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['style']['li'])); ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][style][li]" />&quot;
									class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['class']['li'])); ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][class][li]" />&quot;
									&gt;<br />
								</div>
							</div>
							<div class="widget-general-item if-js-closed">
								<h4 class="widget-general-title"><span><?php _e('Sidebar Widget Settings (PHP Function)','MiniMetaWidget'); ?></span> <br class="clear" /></h4>
								<div class="widget-general-control">
									<?php 
									if (!isset($options_widgets[$mmconfigid]['general']['php']['title'])) $options_widgets[$mmconfigid]['general']['php']['title']=__('Meta'); //def. Options
									if (!isset($options_widgets[$mmconfigid]['general']['php']['before_title'])) $options_widgets[$mmconfigid]['general']['php']['before_title']='<h2>';
									if (!isset($options_widgets[$mmconfigid]['general']['php']['after_title'])) $options_widgets[$mmconfigid]['general']['php']['after_title']='</h2>';
									if (!isset($options_widgets[$mmconfigid]['general']['php']['before_widget'])) $options_widgets[$mmconfigid]['general']['php']['before_widget']='<div class="widget widget_minimeta">';
									if (!isset($options_widgets[$mmconfigid]['general']['php']['after_widget'])) $options_widgets[$mmconfigid]['general']['php']['after_widget']='</div>';
									?>
									<?php _e('Title:'); ?>
									<input class="textinput" type="text" name="widget-options[<?php echo $mmconfigid; ?>][general][php][title]" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['php']['title'])); ?>" /><br />
									<?php _e('Before Title:'); ?>
									<input class="textinput" type="text" name="widget-options[<?php echo $mmconfigid; ?>][general][php][before_title]" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['php']['before_title'])); ?>" /><br />
									<?php _e('After Title:'); ?>
									<input class="textinput" type="text" name="widget-options[<?php echo $mmconfigid; ?>][general][php][after_title]" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['php']['after_title'])); ?>" /><br />
									<?php _e('Before Widget:'); ?>
									<input class="textinput" type="text" name="widget-options[<?php echo $mmconfigid; ?>][general][php][before_widget]" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['php']['before_widget'])); ?>" /><br />
									<?php _e('After Widget:'); ?>
									<input class="textinput" type="text" name="widget-options[<?php echo $mmconfigid; ?>][general][php][after_widget]" value="<?php echo htmlentities(stripslashes($options_widgets[$mmconfigid]['general']['php']['after_widget'])); ?>" /><br />
								</div>
							</div>
							<div class="widget-general-item if-js-closed">
								<h4 class="widget-general-title"><span><?php _e('Display on Pages','MiniMetaWidget'); ?></span> <br class="clear" /></h4>
								<div class="widget-general-control">
									<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['notselected'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][notselected]" />&nbsp;<?php _e('Display on <b>not</b> selected Pages','MiniMetaWidget');?><br />
									<b><?php _e('out','MiniMetaWidget'); ?>&nbsp;&nbsp;<?php _e('in','MiniMetaWidget'); ?>&nbsp;&nbsp;&nbsp;<?php _e('Pages','MiniMetaWidget'); ?></b><br />
									&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['out']['home'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][out][home]" />&nbsp;&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['in']['home'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][in][home]" />&nbsp;&nbsp;<?php _e('Homepage','MiniMetaWidget');?><br />
									&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['out']['singlepost'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][out][singlepost]" />&nbsp;&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['in']['singlepost'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][in][singlepost]" />&nbsp;&nbsp;<?php _e('Single Post','MiniMetaWidget');?><br />
									&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['out']['search'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][out][search]" />&nbsp;&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['in']['search'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][in][search]" />&nbsp;&nbsp;<?php _e('Search Page','MiniMetaWidget');?><br />
									&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['out']['errorpages'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][out][errorpages]" />&nbsp;&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked($options_widgets[$mmconfigid]['general']['pagesnot']['in']['errorpages'],true); ?> value="1" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][in][errorpages]" />&nbsp;&nbsp;<?php _e('Error Page','MiniMetaWidget');?><br />
							<?php 	
									$pages = get_pages('sort_column=menu_order&hierarchical=1'); 
									//print_r($pages);
									foreach ($pages as $page) { ?>
										&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked(in_array($page->ID,(array)$options_widgets[$mmconfigid]['general']['pagesnot']['out']['pages']),true); ?> value="<?php echo $page->ID; ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][out][pages][]" />&nbsp;&nbsp;&nbsp;<input class="checkbox" type="checkbox" <?php checked(in_array($page->ID,(array)$options_widgets[$mmconfigid]['general']['pagesnot']['in']['pages']),true); ?> value="<?php echo $page->ID; ?>" name="widget-options[<?php echo $mmconfigid; ?>][general][pagesnot][in][pages][]" />&nbsp;&nbsp;<?php _subpagecharakter($pages,$page->ID); echo $page->post_title; ?><br />
							<?PHP	} ?>
								</div>
							</div>			
						</div>
					</div>
		
				
				
				
				</div>
			</div>					

		</div>
	</div>
</div>

</form> 
</div>
