<?php

/**
 * This class is responsible for configuring the admin section
 * of the plugin.  It adds the settings page, sets up the 'edit.php'
 * page and adds additional sortable fields.
 */
class DFADS_Admin {
    
    // Call the necessary hooks.
    public function __construct() {
        if ( is_admin() ) {
	    	add_action( 'admin_init', array( $this, 'page_init' ) );
	    	add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
	    	add_action( 'admin_init', array( $this, 'load_css' ) );
	    	add_action( 'admin_init', array( $this, 'load_js' ) );
			add_filter( 'manage_edit-dfads_columns', array( $this, 'set_custom_edit_dfads_columns' ) );
			add_action( 'manage_dfads_posts_custom_column', array( $this, 'posts_custom_column' ), 10, 2 );
			add_filter( 'manage_edit-dfads_sortable_columns', array( $this, 'dfads_sortable_columns' ) );
			add_action( 'load-edit.php', array( $this, 'edit_dfads_load' ) );
		}
    }
    
    // Load admin specific CSS files.
    function load_css() {
    	if ( self::is_dfads_page() ) {
			wp_register_style( 'dfads-admin-style', DFADS_PLUGIN_URL.'css/admin.css', array(), DFADS_VERSION );
			wp_enqueue_style( 'dfads-admin-style' );
        }
    }
    
    // Load admin specific Javascript files.
    function load_js() {
    	if ( self::is_dfads_page() ) {
			wp_register_script( 'dfads-admin-script', DFADS_PLUGIN_URL.'js/admin.js', array( 'jquery' ), DFADS_VERSION, true );
        	wp_enqueue_script( 'dfads-admin-script' );
        }
    }
	
	// Add link to settings page to the "Settings" menu.
    public function add_plugin_page() {
		global $dfads_plugin_hook;
 		$dfads_plugin_hook = add_options_page('Ads Settings', 'Ads', 'manage_options', 'dfads-settings', array( $this, 'create_admin_page' ) );
    }

	// This is the HTML of the "Settings" page.
    public function create_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'dfads' ); ?>
			<h2>Ads Admin Panel</h2>
			<div class="metabox-holder has-right-sidebar">		
				<div class="inner-sidebar">

				</div> <!-- .inner-sidebar -->
 
				<div id="post-body">
					<div id="post-body-content">
						<div class="postbox">
							<h3 class="dfads_admin"><span>General Settings</span></h3>
							<div class="inside dfads_form_css">
								<?php
								echo '<form method="post" action="options.php">';
								settings_fields( 'dfads-settings-group' );	
								do_settings_sections( 'dfads-settings' );
								submit_button();
								echo '</form>';
								?>
							</div> <!-- .inside -->
						</div>
 
						<div class="postbox">
							<h3 class="dfads_admin"><span>Shortcode / PHP Template Code Generator</span></h3>
							<div class="inside">
								<?php $this->include_generator(); ?>
							</div> <!-- .inside -->
						</div> <!-- .postbox -->
					</div> <!-- #post-body-content -->
				</div> <!-- #post-body -->
			</div> <!-- .metabox-holder -->
		</div> <!-- .wrap -->
		<?php
    }
	
	// Adds the necessary page elements.
    public function page_init() {		
		
		register_setting( 'dfads-settings-group', 'dfads-settings', array( $this, 'validate' ) );

		// Settings
		add_settings_section(
			'dfads-setting-section',	// Section ID
			'',							// Section Title
			'',							// Section Callback Function
			'dfads-settings'			// Menu slug from add_options_page()
		);
		
		// Add Field (Enable shortcodes in Text Widgets)
		add_settings_field(
			'dfads_enable_shortcodes_in_widgets', 		// Field ID
			'Enable shortcodes in Text Widgets:', 		// Field Title
			array( $this, 'dfads_enable_shortcode' ),	// Field Callback Function
			'dfads-settings',							// Menu slug from add_options_page()
			'dfads-setting-section'						// The Section ID this field should appear in.
		);
		
		// Add Field (Enable ad impression count for admin(s))
		add_settings_field(
			'dfads_enable_count_for_admin', 
			'Enable ad impression count for admin(s):', 
			array( $this, 'dfads_enable_count' ), 
			'dfads-settings',
			'dfads-setting-section'			
		);
    }
	
	// Handles updating settings data upon being submitted.
    public function validate( $input ) {
    	// add necessary validation here. For now, none.
        return $input;
    }
	
	// Shortcode checkbox option.
    public function dfads_enable_shortcode() {
    	$output = get_option( 'dfads-settings' );
    	$output['dfads_enable_shortcodes_in_widgets'] = isset( $output['dfads_enable_shortcodes_in_widgets'] ) ? $output['dfads_enable_shortcodes_in_widgets'] : 0;
    	echo '
    	<fieldset class="check">
    		<label>
    			<input name="dfads-settings[dfads_enable_shortcodes_in_widgets]" id="dfads_enable_shortcodes_in_widgets" type="checkbox" value="1" ' . checked( 1, $output['dfads_enable_shortcodes_in_widgets'], false ) . ' /> Enable
    		</label>
    		<p class="form-help">Check this box to enable shortcode rendering in Text modules so you can add your ads to your widgetized areas.<br />By default, WordPress Text widgets do not render shortcodes.</p>
    	</fieldset>';
    }
    
	// Admin impression count checkbox option.
    public function dfads_enable_count() {
    	$output = get_option( 'dfads-settings' );
    	$output['dfads_enable_count_for_admin'] = isset( $output['dfads_enable_count_for_admin'] ) ? $output['dfads_enable_count_for_admin'] : 0;
    	echo '
    	<fieldset class="check">
    		<label>
    			<input name="dfads-settings[dfads_enable_count_for_admin]" id="dfads_enable_count_for_admin" type="checkbox" value="1" ' . checked( 1, $output['dfads_enable_count_for_admin'], false ) . ' /> Enable
    		</label>
    		<p class="form-help">Check this box to enable increasing an ad\'s impression count value when viewed by an account with admin permissions.</p>
    	</fieldset>';
    }
    
    // Loads the shortcode generator script.
    public function include_generator() {
    	return require_once( DFADS_PLUGIN_PATH . 'inc/generator.php' );
    }
    
    // This adds custom fields to the list of ads here: edit.php?post_type=dfads
    function set_custom_edit_dfads_columns($columns) {
		return $columns + array(
			'start_date' => __('Start Date'), 
			'end_date' => __('End Date'),
			'impression_count' => __('Impression Count'),
			'impression_limit' => __('Impression Limit'),
			'ad' => __('Ad'), 
		);
	}
	
	// Only returns true if we're on a DFADS admin config page.
	static function is_dfads_page() {
		
		$request = ( isset( $_REQUEST )  ) ? $_REQUEST : false;
		
		if ( !$request ) {
			return false;
		}
		
		if ( isset( $request['post_type'] ) && $request['post_type'] == 'dfads') {
			return true;
		}
		
		if ( isset( $request['page'] ) && $request['page'] == 'dfads-settings' ) {
			return true;
		}
		
		if ( isset( $request['post'] ) && get_post_type( $request['post'] ) == 'dfads') {
			return true;
		}
		
		return false;
	}

	// This determines what to show in each column on the edit.php?post_type=dfads page.
	function posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'ad':
				echo get_post($post_id)->post_content; 
				break;

			case 'start_date':
				$start_date = get_post_meta( $post_id , '_dfads_start_date' , true ); 
				if ( $start_date != '') {
					echo date_i18n( 'm/d/Y', $start_date );
				} else {
					echo 'Immediately';
				}
				break;

			case 'end_date':
				$end_date = get_post_meta( $post_id , '_dfads_end_date' , true ); 
				if ( $end_date != '') {
					echo date_i18n( 'm/d/Y', $end_date );
				} else {
					echo 'Never';
				}
				break;

			case 'impression_count':
				$count = get_post_meta( $post_id , '_dfads_impression_count' , true ); 
				$count = intval( $count );
				echo number_format( $count );
				break;

			case 'impression_limit':
				$limit = get_post_meta( $post_id , '_dfads_impression_limit' , true );
				if ( $limit > 0 ) {
					echo number_format( $limit );
				} else {
					echo 'Unlimited';
				}
				break;
		} // switch ( $column ) {
	}	
	
	// This initializes sorting on the custom field columns.
	function dfads_sortable_columns( $columns ) {
		$columns['start_date'] = '_dfads_start_date';
		$columns['end_date'] = '_dfads_end_date';
		$columns['impression_count'] = '_dfads_impression_count';
		$columns['impression_limit'] = '_dfads_impression_limit';
		return $columns;
	}
	
	// This captures the $_REQUEST so we can modify the query for sortable columns.
	function edit_dfads_load() {
		add_filter( 'request', array( $this, 'dfads_sort_custom_fields' ) );
	}

	// Figure out what columns we are sorting on so we can update the query.
	function dfads_sort_custom_fields( $vars ) {

		if ( isset( $vars['post_type'] ) && 'dfads' == $vars['post_type'] ) {
			
			// start date
			if ( isset( $vars['orderby'] ) && '_dfads_start_date' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_dfads_start_date',
						'orderby' => 'meta_value_num'
					)
				);
			}
			
			// end date
			if ( isset( $vars['orderby'] ) && '_dfads_end_date' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_dfads_end_date',
						'orderby' => 'meta_value_num'
					)
				);
			}
			
			// impression count
			if ( isset( $vars['orderby'] ) && '_dfads_impression_count' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_dfads_impression_count',
						'orderby' => 'meta_value_num'
					)
				);
			}
			
			// impression limit
			if ( isset( $vars['orderby'] ) && '_dfads_impression_limit' == $vars['orderby'] ) {
				$vars = array_merge(
					$vars,
					array(
						'meta_key' => '_dfads_impression_limit',
						'orderby' => 'meta_value_num'
					)
				);
			}			
		}

		return $vars;
	}
}

new DFADS_Admin();
