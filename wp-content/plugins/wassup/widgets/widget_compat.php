<?php
/**
 * Compatibility functions and classes to build Wassup aside widgets in older versions of Wordpress.
 *
 * @package WassUp Real-time Analytics
 * @subpackage widgets/widget-compat.php module
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

if(!class_exists('WP_Widget')){
/**
 * WP_Widget is a Wordpress class to create multi-widgets in WP v2.8+
 *
 * It is defined here for backward compatibility with WP v2.2 - v2.7
 * New widgets should be extensions of WP_Widget and must overwrite these 3 parent methods:
 *    WP_Widget::form   - control form for editing widget settings.
 *    WP_Widget::update - processes and saves widget settings.
 *    WP_Widget::widget - displays the widget.
 */
class WP_Widget{
	var $id_base;
	var $name;
	var $widget_options;
	var $control_options;
	var $id;
	var $updated = false;
	var $number = false;

	function widget($args,$instance){echo "";}
	function form($instance){echo '<p class="no-options-widget">'.__('There are no options for this widget.').'</p>';return 'noform';}
	function update($new_instance,$old_instance){return $new_instance;}
	/** PHP4 constructor */
	function WP_Widget($id_base=false,$name,$widget_opts=array(),$control_opts=array()){
		WP_Widget::__construct($id_base,$name,$widget_opts,$control_opts );
	}
	/** PHP5 constructor */
	function __construct($id_base=false,$name,$widget_opts=array(), $control_opts=array()){
		if(empty($id_base)) $this->id_base=preg_replace( '/(Widget$)/','',strtolower(get_class($this)));
		else $this->id_base=strtolower($id_base);
		$this->name=$name;
		$this->option_name='widget_'.$this->id_base;
		$this->widget_options=wp_parse_args($widget_opts,array('classname'=>$this->option_name));
		$this->control_options=wp_parse_args($control_opts,array('id_base'=>$this->id_base));
		$this->id=$this->id_base; //single widget only
		$this->number=1;
	}
	/** name attribute for input fields */
	function get_field_name($field_name){
		return $this->id_base.'['.$field_name.']';
	}
	/** id attribute for input fields */
	function get_field_id($field_name){
		return $this->id_base."-".$field_name;
	}
	function update_callback(){
		$instance=array();
		$settings=array();
		if(!empty($_POST) && isset($_POST[$this->id_base])){
			if(is_array($_POST[$this->id_base])) $settings=$_POST[$this->id_base];
			else $settings=maybe_unserialize($_POST[$this->id_base]);
			if(empty($settings) || !is_array($settings)) $settings=$_POST;
			if(!empty($settings)){
				$new_instance=stripslashes_deep($settings);
				$old_instance=$this->get_settings();
				$instance=$this->update($new_instance,$old_instance);
				if(!empty($instance) && is_array($instance)){
					$this->save_settings($instance);
					$this->updated=true;
				}else{
					$instance=array();
				}
			}
		}
		return $instance;
	}
	function get_settings(){
		$settings=maybe_unserialize(get_option($this->option_name));
		if(!is_array($settings))$settings=array();
		return $settings;
	}
	function save_settings($settings){
		update_option($this->option_name,$settings);
	}
} //end Class WP_Widget
}

if (!function_exists('register_widget')){
/**
 * a crude emulation of 'register_widget' function for older versions of Wordpress
 *
 * 'register_widget' by instantiating widget class and assigning parameters for 'wp_register_sidebar_widget' and 'wp_register_widget_control' hooks from class variables and methods
 * @param string $widget_class
 * @return void
 */
function register_widget($widget_class){
	global $wassup_widgets;
	if(empty($wassup_widgets) || !is_array($wassup_widgets)) $wassup_widgets=array();
	if(class_exists($widget_class) && empty($wassup_widgets[$widget_class])){
		$wassup_widgets[$widget_class]=new $widget_class;
		wp_register_sidebar_widget($wassup_widgets[$widget_class]->id,$wassup_widgets[$widget_class]->name,array(&$wassup_widgets[$widget_class],'widget'),$wassup_widgets[$widget_class]->widget_options);
		wp_register_widget_control($wassup_widgets[$widget_class]->id,$wassup_widgets[$widget_class]->name,array(&$wassup_widgets[$widget_class],'form'),$wassup_widgets[$widget_class]->control_options);
	}
}
}
?>
