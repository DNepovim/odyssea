<?PHP
/**
 * MiniMeta widget class for WordPress
 *
 * @since 2.8.0
 */

 // don't load directly 
if ( !defined('ABSPATH') ) 
	die('-1');
 
class WP_Widget_MiniMeta extends WP_Widget {

	function WP_Widget_MiniMeta() {
		$widget_ops = array('classname' => 'widget_minimeta', 'description' => __('Displaying Meta links, Login Form and Admin Links','MiniMetaWidget') );
		$this->WP_Widget('minimeta', __('MiniMeta Widget'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$args['title'] = empty($instance['title']) ? __('Meta') : apply_filters('widget_title', $instance['title']);
		
		//Set options to disply
		minimeta_widget_display($instance['config'],$args);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['config'] = $new_instance['config'];

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '','config'=>'' ) );
		$title = strip_tags($instance['title']);
		$config = $instance['config'];
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('config'); ?>" title="<?php _e('Select a Widget Config','MiniMetaWidget');?>"><?php _e('Widget Config:','MiniMetaWidget');?> 
			<?PHP 
			$options_widgets = get_option('minimeta_widget_options');
			if (is_array($options_widgets)) {
			?>
			<select class="widefat" name="<?php echo $this->get_field_name('config'); ?>" id="<?php echo $this->get_field_id('config'); ?>"><?PHP
			echo '<option value="">'.__('default','MiniMetaWidget').'</option>';
				foreach ($options_widgets as $name => $values) {
					?> <option value="<?PHP echo $name;?>"<?PHP selected($config,$name);?>><?PHP echo $values['optionname'];?></option>'; <?PHP
				}?>
			</select></label></p>
			<?PHP } else { ?>
				<span style="color:red;font-face:italic;"><?PHP _e('default','MiniMetaWidget'); ?></span>
			<?PHP } ?>
			<br />
			<span class="setting-description"><?php _e('To make/change a Widget Config go to <a href="admin.php?page=minimeta-widget">MiniMeta Widget</a>','MiniMetaWidget'); ?></span>
		<?php
	}

	function register() {
		register_widget('WP_Widget_MiniMeta');	
	}	
}
?>