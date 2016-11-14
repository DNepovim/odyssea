<?PHP
// don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');

class MiniMetaWidgetParts {
    //help Functions
	function styleclass($style="",$class="") {
		$style=!empty($style)?' style="'.$style.'"':'';
		$class=!empty($class)?' class="'.$class.'"':'';
		return $style.$class;
	}
    function ulopenclose($useul=true) {
		global $ulopen,$stylegeneralul,$classgeneralul;
		if ($useul and !$ulopen) {
			echo '<ul'.MiniMetaWidgetParts::styleclass($stylegeneralul,$classgeneralul).'>';
			$ulopen=true;
		}	
		if (!$useul and $ulopen) {
			echo '</ul>';
			$ulopen=false;
		}	
	}

	//Title
	function title_display($args) {
		global $user_identity;
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(false);
		if(is_user_logged_in()) {
			$titletext=$title;
			if ($displayidentity and !empty($user_identity)) {
				$titletext=$user_identity;
				if (!empty($bevore))
					$titletext= htmlentities(stripslashes($bevore)).' '.$titletext;
				if (!empty($after))
					$titletext.=' '. htmlentities(stripslashes($after));
			}	
			if ($profilelink and current_user_can('read')) {
                echo $before_title ."<a href=\"".admin_url("/profile.php")."\" title=\"".__('Your Profile')."\">".$titletext."</a>". $after_title; 
            } else {
				echo $before_title . $titletext . $after_title; 
            }
		} else {
			echo $before_title . $title . $after_title;
		}
	}
	
	function title_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if ($loginout=='in') {
			?>
			<input class="checkbox" value="1" type="checkbox" <?php checked($displayidentity,true); ?> id="minimeta-displayidentity-<?php echo $optionname; ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][displayidentity]" />&nbsp;<?php _e('Display user Identity as title','MiniMetaWidget');?><br />
			<?php _e('Text bevore user Identity','MiniMetaWidget');?>&nbsp;<input class="textinput" value="<?php echo htmlentities(stripslashes($bevore)); ?>" type="text" id="minimeta-bevortitletext-<?php echo $optionname; ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][bevore]" /><br />
			<?php _e('Text after user Identity','MiniMetaWidget');?>&nbsp;<input class="textinput" value="<?php echo htmlentities(stripslashes($after)); ?>" type="text" id="minimeta-bevortitletext-<?php echo $optionname; ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][after]" /><br />
			<input class="checkbox" value="1" type="checkbox" <?php checked($profilelink,true); ?> id="minimeta-profilelink-<?php echo $optionname; ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][profilelink]" />&nbsp;<?php _e('Link to Your Profile in title','MiniMetaWidget');?><br />	
			<?PHP
		}
	}

	//Gravatar
	function gravatar_display($args) {
		global $user_ID;
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(false);
		if ($size>512) $size=512;
		echo "<div".MiniMetaWidgetParts::styleclass($stylediv,$classdiv).">";
		echo get_avatar($user_ID, $size);
		echo "</div>";
	}
	
	function gravatar_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if ($size>512) $size=512;
		if (empty($size)) $size=70;
		?>
			<?php _e('Gravatar Size:','MiniMetaWidget');?> <input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($size)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][size]" /><br />
			<hr />
		<?php 
		if (!isset($stylediv)) $stylediv='text-align:center;'; //def. Css
		 _e('Stylesheet:','MiniMetaWidget');?><br /> 
		&lt;div
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylediv)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylediv]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classdiv)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classdiv]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	//Loginform
	function loginform_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if($ulli) {
			MiniMetaWidgetParts::ulopenclose(true);
			echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli).">";
		} else {
			MiniMetaWidgetParts::ulopenclose(false);
		}
		?>
				<form name="loginform" id="loginform" action="<?php echo site_url('wp-login.php', 'login_post') ?>" method="post"<?php echo MiniMetaWidgetParts::styleclass($styleform,$classform); ?>>
                    <p>
                        <label><?php _e('Username') ?><br />
                        <input type="text" name="log" id="user_login" value="<?php echo attribute_escape(stripslashes($user_login)); ?>" size="20" tabindex="10"<?php echo MiniMetaWidgetParts::styleclass($stylelogin,$classlogin); ?> /></label>
                    </p>
                    <p>
                        <label><?php _e('Password') ?><br />
                        <input type="password" name="pwd" id="user_pass" value="" size="20" tabindex="20"<?php echo MiniMetaWidgetParts::styleclass($stylepassword,$classpassword); ?> /></label>
                    </p>
                    <?php do_action('login_form'); ?>
                    <?php if($rememberme) {?><p class="forgetmenot"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php echo MiniMetaWidgetParts::styleclass($stylerememberme,$classrememberme); ?> /> <?php _e('Remember Me'); ?></label></p><?php } ?>
                    <p class="submit">
                        <input type="submit" name="wp-submit" id="wp-submit" value="<?php _e('Log in'); ?>" tabindex="100" />
                        <?php if($redirect) {?><input type="hidden" name="redirect_to" value="<?php echo attribute_escape($_SERVER['REQUEST_URI']); ?>" /><?php } ?>
                        <?php if($testcookie) {?><input type="hidden" name="testcookie" value="1" /><?php } ?>
                    </p>
				</form>
		<?php
		if($ulli) 
			echo "</li>";
	}
	
	function loginform_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		?>
			<input class="checkbox" value="1" type="checkbox" <?php checked($rememberme,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][rememberme]" />&nbsp;<?php _e('Remember Me');?><br />
			<input class="checkbox" value="1" type="checkbox" <?php checked($redirect,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][redirect]" />&nbsp;<?php _e('Enable WordPress redirect function','MiniMetaWidget');?><br />
			<input class="checkbox" value="1" type="checkbox" <?php checked($testcookie,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][testcookie]" />&nbsp;<?php _e('Enable WordPress Cookie Test for login Form','MiniMetaWidget');?><br />
			<input class="checkbox" value="1" type="checkbox" <?php checked($ulli,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][ulli]" />&nbsp;<?php _e('Form in &lt;ul&gt; &lt;il&gt; tag','MiniMetaWidget');?><br />
			<hr />
		<?php
		if (!isset($classlogin)) $classlogin='input'; //def. Css
		if (!isset($classpassword)) $classpassword='input'; 
		 _e('Stylesheet:','MiniMetaWidget');?><br /> 
		&lt;form id=&quot;loginform&quot;
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleform)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleform]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classform)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classform]" />&quot;
		&gt;<br />
		&lt;input type=&quot;text&quot; id=&quot;user_login&quot;
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelogin)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelogin]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlogin)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlogin]" />&quot;
		&gt;<br />
		&lt;input type=&quot;password&quot; id=&quot;user_pass&quot;
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylepassword)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylepassword]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classpassword)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classpassword]" />&quot;
		&gt;<br />
		&lt;input type=&quot;checkbox&quot; id=&quot;rememberme&quot;
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylerememberme)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylerememberme]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classrememberme)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classrememberme]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	//Seiteadmin Link
	function seiteadmin_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(true);
		$linkname=__('Site Admin');
		if ($namedashboard) $linkname=__('Dashboard');
		echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".admin_url()."\"".MiniMetaWidgetParts::styleclass($styleseiteadmin,$classseiteadmin).">".$linkname."</a></li>";
	}

	function seiteadmin_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		?>
			<input class="checkbox" value="1" type="checkbox" <?php checked($namedashboard,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][namedashboard]" />&nbsp;<?php _e('Show Link Name as &quot;Dashboard&quot; <b>not</b> &quot;Site Admin&quot;','MiniMetaWidget');?><br />
			<hr />
		<?php
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleseiteadmin)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleseiteadmin]" />&quot; 
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classseiteadmin)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classseiteadmin]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	//Loginout Link
	function linkloginout_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(true);
		$redirect = $redirect ? $_SERVER['REQUEST_URI'] : '';
		if(is_user_logged_in()) {
			echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".wp_logout_url($redirect)."\"".MiniMetaWidgetParts::styleclass($styleloginout,$classloginout).">".__('Log out')."</a></li>";
		} else {
			echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".wp_login_url($redirect)."\"".MiniMetaWidgetParts::styleclass($styleloginout,$classloginout).">".__('Log in')."</a></li>";
		}
	}
	
	function linkloginout_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		?>
		<input class="checkbox" value="1" type="checkbox" <?php checked($redirect,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][redirect]" />&nbsp;<?php _e('Enable WordPress redirect function','MiniMetaWidget');?><br />
		<hr />
		<?php 
		if (!isset($styleloginout)) $styleloginout='color:red;'; //def. Css
		 _e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleloginout)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleloginout]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classloginout)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classloginout]" />&quot;
		&gt;<br />
		<?PHP
	}

	//Adminlinks
	function adminlinks_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(true);
        if (sizeof($adminlinks)>0 or $notselected) { //show only if a Admin Link is selectesd
			if (!is_array($adminlinks)) $adminlinks[]="";
			$minimeta_adminlinks=get_option('minimeta_adminlinks'); 
			foreach ($minimeta_adminlinks as $menu) {
				$output="";
				foreach ($menu as $submenu) {
					if(current_user_can($submenu[1]) and is_array($submenu) and ((!$notselected and in_array(wp_specialchars($submenu[2]),$adminlinks)) or ($notselected and !in_array(wp_specialchars($submenu[2]),$adminlinks)))) {
						$output.= "<li".MiniMetaWidgetParts::styleclass($styleadminlinksli,$classadminlinksli)."><a href=\"".admin_url("/".$submenu[2])."\" title=\"".$submenu[0]."\"".MiniMetaWidgetParts::styleclass($styleadminlinkshref,$classadminlinkshref).">".$submenu[0]."</a></li>";
					}
				}
				if (!empty($output) and !$notopics) {
					echo "<li".MiniMetaWidgetParts::styleclass($styleadminlinkslitopic,$classadminlinkslitopic).">".$menu['menu']."<ul".MiniMetaWidgetParts::styleclass($styleadminlinksul,$classadminlinksul).">".$output."</ul></li>";
				} else {
					echo $output;
				}    
			}
        }
	}	
	
	function adminlinks_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		?>
        <input class="checkbox" value="1" type="checkbox" <?php checked($notopics,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][notopics]" />&nbsp;<?php _e('Do not show Admin Links Topics','MiniMetaWidget');?><br />
		<input class="checkbox" value="1" type="checkbox" <?php checked($notselected,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][notselected]" />&nbsp;<?php _e('Display <b>not</b> selected Admin Links','MiniMetaWidget');?><br />
		 <?php _e('Select Admin Links:','MiniMetaWidget');?> <input type="button" value="<?php _e('All'); ?>" onclick='jQuery("#minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > optgroup >option").attr("selected","selected")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /> <input type="button" value="<?php _e('None'); ?>" onclick='jQuery("#minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > optgroup > option").attr("selected","")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /><br />
         <select style="height:120px;font-size:11px;" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][adminlinks][]" id="minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?>" multiple="multiple">
         <?PHP
			$minimeta_adminlinks=get_option('minimeta_adminlinks');
            foreach ($minimeta_adminlinks as $menu) {
             echo "<optgroup label=\"".$menu['menu']."\">";
             foreach ($menu as $submenu) {
              if (is_array($submenu)) {
               ?> <option value="<?PHP echo $submenu[2];?>"<?PHP selected(in_array($submenu[2],(array)$adminlinks),true);?>><?PHP echo $submenu[0];?></option> <?PHP
              }
             }
             echo "</optgroup>";
            }        
         ?>  
         </select>
		<hr />
		<?php 
		if (!isset($styleadminlinkslitopic)) $styleadminlinkslitopic='font-weight:bold;font-style:italic;'; //def. Css
		if (!isset($styleadminlinksli)) $styleadminlinksli='font-weight:normal;font-style:normal;';
		if (!isset($styleadminlinksselect)) $styleadminlinksselect='font-size:10px;';
		
		_e('Stylesheet:','MiniMetaWidget'); echo '<br />';
		_e('topic','MiniMetaWidget');?>&nbsp;&lt;li
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinkslitopic)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinkslitopic]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinkslitopic)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinkslitopic]" />&quot;
		&gt;<br />
		&lt;ul
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinksul)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinksul]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinksul)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinksul]" />&quot;
		&gt;<br />
		&lt;li
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinksli)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinksli]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinksli)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinksli]" />&quot;
		&gt;<br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinkshref)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinkshref]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinkshref)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinkshref]" />&quot;
		&gt;<br />
		<?PHP
	}	
	
	//Adminlinks
	function adminselect_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(true);
        if (sizeof($adminlinks)>0 or $notselected) { //show only if a Admin Link is selectesd
			if (!is_array($adminlinks)) $adminlinks[]="";
            echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><select onchange=\"document.location.href=this.options[this.selectedIndex].value;\"".MiniMetaWidgetParts::styleclass($styleadminlinksselect,$classadminlinksselect)."><option selected=\"selected\"".MiniMetaWidgetParts::styleclass($styleadminlinksoption,$classadminlinksoption).">".__('Please select:','MiniMetaWidget')."</option>";
			$minimeta_adminlinks=get_option('minimeta_adminlinks'); 
            foreach ($minimeta_adminlinks as $menu) {
            $output="";
                foreach ($menu as $submenu) {
					if(current_user_can($submenu[1]) and is_array($submenu) and ((!$notselected and in_array(wp_specialchars($submenu[2]),$adminlinks)) or ($notselected and !in_array(wp_specialchars($submenu[2]),$adminlinks)))) {
						$output.= "<option value=\"".admin_url("/".$submenu[2])."\"".MiniMetaWidgetParts::styleclass($styleadminlinksoption,$classadminlinksoption).">".$submenu[0]."</option>";
                    }
                }
                if (!empty($output) and !$notopics) {
                    echo "<optgroup label=\"".$menu['menu']."\"".MiniMetaWidgetParts::styleclass($styleadminlinksoptgroup,$classadminlinksoptgroup).">".$output."</optgroup>";
                } else {
				     echo $output;
				}    
            }
            echo "</select></li>";
        }
	}	
	
	function adminselect_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		?>
         <input class="checkbox" value="1" type="checkbox" <?php checked($notopics,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][notopics]" />&nbsp;<?php _e('Do not show Admin Links Topics','MiniMetaWidget');?><br />
		 <input class="checkbox" value="1" type="checkbox" <?php checked($notselected,true); ?> name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][notselected]" />&nbsp;<?php _e('Display <b>not</b> selected Admin Links','MiniMetaWidget');?><br />
		 <?php _e('Select Admin Links:','MiniMetaWidget');?> <input type="button" value="<?php _e('All'); ?>" onclick='jQuery("#minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > optgroup >option").attr("selected","selected")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /> <input type="button" value="<?php _e('None'); ?>" onclick='jQuery("#minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > optgroup > option").attr("selected","")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /><br />
         <select style="height:120px;font-size:11px;" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][adminlinks][]" id="minimeta-adminlinks-<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?>" multiple="multiple">
         <?PHP
			$minimeta_adminlinks=get_option('minimeta_adminlinks');
            foreach ($minimeta_adminlinks as $menu) {
             echo "<optgroup label=\"".$menu['menu']."\">";
             foreach ($menu as $submenu) {
              if (is_array($submenu)) {
                ?> <option value="<?PHP echo $submenu[2];?>"<?PHP selected(in_array($submenu[2],(array)$adminlinks),true);?>><?PHP echo $submenu[0];?></option> <?PHP
              }
             }
             echo "</optgroup>";
            }        
         ?>  
         </select>
		<hr />
		<?php 
		if (!isset($styleadminlinksselect)) $styleadminlinksselect='font-size:10px;';
		
		_e('Stylesheet:','MiniMetaWidget'); 
		?><br />
		&lt;select
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinksselect)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinksselect]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinksselect)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinksselect]" />&quot;
		&gt;<br />
		&lt;optiongroup
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinksoptgroup)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinksoptgroup]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinksoptgroup)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinksoptgroup]" />&quot;
		&gt;<br />
		&lt;option
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($styleadminlinksoption)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][styleadminlinksoption]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classadminlinksoption)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classadminlinksoption]" />&quot;
		&gt;<br />
		<?PHP
	}	
	
	//Lostpw Link
	function linklostpw_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		MiniMetaWidgetParts::ulopenclose(true);
		echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".site_url('wp-login.php?action=lostpassword', 'login')."\" title=\"".__('Password Lost and Found')."\"".MiniMetaWidgetParts::styleclass($stylelinklostpw,$classlinklostpw).">".__('Lost your password?')."</a></li>";
	}	
	
	function linklostpw_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelinklostpw)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelinklostpw]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlinklostpw)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlinklostpw]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	//register Link
	function linkregister_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if(get_option('users_can_register')) {
			MiniMetaWidgetParts::ulopenclose(true);
			echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".site_url('wp-login.php?action=register', 'login')."\"".MiniMetaWidgetParts::styleclass($stylelinkregister,$classlinkregister).">" . __('Register') . "</a></li>";
		}
	}		

	function linkregister_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if(!get_option('users_can_register')) { 
			?>
			<span style="color:red;"><?PHP _e('Register is not allowed in this Blog! Activate it to use this Link.','MiniMetaWidget');?></span>
			<hr />
			<?PHP
		}
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelinkregister)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelinkregister]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlinkregister)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlinkregister]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	
	//WP Bokmarks Categorys
	function bookmarkscat_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (is_array($categorys)) {
			MiniMetaWidgetParts::ulopenclose(true);
			wp_list_bookmarks('echo=1&title_li=&before=<li'.MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli).'>&categorize=0&category='.implode(',',$categorys).'&show_images=0&orderby=name');
		}
	}		
	
	function bookmarkscat_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		 _e('Select Links to Display:','MiniMetaWidget');?> <input type="button" value="<?php _e('All'); ?>" onclick='jQuery("#minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > option").attr("selected","selected")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /> <input type="button" value="<?php _e('None'); ?>" onclick='jQuery("#minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > option").attr("selected","")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /><br />
        <select style="height:70px;font-size:11px;" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][categorys][]" id="minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?>" multiple="multiple">
        <?PHP
        $cats=get_terms( 'link_category', array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => false ) );
		foreach ($cats as $catsdisplay) {
            ?> <option value="<?PHP echo $catsdisplay->term_id;?>"<?PHP selected(in_array($catsdisplay->term_id,(array)$categorys),true);?>><?PHP echo wp_specialchars($catsdisplay->name);?></option> <?PHP
        }        
        ?>  
        </select>
		<?PHP
	}
	
	//WP Bokmarks 
	function bookmarks_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (is_array($links)) {
			MiniMetaWidgetParts::ulopenclose(true);
			wp_list_bookmarks('echo=1&title_li=&before=<li'.MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli).'>&categorize=0&show_images=0&show_private=1&hide_invisible=0&orderby=name&include='.implode(',',$links));
		}
	}		
	
	function bookmarks_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		 _e('Select Links to Display:','MiniMetaWidget');?> <input type="button" value="<?php _e('All'); ?>" onclick='jQuery("#minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > option").attr("selected","selected")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /> <input type="button" value="<?php _e('None'); ?>" onclick='jQuery("#minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?> > option").attr("selected","")' style="font-size:9px;"<?php echo $disabeld; ?> class="button" /><br />
        <select style="height:70px;font-size:11px;" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][links][]" id="minimeta-links<?php echo $optionname; ?>-<?php echo $loginout; ?>-<?php echo $ordering; ?>" multiple="multiple">
        <?PHP
        $bookmarks=get_bookmarks(array('hide_invisible' => 0,'orderby' =>'name'));
		foreach ($bookmarks as $linksdisplay) {
            ?> <option value="<?PHP echo $linksdisplay->link_id; ?>"<?PHP selected(in_array($linksdisplay->link_id,(array)$links),true);?>><?PHP echo wp_specialchars($linksdisplay->link_name).' - '.wp_specialchars($linksdisplay->link_url);?></option><?PHP
        }        
        ?>  
        </select>
		<?PHP
	}

	//RSS Link
	function linkrss_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (empty($linktext))
			$linktext=__('Entries <abbr title="Really Simple Syndication">RSS</abbr>');
		MiniMetaWidgetParts::ulopenclose(true);
		echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".get_bloginfo('rss2_url')."\" title=\"".attribute_escape(__('Syndicate this site using RSS 2.0'))."\"".MiniMetaWidgetParts::styleclass($stylelinkrss,$classlinkrss).">".stripslashes($linktext)."</a></li>";
	}			
	
	function linkrss_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (empty($linktext))
			$linktext=__('Entries <abbr title="Really Simple Syndication">RSS</abbr>');
		_e('Link Text:','MiniMetaWidget'); ?> <input class="text" type="text" value="<?php echo htmlentities(stripslashes($linktext)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][linktext]" />	
		<hr />
		<?PHP
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelinkrss)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelinkrss]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlinkrss)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlinkrss]" />&quot;
		&gt;<br />
		<?PHP
	}
		
	//Comment RSS Link
	function linkcommentrss_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (empty($linktext))
			$linktext=__('Comments <abbr title="Really Simple Syndication">RSS</abbr>');
		MiniMetaWidgetParts::ulopenclose(true);
		echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"".get_bloginfo('comments_rss2_url')."\" title=\"".attribute_escape(__('The latest comments to all posts in RSS'))."\"".MiniMetaWidgetParts::styleclass($stylelinkcommentrss,$classlinkcommentrss).">".stripslashes($linktext)."</a></li>";
	}	
	
	function linkcommentrss_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		if (empty($linktext))
			$linktext=__('Comments <abbr title="Really Simple Syndication">RSS</abbr>');
		_e('Link Text:','MiniMetaWidget'); ?> <input class="text" type="text" value="<?php echo htmlentities(stripslashes($linktext)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][linktext]" />	
		<hr />
		<?PHP
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelinkcommentrss)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelinkcommentrss]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlinkcommentrss)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlinkcommentrss]" />&quot;
		&gt;<br />
		<?PHP
	}
		
	//Wordpress Link
	function linkwordpress_display($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		$stylelinkwordpress=!empty($stylelinkwordpress)?' style="'.$stylelinkwordpress.'"':'';
		MiniMetaWidgetParts::ulopenclose(true);
		echo "<li".MiniMetaWidgetParts::styleclass($stylegeneralli,$classgeneralli)."><a href=\"http://wordpress.org/\" title=\"".attribute_escape(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.'))."\"".MiniMetaWidgetParts::styleclass($stylelinkwordpress,$classlinkwordpress).">WordPress.org</a></li>";
	}
	
	function linkwordpress_options($args) {
		if(is_array($args)) 
			extract($args, EXTR_SKIP );
		_e('Stylesheet:','MiniMetaWidget');?><br />
		&lt;a href=&quot;...&quot; 
		style=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($stylelinkwordpress)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][stylelinkwordpress]" />&quot;
		class=&quot;<input class="textinput" type="text" value="<?php echo htmlentities(stripslashes($classlinkwordpress)); ?>" name="widget-options[<?php echo $optionname; ?>][<?php echo $loginout; ?>][<?php echo $ordering; ?>][args][classlinkwordpress]" />&quot;
		&gt;<br />
		<?PHP
	}
	
	//action wp_meta
	function actionwpmeta_display() {
		if(has_action('wp_meta')) {
			MiniMetaWidgetParts::ulopenclose(true);
			do_action('wp_meta');
		}
	}	
	
	function parts() {
		//$MiniMetaWidgetParts['name']=array('name','function to display','function to control','logtin','logtout')
		$MiniMetaWidgetParts['gravatar']=array(__('Gravatar'),array('MiniMetaWidgetParts','gravatar_display'),array('MiniMetaWidgetParts','gravatar_options'),true,false);
		$MiniMetaWidgetParts['title']=array(__('Title'),array('MiniMetaWidgetParts','title_display'),array('MiniMetaWidgetParts','title_options'),true,true);
		$MiniMetaWidgetParts['gravatar']=array(__('Gravatar'),array('MiniMetaWidgetParts','gravatar_display'),array('MiniMetaWidgetParts','gravatar_options'),true,false);
		$MiniMetaWidgetParts['loginform']=array(__('Login Form','MiniMetaWidget'),array('MiniMetaWidgetParts','loginform_display'),array('MiniMetaWidgetParts','loginform_options'),false,true);
		$MiniMetaWidgetParts['linkseiteadmin']=array(__('Link:','MiniMetaWidget').' '.__('Site Admin'),array('MiniMetaWidgetParts','seiteadmin_display'),array('MiniMetaWidgetParts','seiteadmin_options'),true,false);
		$MiniMetaWidgetParts['linkregister']=array(__('Link:','MiniMetaWidget').' '.__('Register'),array('MiniMetaWidgetParts','linkregister_display'),array('MiniMetaWidgetParts','linkregister_options'),false,true);
		$MiniMetaWidgetParts['linkloginlogout']=array(__('Link: Login/Logout','MiniMetaWidget'),array('MiniMetaWidgetParts','linkloginout_display'),array('MiniMetaWidgetParts','linkloginout_options'),true,true);
		$MiniMetaWidgetParts['adminlinks']=array(__('Adminlinks as Links','MiniMetaWidget'),array('MiniMetaWidgetParts','adminlinks_display'),array('MiniMetaWidgetParts','adminlinks_options'),true,false);
		$MiniMetaWidgetParts['adminselect']=array(__('Adminlinks as Selectbox','MiniMetaWidget'),array('MiniMetaWidgetParts','adminselect_display'),array('MiniMetaWidgetParts','adminselect_options'),true,false);
		$MiniMetaWidgetParts['linklostpw']=array(__('Link:','MiniMetaWidget').' '.__('Lost your password?'),array('MiniMetaWidgetParts','linklostpw_display'),array('MiniMetaWidgetParts','linklostpw_options'),false,true);
		$MiniMetaWidgetParts['bookmarkscat']=array(__('Blog Links by category','MiniMetaWidget'),array('MiniMetaWidgetParts','bookmarkscat_display'),array('MiniMetaWidgetParts','bookmarkscat_options'),true,true);
		$MiniMetaWidgetParts['bookmarks']=array(__('Blog Links','MiniMetaWidget'),array('MiniMetaWidgetParts','bookmarks_display'),array('MiniMetaWidgetParts','bookmarks_options'),true,true);
		$MiniMetaWidgetParts['linkrss']=array(__('Link:','MiniMetaWidget').' '.__('Entries <abbr title="Really Simple Syndication">RSS</abbr>'),array('MiniMetaWidgetParts','linkrss_display'),array('MiniMetaWidgetParts','linkrss_options'),true,true);
		$MiniMetaWidgetParts['linkcommentrss']=array(__('Link:','MiniMetaWidget').' '.__('Comments <abbr title="Really Simple Syndication">RSS</abbr>'),array('MiniMetaWidgetParts','linkcommentrss_display'),array('MiniMetaWidgetParts','linkcommentrss_options'),true,true);
		$MiniMetaWidgetParts['linkwordpress']=array(__('Link:','MiniMetaWidget').' WordPress.org',array('MiniMetaWidgetParts','linkwordpress_display'),array('MiniMetaWidgetParts','linkwordpress_options'),true,true);
		$MiniMetaWidgetParts['actionwpmeta']=array(__('Do Action:','MiniMetaWidget').' wp_meta',array('MiniMetaWidgetParts','actionwpmeta_display'),'',true,true);
		return apply_filters('MiniMetaWidget_parts',$MiniMetaWidgetParts);
	}
	
}



?>