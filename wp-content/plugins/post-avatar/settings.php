<?php
# ----------------------------------------------------------------------------------- #
#                            SETTINGS PAGE AND BACKEND                                #
# ----------------------------------------------------------------------------------- #

/*
 * Create options page
 *
 * @since Post Avatar 1.0
 *
 */
function gklpa_settings_menu() {
	add_options_page(__('Post Avatar Options', 'gklpa'), __('Post Avatar', 'gklpa'), 'manage_options', basename(__FILE__), 'gklpa_settings_form');
}

/*
 * Register plugin settings
 *
 * @since Post Avatar 1.6
 *
 */
function gklpa_register_settings(){
	register_setting( 'gkl_postavatar', 'gkl_postavatar', 'gklpa_sanitize_settings' );
	
	// Create sections
	add_settings_section( 'gklpa_option_section_main', __('Default Options', 'gklpa'), null, 'gkl_postavatar' );
	add_settings_section( 'gklpa_option_section_html', __('Customize HTML/CSS', 'gklpa'), null, 'gkl_postavatar' );
	
	// Create fields for main section
	$fields = array( 'scan_recursive', 'get_imagesize', 'use_feed_filter' );
	
	add_settings_field( 'image_dir', __('Path to Images Folder:'), 'gklpa_settings_display_image_dir', 
		'gkl_postavatar', 'gklpa_option_section_main', array( 'label_for' => 'Image Directory' ) );
	add_settings_field( 'use_content_filter', __('Display:'), 'gklpa_settings_display_use_content_filter', 
		'gkl_postavatar', 'gklpa_option_section_main', array( 'label_for' => 'Display' ) );
	add_settings_field( 'scan_recursive', __('Others:', 'gklpa'), 'gklpa_settings_display_other_checkboxes', 
		'gkl_postavatar', 'gklpa_option_section_main', array( 'label_for' => 'Display', 'other_fields' => $fields  ) );
	
	// Create fields for html section
	$fields = array( 'html_before', 'html_after' );
	add_settings_field( 'html_before', __('HTML:', 'gklpa'), 'gklpa_settings_display_html', 
		'gkl_postavatar', 'gklpa_option_section_html', array( 'label_for' => 'HTML', 'other_fields' => $fields  ) );
	add_settings_field( 'css_class', __('CSS:', 'gklpa'), 'gklpa_settings_display_css', 
		'gkl_postavatar', 'gklpa_option_section_html', array( 'label_for' => 'CSS') );

}

/*
 * Function to display input field for image_dir 
 *
 * @since Post Avatar 1.6
 *
 * @param array $args Additional values 
 */
function gklpa_settings_display_image_dir( $args ) {
	$option = get_option( 'gkl_postavatar' ); 

	$html = "<input id=\"gklpa_image_dir\" name=\"gkl_postavatar[image_dir]\" type=\"text\" value=\"{$option['image_dir']}\" /><br />\n";
	$html .= __( 'You must not leave this field blank. The directory also must exist.', 'gklpa' );
	$html .= "\n";
	
	echo $html;
}

/*
 * Function to display input field for use_content_filter 
 *
 * @since Post Avatar 1.6
 *
 * @param array $args Additional values 
 */
function gklpa_settings_display_use_content_filter( $args ) {
	$option = get_option( 'gkl_postavatar' );
	
	$html = "<input name=\"gkl_postavatar[use_content_filter]\" type=\"checkbox\" value=\"1\" ". checked('1', esc_attr($option['use_content_filter']), false ). " />\n";
	$html .= __('Show avatar in post? Disable to use template tag', 'gklpa');
	$html .= "\n";
	
	echo $html;
}

/*
 * Function to display input field for additional checkboxes: scan_recursive, get_imagesize and use_feed_filter 
 *
 * @since Post Avatar 1.6
 *
 * @param array $args Additional values 
 */
function gklpa_settings_display_other_checkboxes( $args ){
	$option = get_option( 'gkl_postavatar');
	$other_fields = $args['other_fields'];
	
	$html = "<input name=\"gkl_postavatar[scan_recursive]\" type=\"checkbox\" value=\"1\" ". checked('1', esc_attr( $option[$other_fields[0]] ), false ). " />";
	$html .= __('Scan the images directory and its sub-directories?', 'gklpa');
	$html .= "\n<br />\n";
	$html .= "<input name=\"gkl_postavatar[get_imagesize]\" type=\"checkbox\" value=\"1\" ". checked('1', esc_attr( $option[$other_fields[1]] ), false ). " />";
	$html .= __('Get image dimensions? Disable this feature if you encounter getimagesize errors', 'gklpa');
	$html .= "\n<br />\n";
	$html .= "<input name=\"gkl_postavatar[use_feed_filter]\" type=\"checkbox\" value=\"1\" ". checked('1', esc_attr( $option[$other_fields[2]]), false ). " />";
	$html .= __('Show post avatars in RSS feeds?', 'gklpa');
	$html .= "\n<br />\n";
	
	echo $html;
}

/*
 * Function to display input field for before_html and after_html
 *
 * @since Post Avatar 1.6
 *
 * @param array $args Additional values 
 */
function gklpa_settings_display_html( $args ){
	$option = get_option( 'gkl_postavatar' );
	//$other_fields = $args['other_fields'];
	$other_fields =  $args['other_fields'] ;

	
	$html =  __('Use this HTML before/after the post avatar image', 'gklpa'); 
	$html .= "<br />\n <input name=\"gkl_postavatar[html_before]\" type=\"text\" value=\"" . esc_html( stripslashes($option[$other_fields[0]] ) ) ."\" /> / ";
	$html .= "<input name=\"gkl_postavatar[html_after]\" type=\"text\" value=\"". esc_html( stripslashes($option[$other_fields[1]]) ) ."\" /><br /> \n";
	$html .= __('You can leave this field blank.', 'gklpa'); 
	$html .= '<br />';
	
	echo $html;
}

/*
 * Function to display input field for css_class 
 *
 * @since Post Avatar 1.6
 *
 * @param array $args Additional values 
 */
function gklpa_settings_display_css( $args ){
	$option = get_option( 'gkl_postavatar' );
	
	$html = __('Use this CSS class for the post avatar image', 'gklpa'); 
	$html .= "<br /><input name=\"gkl_postavatar[css_class]\" type=\"text\" value=\"" .  esc_attr( $option['css_class'] ) . "\"  /><br />\n";
	$html .= __('You can leave this field blank.', 'gklpa');

	echo $html;
}

/*
 * Sanitize options for Settings API 
 *
 * @since Post Avatar 1.6
 *
 * @param array $input Form input
 * @return array $val_input Sanitized/validated output
 */
function gklpa_sanitize_settings( $input ){
	global $allowedposttags;
		$val_input = (array) get_option('gkl_postavatar');
		$val_input['image_dir'] = esc_attr(trailingslashit(rtrim($input['image_dir'], '/')));
		$val_input['scan_recursive'] = absint($input['scan_recursive']);
		$val_input['use_content_filter'] = absint($input['use_content_filter']);		
		$val_input['get_imagesize'] = absint($input['get_imagesize']);
		$val_input['use_feed_filter'] = absint($input['use_feed_filter']);
		$val_input['css_class'] = sanitize_html_class($input['css_class']); // allow alphanumeric characters only
		$val_input['html_before'] = wp_kses( $input['html_before'], $allowedposttags ); 
		$val_input['html_after'] = wp_kses( $input['html_after'], $allowedposttags );
	
	return $val_input;
}

/*
 * Display the settings HTML 
 *
 * @since Post Avatar 1.4
 *
 */
function gklpa_settings_form() {
	global $allowedposttags, $gklpa_plugin_settings;
	$settings = get_option( 'gkl_postavatar' );
?>
<div class="wrap">
	<h2><?php _e('Post Avatar Settings', 'gklpa'); ?></h2>
	<form name="gkl_postavatar" method="post" action="options.php">	
	<?php settings_fields( 'gkl_postavatar'); 
		  do_settings_sections ( 'gkl_postavatar' );
	?>
		<p class="submit"><input type="submit" name="submit" value="<?php _e('Save Changes', 'gklpa') ?> &raquo;" /></p>

	</form>
</div><?php
}
?>