<?php
/*
|--------------------------------------------------------------------------
| Add Iconclass Custom Field
|--------------------------------------------------------------------------
*/

if ( !function_exists( 'fastwp_add_custom_nav_fields' ) ) {

    function fastwp_add_custom_nav_fields( $menu_item ) {
        
        $menu_item->menutype = get_post_meta( $menu_item->ID, '_menu_item_menutype', true );
        $menu_item->menuicon = get_post_meta( $menu_item->ID, '_menu_item_menuicon', true );
        $menu_item->menuicon_selected = get_post_meta( $menu_item->ID, '_menu_item_menuicon_selected', true );
        return $menu_item;
        
    }
    
    /* add custom menu fields to menu */
    add_filter( 'wp_setup_nav_menu_item', 'fastwp_add_custom_nav_fields' );

}



if ( !function_exists( 'fastwp_menu_fields_update' ) ) {
    
    function fastwp_menu_fields_update( $menu_id, $menu_item_id, $args ) {
        
        if ( isset($_REQUEST['menu-item-menutype']) && is_array( $_REQUEST['menu-item-menutype']) ) {
            $menutypeclass_value = $_REQUEST['menu-item-menutype'][$menu_item_id];
            update_post_meta( $menu_item_id, '_menu_item_menutype', $menutypeclass_value );
        }
                
        if ( isset($_REQUEST['menu-item-menuicon']) && is_array( $_REQUEST['menu-item-menuicon']) ) {
            $iconborder_value = $_REQUEST['menu-item-menuicon'][$menu_item_id];
            update_post_meta( $menu_item_id, '_menu_item_menuicon', $iconborder_value );
        }
                        
        if ( isset($_REQUEST['menu-item-menuicon_selected']) && is_array( $_REQUEST['menu-item-menuicon_selected']) ) {
            $iconborder_value = $_REQUEST['menu-item-menuicon_selected'][$menu_item_id];
            update_post_meta( $menu_item_id, '_menu_item_menuicon_selected', $iconborder_value );
        }
        
    }
    
    /* save menu custom fields */
    add_action( 'wp_update_nav_menu_item', 'fastwp_menu_fields_update' , 10 , 3 );

}




if ( !function_exists( 'fastwp_ui_edit_walker' ) ) {

    function fastwp_ui_edit_walker( $walker , $menu_id ) {
        
        return 'FastWP_Walker_Nav_Menu_Edit_Custom';
        
    }
    
    /* edit menu walker */
    add_filter( 'wp_edit_nav_menu_walker', 'fastwp_ui_edit_walker' , 10 , 2 );

}



class FastWP_Walker_Nav_Menu_Edit_Custom extends Walker_Nav_Menu  {
	
    /**
	 * @see Walker_Nav_Menu::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {	
	}
	
	/**
	 * @see Walker_Nav_Menu::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
	}
	
	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array() , $current_object_id = 0 ) {
        global $_wp_nav_menu_max_depth;
	   
	    $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;
	
	    $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	
	    ob_start();
	    $item_id = esc_attr( $item->ID );
	    $removed_args = array(
	   //     'action',
	   //     'customlink-tab',
	   //     'edit-menu-item',
	    //    'menu-item',
	   //     'page-tab',
	    //    '_wpnonce',
	    );
	
	    $original_title = '';
	    if ( 'taxonomy' == $item->type ) {
	        $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
	        if ( is_wp_error( $original_title ) )
	            $original_title = false;
	    } elseif ( 'post_type' == $item->type ) {
	        $original_object = get_post( $item->object_id );
	        $original_title = $original_object->post_title;
	    }
	
	    $classes = array(
	        'menu-item menu-item-depth-' . $depth,
	        'menu-item-' . esc_attr( $item->object ),
	        'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
	    );
	
	    $title = $item->title;
	
	    if ( ! empty( $item->_invalid ) ) {
	        $classes[] = 'menu-item-invalid';
	        /* translators: %s: title of menu item which is invalid */
	        $title = sprintf( __( '%s (Invalid)' , 'fastwp' ), $item->title );
	    } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
	        $classes[] = 'pending';
	        /* translators: %s: title of menu item in draft status */
	        $title = sprintf( __('%s (Pending)' , 'fastwp' ), $item->title );
	    }
	
	    $title = empty( $item->label ) ? $title : $item->label;
	
	    ?>
	    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
	        <dl class="menu-item-bar">
	            <dt class="menu-item-handle">
	                <span class="item-title"><?php echo esc_html( $title ); ?></span>
	                <span class="item-controls">
	                    <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
	                    <span class="item-order hide-if-js">
	                        <a href="<?php
	                            echo wp_nonce_url(
	                                add_query_arg(
	                                    array(
	                                        'action' => 'move-up-menu-item',
	                                        'menu-item' => $item_id,
	                                    ),
	                                    remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                                ),
	                                'move-menu_item'
	                            );
	                        ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up' , 'fastwp'); ?>">&#8593;</abbr></a>
	                        |
	                        <a href="<?php
	                            echo wp_nonce_url(
	                                add_query_arg(
	                                    array(
	                                        'action' => 'move-down-menu-item',
	                                        'menu-item' => $item_id,
	                                    ),
	                                    remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                                ),
	                                'move-menu_item'
	                            );
	                        ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down' , 'fastwp'); ?>">&#8595;</abbr></a>
	                    </span>
	                    <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item' , 'fastwp' ); ?>" href="<?php
	                        echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
	                    ?>"><?php _e( 'Edit Menu Item' , 'fastwp' ); ?></a>
	                </span>
	            </dt>
	        </dl>
	
	        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
	            <?php if( 'custom' == $item->type ) : ?>
	                <p class="field-url description description-wide">
	                    <label for="edit-menu-item-url-<?php echo $item_id; ?>">
	                        <?php _e( 'URL' , 'fastwp' ); ?><br />
	                        <input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
	                    </label>
	                </p>
	            <?php endif; ?>
	            <p class="description description-thin">
	                <label for="edit-menu-item-title-<?php echo $item_id; ?>">
	                    <?php _e( 'Navigation Label' , 'fastwp' ); ?><br />
	                    <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
	                </label>
	            </p>
	            <p class="description description-thin">
	                <label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
	                    <?php _e( 'Title Attribute' , 'fastwp' ); ?><br />
	                    <input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
	                </label>
	            </p>
	            <p class="field-link-target description">
	                <label for="edit-menu-item-target-<?php echo $item_id; ?>">
	                    <input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
	                    <?php _e( 'Open link in a new window/tab' , 'fastwp' ); ?>
	                </label>
	            </p>
	            <p class="field-css-classes description description-thin">
	                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
	                    <?php _e( 'CSS Classes (optional)' , 'fastwp' ); ?><br />
	                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
	                </label>
	            </p>
	            <p class="field-xfn description description-thin">
	                <label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
	                    <?php _e( 'Link Relationship (XFN)' , 'fastwp' ); ?><br />
	                    <input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
	                </label>
	            </p>
	            <p class="field-description description description-wide">
	                <label for="edit-menu-item-description-<?php echo $item_id; ?>">
	                    <?php _e( 'Description' , 'fastwp' ); ?><br />
	                    <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
	                    <span class="description"><?php _e( 'The description will be displayed in the menu if the current theme supports it.' , 'fastwp' ); ?></span>
	                </label>
	            </p>        
	            
                <p class="field-custom description description-thin fastwp-edit-menu-type">
	                <label for="edit-menu-item-menutype-<?php echo $item_id; ?>">
	                    
						<?php _e( 'Menu Type' , 'fastwp' ); ?><br />
	                                            
                        <select class="widefat" id="edit-menu-item-menutype-<?php echo $item_id; ?>" name="menu-item-menutype[<?php echo $item_id; ?>]">
                        	<option value="section" <?php selected('section' , esc_attr( $item->menutype ) , true); ?>><?php _e( 'Onepage Section' , 'fastwp'); ?></option>
                            <option value="separator" <?php selected('separator' , esc_attr( $item->menutype ) , true); ?>><?php _e( 'Page separator' , 'fastwp'); ?></option>
                            <option value="page" <?php selected('page' , esc_attr( $item->menutype ) , true); ?>><?php _e( 'Normal Page' , 'fastwp'); ?></option>
                        </select>
                        
	                </label>
	            </p>
				<p class="field-custom description description-thin fastwp-edit-menu-icon" style="<?php echo (esc_attr( $item->menutype ) == 'page')?'display:none;':''; ?>">
	                <label for="edit-menu-item-menuicon-<?php echo $item_id; ?>">
	                    
						<?php _e( 'Section style' , 'fastwp' ); ?><br />
	                                            
                        <select class="widefat" id="edit-menu-item-menuicon-<?php echo $item_id; ?>" name="menu-item-menuicon[<?php echo $item_id; ?>]">
                        	<option value="noicon" <?php selected('noicon' , esc_attr( $item->menuicon ) , true); ?>><?php _e( 'No icon' , 'fastwp'); ?></option>
                            <option value="icon" <?php selected('icon' , esc_attr( $item->menuicon ) , true); ?>><?php _e( 'Icon' , 'fastwp'); ?></option>
                            <option value="iconborder" <?php selected('iconborder' , esc_attr( $item->menuicon ) , true); ?>><?php _e( 'Icon with border' , 'fastwp'); ?></option>
                        </select>
                        
	                </label>
	            </p>
				<br style="clear:both;">
				<div class="fastwp-select-icon" style="<?php echo (esc_attr( $item->menuicon ) == 'noicon')?'display:none;':''; ?>">
					<?php
					echo admin_choose_fa_icon(esc_attr( $item->menuicon_selected ));
					?>				
					<input type="hidden" class="hidden_icon" id="edit-menu-item-menuicon_selected-<?php echo $item_id; ?>" name="menu-item-menuicon_selected[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menuicon_selected ); ?>">
                
				</div>
        
	            <div class="menu-item-actions description-wide submitbox">
	                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
	                    <p class="link-to-original">
	                        <?php printf( __('Original: %s' , 'fastwp' ), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
	                    </p>
	                <?php endif; ?>
	                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
	                echo wp_nonce_url(
	                    add_query_arg(
	                        array(
	                            'action' => 'delete-menu-item',
	                            'menu-item' => $item_id,
	                        ),
	                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
	                    ),
	                    'delete-menu_item_' . $item_id
	                ); ?>"><?php _e('Remove' , 'fastwp'); ?></a> <span class="meta-sep"> | </span> <a class="item-cancel submitcancel" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array('edit-menu-item' => $item_id, 'cancel' => time()), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) ) ) );
	                    ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel' , 'fastwp' ); ?></a>
	            </div>
	
	            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
	            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
	            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
	            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
	            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
	            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
	        </div><!-- .menu-item-settings-->
	        <ul class="menu-item-transport"></ul>
	    <?php
	    
	    $output .= ob_get_clean();

	    }
}



add_action( 'init', 'fwp_buttons' );
function fwp_buttons() {
	add_filter("mce_external_plugins", "fwp_add_buttons");
    add_filter('mce_buttons', 'fwp_register_buttons');
}	
function fwp_add_buttons($plugin_array) {
	$plugin_array['fwp_shortcodes'] = get_template_directory_uri() . '/js/fastwp.admin.mce.js';
	return $plugin_array;
}
function fwp_register_buttons($buttons) {
	array_push( $buttons, 'fastwp_shortcodes' ); // dropcap', 'recentposts
	return $buttons;
}


add_action('admin_ajax_build_shortcodes', array('admin_shortcode_editor', 'build_shortcodes'));
add_action('wp_ajax_build_shortcodes', array('admin_shortcode_editor', 'build_shortcodes'));
add_action('wp_ajax_nopriv_build_shortcodes', array('admin_shortcode_editor', 'build_shortcodes'));


add_action('admin_ajax_fwp_get_shortcode', array('admin_shortcode_editor', 'get_shortcode'));
add_action('wp_ajax_fwp_get_shortcode', array('admin_shortcode_editor', 'get_shortcode'));
add_action('wp_ajax_nopriv_fwp_get_shortcode', array('admin_shortcode_editor', 'get_shortcode'));




function fastwp_scripts_styles_adm(){
	global $fwp_shortcodes;
	if(is_admin()){
		wp_enqueue_style( 'fwp_fontawesome', get_template_directory_uri() . '/css/font-awesome.css');
		wp_enqueue_script( 'fwp_admin', get_template_directory_uri() . '/js/fwp_admin.js', array(), '1.0', true );
		wp_localize_script( 'fwp_admin', 'fastwp_admin', array('theme_url' => get_template_directory_uri() .'/', 'shortcodes' => json_encode($fwp_shortcodes)) );
		wp_enqueue_script('jquery-ui-tabs');
	}
}
add_action( 'admin_enqueue_scripts', 'fastwp_scripts_styles_adm' );

class admin_shortcode_editor {
	static function build_shortcodes(){
	global $fwp_shortcodes;		
		echo '<div style="">
		<dl class="sc_builder">
			<dt>Shortcode to build</dt>
			<dd>
		<select name="fwp_available_shortcodes" id="fwp_available_shortcodes" onChange="fwp_build_shortcode_builder(this)"><option>'.__('Select shortcode to insert','fastwp').'</option>';
		foreach($fwp_shortcodes as $k=>$v){
			echo '<option value="'.$k.'">'.$v['label'].'</option>';
		}
		echo '</select></dd>
		</dl><hr>';
		
		echo '<form action="" id="fwp_shortcode_form" onSubmit="return fwp_add_shortcode()">';
		echo '<div class="fwp_shortcode_tag">
		'.__('Select shortcode to generate...', 'fastwp').'</div>';
		echo '</form></div>';
		exit;
	}

	static function get_shortcode(){
		global $fwp_shortcodes;
		$shortcode = $_POST['shortcode'];
		$content = '<dl class="sc_builder">';
		if(isset($fwp_shortcodes[$shortcode])){
			$code = '1';
			$sc = $fwp_shortcodes[$shortcode];
			if($sc['content'] != false){
				$content .= '<dt>Content</dt>';
				$content .= '<dd>'.self::build_field($sc['content']['type'], 's_content', $sc['content']['default']).'</dd>';
	
			}
			if(count(@$sc['params']) > 0){
				foreach($sc['params'] as $k=>$p){
					$label = (isset($p['label']))?$p['label']:$k;
					$content .= '<dt>'.$label.'</dt>';
					$content .= '<dd>'.self::build_field($p['type'], $k, $p['default']).'</dd>';
	
				}
			}
			
			$content .= '</dl>
			<hr>
			<dl class="sc_builder">
				<dt>
					<input type="submit" class="button button-primary" style="width:100%; margin-bottom:10px" value="Generate code">
					<input type="button" id="fwp_insert_sc" onClick="fwp_insert_into_editor()" class="button button-primary" style="width:100%; margin-bottom:10px" value="Insert into post">
				</dt>
				<dd><textarea id="shortcode_output"></textarea></dd>
			</dl>
			';
		}else {
			$code = '0';
		}
	//	var_dump($fwp_shortcodes[$shortcode]);
		echo json_encode(array('status'=>$code, 'sc'=> $content));
		exit;
	}
	
	static function build_field($type, $id, $default){
		switch($type){
			case 'mce':
			case 'textarea':
				return '<textarea name="'.$id.'" id="'.$id.'" class="fwp_textarea" rel="is_param">'.$default.'</textarea>';
			break;
			case 'image':
			case 'text':
			case 'color':
				return '<input type="text" name="'.$id.'" id="'.$id.'" class="fwp_textarea" value="'.$default.'" rel="is_param">';
			break;
			case 'color-iris':
				return '<input type="text" class="fwp-iris" name="'.$id.'" value="'.$default.'" rel="is_param">';
			break;
			case 'fa-icon':
			
				return '<div class="fastwp-select-icon">'.admin_choose_fa_icon($default).'			
					<input type="hidden" class="hidden_icon" id="'.$id.'" name="'.$id.'" value="'.$default.'" rel="is_param">
                
				</div>';
			break;
			
		}
	}

}

function admin_choose_fa_icon($current = ''){
	global $fwp_font_awesome;
	$HTML = '<div class="fwp-icons">';
	$ThemeDir = get_template_directory_uri();
	foreach($fwp_font_awesome as $icon){
		$HTML .= '<span '.(($icon == $current)?'class="selected"':'').'><img class="aligner" src="'.$ThemeDir.'/images/1px.png"><i id="'.$icon.'" class="fa '.$icon.' fa-2x"></i></span>';
	}
	$HTML .= '</div>';
	return $HTML;
}

$fwp_font_awesome = array('fa-rub','fa-pagelines','fa-stack-exchange','fa-arrow-circle-o-right','fa-arrow-circle-o-left','fa-caret-square-o-left','fa-dot-circle-o','fa-wheelchair','fa-vimeo-square','fa-try','fa-plus-square-o','fa-adjust','fa-anchor','fa-archive','fa-arrows','fa-arrows-h','fa-arrows-v','fa-asterisk','fa-ban','fa-bar-chart-o','fa-barcode','fa-bars','fa-beer','fa-bell','fa-bell-o','fa-bolt','fa-book','fa-bookmark','fa-bookmark-o','fa-briefcase','fa-bug','fa-building-o','fa-bullhorn','fa-bullseye','fa-calendar','fa-calendar-o','fa-camera','fa-camera-retro','fa-caret-square-o-down','fa-caret-square-o-left','fa-caret-square-o-right','fa-caret-square-o-up','fa-certificate','fa-check','fa-check-circle','fa-check-circle-o','fa-check-square','fa-check-square-o','fa-circle','fa-circle-o','fa-clock-o','fa-cloud','fa-cloud-download','fa-cloud-upload','fa-code','fa-code-fork','fa-coffee','fa-cog','fa-cogs','fa-comment','fa-comment-o','fa-comments','fa-comments-o','fa-compass','fa-credit-card','fa-crop','fa-crosshairs','fa-cutlery','fa-desktop','fa-dot-circle-o','fa-download','fa-ellipsis-h','fa-ellipsis-v','fa-envelope','fa-envelope-o','fa-eraser','fa-exchange','fa-exclamation','fa-exclamation-circle','fa-exclamation-triangle','fa-external-link','fa-external-link-square','fa-eye','fa-eye-slash','fa-female','fa-fighter-jet','fa-film','fa-filter','fa-fire','fa-fire-extinguisher','fa-flag','fa-flag-checkered','fa-flag-o','fa-flask','fa-folder','fa-folder-o','fa-folder-open','fa-folder-open-o','fa-frown-o','fa-gamepad','fa-gavel','fa-gift','fa-glass','fa-globe','fa-hdd-o','fa-headphones','fa-heart','fa-heart-o','fa-home','fa-inbox','fa-info','fa-info-circle','fa-key','fa-keyboard-o','fa-laptop','fa-leaf','fa-lemon-o','fa-level-down','fa-level-up','fa-lightbulb-o','fa-location-arrow','fa-lock','fa-magic','fa-magnet','fa-mail-reply-all','fa-male','fa-map-marker','fa-meh-o','fa-microphone','fa-microphone-slash','fa-minus','fa-minus-circle','fa-minus-square','fa-minus-square-o','fa-mobile','fa-money','fa-moon-o','fa-music','fa-pencil','fa-pencil-square','fa-pencil-square-o','fa-phone','fa-phone-square','fa-picture-o','fa-plane','fa-plus','fa-plus-circle','fa-plus-square','fa-plus-square-o','fa-power-off','fa-print','fa-puzzle-piece','fa-qrcode','fa-question','fa-question-circle','fa-quote-left','fa-quote-right','fa-random','fa-refresh','fa-reply','fa-reply-all','fa-retweet','fa-road','fa-rocket','fa-rss','fa-rss-square','fa-search','fa-search-minus','fa-search-plus','fa-share','fa-share-square','fa-share-square-o','fa-shield','fa-shopping-cart','fa-sign-in','fa-sign-out','fa-signal','fa-sitemap','fa-smile-o','fa-sort','fa-sort-alpha-asc','fa-sort-alpha-desc','fa-sort-amount-asc','fa-sort-amount-desc','fa-sort-asc','fa-sort-desc','fa-sort-numeric-asc','fa-sort-numeric-desc','fa-spinner','fa-square','fa-square-o','fa-star','fa-star-half','fa-star-half-o','fa-star-o','fa-subscript','fa-suitcase','fa-sun-o','fa-superscript','fa-tablet','fa-tachometer','fa-tag','fa-tags','fa-tasks','fa-terminal','fa-thumb-tack','fa-thumbs-down','fa-thumbs-o-down','fa-thumbs-o-up','fa-thumbs-up','fa-ticket','fa-times','fa-times-circle','fa-times-circle-o','fa-tint','fa-trash-o','fa-trophy','fa-truck','fa-umbrella','fa-unlock','fa-unlock-alt','fa-upload','fa-user','fa-users','fa-video-camera','fa-volume-down','fa-volume-off','fa-volume-up','fa-wheelchair','fa-wrench','fa-check-square','fa-check-square-o','fa-circle','fa-circle-o','fa-dot-circle-o','fa-minus-square','fa-minus-square-o','fa-plus-square','fa-plus-square-o','fa-square','fa-square-o','fa-btc','fa-eur','fa-gbp','fa-inr','fa-jpy','fa-krw','fa-money','fa-rub','fa-try','fa-usd','fa-align-center','fa-align-justify','fa-align-left','fa-align-right','fa-bold','fa-chain-broken','fa-clipboard','fa-columns','fa-eraser','fa-file','fa-file-o','fa-file-text','fa-file-text-o','fa-files-o','fa-floppy-o','fa-font','fa-indent','fa-italic','fa-link','fa-list','fa-list-alt','fa-list-ol','fa-list-ul','fa-outdent','fa-paperclip','fa-repeat','fa-scissors','fa-strikethrough','fa-table','fa-text-height','fa-text-width','fa-th','fa-th-large','fa-th-list','fa-underline','fa-undo','fa-angle-double-down','fa-angle-double-left','fa-angle-double-right','fa-angle-double-up','fa-angle-down','fa-angle-left','fa-angle-right','fa-angle-up','fa-arrow-circle-down','fa-arrow-circle-left','fa-arrow-circle-o-down','fa-arrow-circle-o-left','fa-arrow-circle-o-right','fa-arrow-circle-o-up','fa-arrow-circle-right','fa-arrow-circle-up','fa-arrow-down','fa-arrow-left','fa-arrow-right','fa-arrow-up','fa-arrows','fa-arrows-alt','fa-arrows-h','fa-arrows-v','fa-caret-down','fa-caret-left','fa-caret-right','fa-caret-square-o-down','fa-caret-square-o-left','fa-caret-square-o-right','fa-caret-square-o-up','fa-caret-up','fa-chevron-circle-down','fa-chevron-circle-left','fa-chevron-circle-right','fa-chevron-circle-up','fa-chevron-down','fa-chevron-left','fa-chevron-right','fa-chevron-up','fa-hand-o-down','fa-hand-o-left','fa-hand-o-right','fa-hand-o-up','fa-long-arrow-down','fa-long-arrow-left','fa-long-arrow-right','fa-long-arrow-up','fa-arrows-alt','fa-backward','fa-compress','fa-eject','fa-expand','fa-fast-backward','fa-fast-forward','fa-forward','fa-pause','fa-play','fa-play-circle','fa-play-circle-o','fa-step-backward','fa-step-forward','fa-stop','fa-youtube-play','fa-adn','fa-android','fa-apple','fa-bitbucket','fa-bitbucket-square','fa-bitcoin','fa-btc','fa-css3','fa-dribbble','fa-dropbox','fa-facebook','fa-facebook-square','fa-flickr','fa-foursquare','fa-github','fa-github-alt','fa-github-square','fa-gittip','fa-google-plus','fa-google-plus-square','fa-html5','fa-instagram','fa-linkedin','fa-linkedin-square','fa-linux','fa-maxcdn','fa-pagelines','fa-pinterest','fa-pinterest-square','fa-renren','fa-skype','fa-stack-exchange','fa-stack-overflow','fa-trello','fa-tumblr','fa-tumblr-square','fa-twitter','fa-twitter-square','fa-vimeo-square','fa-vk','fa-weibo','fa-windows','fa-xing','fa-xing-square','fa-youtube','fa-youtube-play','fa-youtube-square','fa-ambulance','fa-h-square','fa-hospital-o','fa-medkit','fa-plus-square','fa-stethoscope','fa-user-md','fa-wheelchair');
$fwp_shortcodes = array(
	'about-item' => array(
		'label' 	=> 'About box',
		'content' 	=> array('type'=>'textarea', 'default'=>'Your text here'),
		'params'	=> array(
			  'icon' 	=> array('type'=>'fa-icon', 'default'=>'','label'=>'Icon'),
			  'url' 	=> array('type'=>'text', 'default'=>'','label'=>'URL'),
			  'title'	=> array('type'=>'text', 'default'=>'','label'=>'Title'),
			)
	),
	'absolute' 	=> array(
		'label' 	=> 'Absolute positioned container',
		'content' 	=> false,
		'params'	=> array(
			 'top' 		=> array('type'=>'text', 'default'=>'','label'=>'Top'),
			 'right' 	=> array('type'=>'text', 'default'=>'','label'=>'Right'),
			 'bottom' 	=> array('type'=>'text', 'default'=>'','label'=>'Bottom'),
			 'left' 	=> array('type'=>'text', 'default'=>'','label'=>'Left'),
			)
	),
	'accordion' => array(
		'label' 	=> 'Accordion wrap',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array()
	),
	'item' => array(
		'label' 	=> 'Accordion item',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
				'size' => array('type'=>'text', 'default'=>'', 'label'=>'Item title'), 
				'hide' => array('type'=>'fa-icon', 'default'=>'check', 'label'=>'Item icon'),
			)
	),		
	'grid' => array(
		'label' 	=> 'Bootstrap column',
		'content' 	=> false,
		'params'	=> array(
			'size' 		=> array('type'=>'text', 'default'=>'12', 'label'=>'Grid columns (1-12)'), 
			'type' 		=> array('type'=>'text', 'default'=>'xs', 'label'=>'Grid type (Bootstrap types)'), 
			'hide' 		=> array('type'=>'text', 'default'=>'', 'label'=>'Hide below 800px wide'),
			'class' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Custom class for grid item'),
			'id' 		=> array('type'=>'text', 'default'=>'', 'label'=>'Custom ID for grid item'),
		)
	),
	'box' => array(
		'label' 	=> 'Box',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'nopadding' 	=> array('type'=>'text', 'default'=>'false','label'=>'Box without padding'),
		)
	),	
	'button' => array(
		'label' 	=> 'Button',
		'content' 	=> false,
		'params'	=> array(
			 'href' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Target url'), 
			 'target' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Target type (_self/_blank)'),
			)
	),
	'call-to-action' => array(
		'label' 	=> 'Call to action',
		'content' 	=> false,
		'params'	=> array(
			 'color' 	=> array('type'=>'text', 'default'=>'#eeeeee', 'label'=>'Color'),
			 'pointer' 	=> array('type'=>'text', 'default'=>'bottom', 'label'=>'Arrow position'),
			)
	),
	'center' => array(
		'label' 	=> 'Center content',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'valign' 	=> array('type'=>'text', 'default'=>'','label'=>'Vertical align'),
		)
	),
	'bg' => array(
		'label' 	=> 'Container with background',
		'content' 	=> false,
		'params'	=> array(
				'color' 	=> array('type'=>'color', 'default'=>'','label'=>'Background Color'),
				'image' 	=> array('type'=>'image', 'default'=>'','label'=>'Background Image'),
				'repeat' 	=> array('type'=>'text', 'default'=>'no-repeat','label'=>'Background repeat'),
				'position' 	=> array('type'=>'text', 'default'=>'top center','label'=>'Background position'),
				'padding' 	=> array('type'=>'text', 'default'=>'','label'=>'Padding (px)'),
				'full' 		=> array('type'=>'text', 'default'=>'','label'=>'Full size'),
			)
	),	
	'add-border' => array(
		'label' 	=> 'Container with border',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array()
	),
	'pattern-bg' => array(
		'label' 	=> 'Container with pattern',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
				'style' => array('type'=>'text', 'default'=>'pattern', 'label'=>'Pattern style'), 
				'bg' 	=> array('type'=>'image', 'default'=>'', 'label'=>'Background image'),
			)
	),
	'video-bg' => array(
		'label' 	=> 'Container with video background',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
				'video' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Video URL'),
				'ratio' 	=> array('type'=>'text', 'default'=>'16/9', 'label'=>'Aspect ratio'),
				'controls' 	=> array('type'=>'text', 'default'=>'false', 'label'=>'Show controls'),
				'target' 	=> array('type'=>'text', 'default'=>'self', 'label'=>'Target element'),
				'height' 	=> array('type'=>'text', 'default'=>'auto', 'label'=>'Video height'),
			)
	),
	'desc' => array(
		'label' 	=> 'Description',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'position'	=> array('type'=>'text', 'default'=>'','label'=>'Alignment'),
			'class' 	=> array('type'=>'text', 'default'=>'header','label'=>'CSS Class'),
			'color' 	=> array('type'=>'color', 'default'=>'','label'=>'Text color'),
		)
	),
	'facts' => array(
		'label' 	=> 'Facts item',
		'content' 	=> false,
		'params'	=> array(
				'title' 	=> array('type'=>'text', 'default'=>'','label'=>'Title'),
				'value' 	=> array('type'=>'text', 'default'=>'','label'=>'Value'),
				'animation' => array('type'=>'text', 'default'=>'fadeIn','label'=>'Animation'),
				'delay' 	=> array('type'=>'text', 'default'=>'200','label'=>'Delay'),
			)
	),
	'fa' => array(
		'label' => 'Font awesome icon',
		'content' => false,
		'params'=> array(
			'icon' => array('type'=>'fa-icon', 'default'=>'', 'label'=>'Icon type'), 
			'size' => array('type'=>'text', 'default'=>'', 'label'=>'Icon size'),
		)
	),
	'full_background' => array(
		'label' => 'Full background image',
		'content' => array('type'=>'mce', 'default'=>''),
		'params'=> array(
			'image' => array('type'=>'image', 'default'=>'', 'label'=>'Select image'), 
			'size' => array('type'=>'text', 'default'=>'cover', 'label'=>'Image stretch type'),
			'speed' => array('type'=>'text', 'default'=>'70', 'label'=>'Speed of scrolling background'),
			'direction' => array('type'=>'text', 'default'=>'h', 'label'=>'Direction of scrolling background (h/v)'),
			'scroll' => array('type'=>'text', 'default'=>'false', 'label'=>'Scroll background'),
			'pattern' => array('type'=>'text', 'default'=>'false', 'label'=>'Show pattern above background'),
		)
	),
	
	'map' => array(
		'label' 	=> 'Google map',
		'content' 	=> false,
		'params'	=> array(
				'height' => array('type'=>'text', 'default'=>'','label'=>'Map height'),
			)
	),
	'group' => array(
		'label' 	=> 'Group floating elements/columns',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
	),	
	'header' => array(
		'label' 	=> 'Heading',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'position' 	=> array('type'=>'text', 'default'=>'','label'=>'Alignment'),
			'class' 	=> array('type'=>'text', 'default'=>'header','label'=>'CSS Class'),
			'color' 	=> array('type'=>'color', 'default'=>'','label'=>'Text color'),
		)
	),
	'tab' => array(
		'label' => 'Individual tab',
		'content' => array('type'=>'textarea', 'default'=>'Tab content here'),
		'params'=> array(
			  'class' => array('type'=>'text', 'default'=>'','label'=>'CSS class'),
			  'title' => array('type'=>'text', 'default'=>'','label'=>'Title'),
			)
	),
	'portfolio' => array(
		'label' 	=> 'Isotope portfolio',
		'content' 	=> false,
		'params'	=> array(
			  'orderby' 	=> array('type'=>'text', 'default'=>'menu_order', 'label'=>'Order by'),
			  'cat_orderby' => array('type'=>'text', 'default'=>'count', 'label'=>'Category order'),
			  'hide_empty'	=> array('type'=>'text', 'default'=>'0', 'label'=>'Hide empty categories'),
			  'hide_filters'=> array('type'=>'text', 'default'=>'no', 'label'=>'Hide filters'),
			)
	),
	'members' => array(
		'label' 	=> 'Members',
		'content' 	=> false,
		'params'	=> array(
				'include' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Include'), 
				'exclude' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Exclude'),
				'limit' 	=> array('type'=>'text', 'default'=>'-1', 'label'=>'Number of members'),
				'nav_pos'	=> array('type'=>'text', 'default'=>'top', 'label'=>'Navigation position'),
				'ribbon'	=> array('type'=>'fa-icon', 'default'=>'img-circle', 'label'=>'Icon'),
				'config' 		=> array('type'=>'text', 'default'=>'','label'=>'Custom initialization config for OWL Carousel'),
			)
	),
	'slide' 	=> array(
		'label' 	=> 'New slide for flex slider',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array()
	),
	'sslide' 	=> array(
		'label' 	=> 'New slide for super-slides',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'pattern' 	=> array('type'=>'text', 'default'=>'true', 'label'=>'Use pattern'), 
			'parallax' 	=> array('type'=>'text', 'default'=>'true', 'label'=>'Has parallax'), 
			'bg' 		=> array('type'=>'image', 'default'=>'', 'label'=>'Background image'), 
		)
	),
	'mailchimp' => array(
		'label' 	=> 'Newsletter register form (mailchimp)',
		'content' 	=> array('type'=>'textarea', 'default'=>'Your text here'),
		'params'	=> array(
			 'icon' 		=> array('type'=>'text', 'default'=>'envelope-o', 'label'=>'Font awesome icon'), 
			 'email_label' 	=> array('type'=>'text', 'default'=>__('Your email address','fastwp'), 'label'=> 'Email input label'), 
			 'submit_label' => array('type'=>'text', 'default'=>__('Subscribe','fastwp'), 'label'=> 'Submit button'), 
			)
	),
	'fastwp-page' => array(
		'label' 	=> 'Page content',
		'content' 	=> false,
		'params'	=> array(
				'id' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Page ID'), 
				'slug' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Page slug'),
			)
	),
	'divider' => array(
		'label' 	=> 'Page divider',
		'content' 	=> false,
		'params'	=> array(
				'text' 	=> array('type'=>'text', 'default'=>'','label'=>'Scroll top text'),
				'type' 	=> array('type'=>'text', 'default'=>'','label'=>'Divider type'),
			)
	),
	'phone' => array(
		'label' 	=> 'Phone shortcode',
		'content' 	=> false,
		'params'	=> array(
				'title1' 	=> array('type'=>'text', 'default'=>'photography', 'label'=>'Top left title'), 
				'icon1' 	=> array('type'=>'fa-icon', 'default'=>'camera-retro', 'label'=>'Top left icon'), 
				'ct1' 		=> array('type'=>'text', 'default'=>'Lorem Ipsum', 'label'=>'Top left content'), 
				'title2' 	=> array('type'=>'text', 'default'=>'design', 'label'=>'Top right title'), 
				'icon2' 	=> array('type'=>'fa-icon', 'default'=>'pagelines', 'label'=>'Top right icon'), 
				'ct2' 		=> array('type'=>'text', 'default'=>'Lorem Ipsum', 'label'=>'Top right content'), 
				'title3' 	=> array('type'=>'text', 'default'=>'analystic', 'label'=>'Bottom left title'), 
				'icon3' 	=> array('type'=>'fa-icon', 'default'=>'laptop', 'label'=>'Bottom left icon'), 
				'ct3' 		=> array('type'=>'text', 'default'=>'Lorem Ipsum', 'label'=>'Bottom left content'), 
				'title4' 	=> array('type'=>'text', 'default'=>'online support', 'label'=>'Bottom right title'), 
				'icon4' 	=> array('type'=>'fa-icon', 'default'=>'cloud-upload', 'label'=>'Bottom right icon'),
				'ct4' 		=> array('type'=>'text', 'default'=>'Lorem Ipsum', 'label'=>'Bottom right content'), 
			)
	),
	'pricing-table' => array(
		'label' 	=> 'Pricing table',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			 'title' 		=> array('type'=>'text', 'default'=>'','label'=>'Title'),
			 'price' 		=> array('type'=>'text', 'default'=>'','label'=>'Price'),
			 'currency' 	=> array('type'=>'text', 'default'=>'$','label'=>'Currency symbol'),
			 'period' 		=> array('type'=>'text', 'default'=>__('per month','fastwp'),'label'=>'Period label'),
			 'is_active' 	=> array('type'=>'text', 'default'=>'0','label'=>'Mark this as active'),
			 'url' 			=> array('type'=>'text', 'default'=>'javascript:void(0)','label'=>'URL'),
			)
	),
	'product-info' => array(
		'label' 	=> 'Product info',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
				'b1label' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Left button label'), 
				'b1url'		=> array('type'=>'text', 'default'=>'', 'label'=>'Left button url'),
				'b2label' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Right button label'), 
				'b2url' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Right button url'),
				'background'=> array('type'=>'text', 'default'=>'', 'label'=>'Custom background color'),
			)
	),
	'progress' => array(
		'label' 	=> 'Progress bar',
		'content' 	=> false,
		'params'	=> array(
			  'value' 	=> array('type'=>'text', 'default'=>'','label'=>'Value'),
			  'suffix' 	=> array('type'=>'text', 'default'=>'','label'=>'Suffix'),
			  'title' 	=> array('type'=>'text', 'default'=>'','label'=>'Title'),
			)
	),
	'rain' => array(
		'label' 	=> 'Rain effect over image',
		'content' 	=> false,
		'params'	=> array(
				'image' 	=> array('type'=>'image', 'default'=>'','label'=>'Background Image'),
			)
	),
	'pie-chart' => array(
		'label' 	=> 'Round progress chart',
		'content' 	=> false,
		'params'	=> array(
				'label' 	=> array('type'=>'text', 'default'=>'','label'=>'Label'),
				'percent' 	=> array('type'=>'text', 'default'=>'0','label'=>'Percent'),
			)
	),
	'relative' 	=> array(
		'label' 	=> 'Relative positioned container',
		'content' 	=> array('type'=>'textarea', 'default'=>'Lorem ipsum'),
		'params'	=> array()
	),
	'services' => array(
		'label' 	=> 'Services',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'text_position' => array('type'=>'text', 'default'=>'top','label'=>'Text position'),
			'include' 		=> array('type'=>'text', 'default'=>'','label'=>'Include services'),
			'exclude' 		=> array('type'=>'text', 'default'=>'','label'=>'Exclude services'),
			'use_parallax' 	=> array('type'=>'text', 'default'=>'true','label'=>'Enable parallax'),
			'background' 	=> array('type'=>'image', 'default'=>'false','label'=>'Background image'),
			'limit' 		=> array('type'=>'text', 'default'=>'-1','label'=>'Limit post number'),
			'icon' 			=> array('type'=>'fa-icon', 'default'=>'cogs','label'=>'Service icon'),
			'config' 		=> array('type'=>'text', 'default'=>'','label'=>'Custom initialization config for OWL Carousel'),
			)
	),
	'f-slider' => array(
		'label' 	=> 'Simple flex slider',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'style' 	=> array('type'=>'text', 'default'=>'home-slider', 'label'=>'Slider style'), 
		)
	),
	'super-slides' => array(
		'label' 	=> 'Super slides',
		'content' 	=>  array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
			'pattern' 	=> array('type'=>'text', 'default'=>'true', 'label'=>'Add pattern'),
		)
	),	
	'tabs' => array(
		'label' 	=> 'Tabs wrapper',
		'content' 	=> array('type'=>'textarea', 'default'=>'Insert individual tabs here'),
	),
	'team-member' => array(
		'label' 	=> 'Team member',
		'content' 	=> false,
		'params'	=> array(
			 'id' 		=> array('type'=>'text', 'default'=>'', 'label'=>'Member ID'),
			)
	),
	'testimonials' => array(
		'label' 	=> 'Testimonials',
		'content' 	=> false,
		'params'	=> array(
				'include' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Include'), 
				'exclude' 	=> array('type'=>'text', 'default'=>'', 'label'=>'Exclude'),
				'limit' 	=> array('type'=>'text', 'default'=>'-1', 'label'=>'Number of testimonials'),
			)
	),
	'text-slider' => array(
		'label' 	=> 'Text slider with background',
		'content' 	=> array('type'=>'mce', 'default'=>'Lorem ipsum'),
		'params'	=> array(
				'image' 	=> array('type'=>'image', 'default'=>'', 'label'=>'Background image'), 
				'height' 	=> array('type'=>'image', 'default'=>'', 'label'=>'Slider height'), 
			)
	),
	'twitter-feed' => array(
		'label' 	=> 'Twitter feed',
		'content' 	=> false,
		'params'	=> array(
				'account' 	 => array('type'=>'text', 'default'=>'envato', 'label'=>'Twitter account'), 
				'limit' 	 => array('type'=>'text', 'default'=>'5', 'label'=>'Limit tweets number to'),
				'show_reply' => array('type'=>'text', 'default'=>'1', 'label'=>'Show replies'),
			)
	),
	'space'		=> array(
		'label' 	=> 'Vertical space',
		'content' 	=> false,
		'params'	=> array(
				'height' => array('type'=>'text', 'default'=>'5','label'=>'Space height'),
			)
	),
	'timeline' => array(
		'label' 	=> 'Website timeline',
		'content' 	=> false,
		'params'	=> array(
			 'group' 	=> array('type'=>'text', 'default'=>'site-timeline', 'label'=>'Timeline category'),
		)
	),
);

if(!function_exists('fastwp_save_google_fonts')){
	function fastwp_save_google_fonts($data){
		if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'of_ajax_post_action'):
			$base_google_path 	= "@import url(http://fonts.googleapis.com/css?family=%s);\n";
			$selected_fonts 	= array();
			if(isset($data['font_h1'])){
				$all_fonts 		= array('font_p');
				for($i=1;$i<=5;$i++){
					$all_fonts[] = "font_h$i";
				}
				
				foreach($all_fonts as $font_key){
					$key 					= str_replace('font_', '', $font_key);
					$selected_fonts[$key] 	= @$data[$font_key];
				}
			}

			$file_pointer 	= dirname(dirname(__FILE__)).'/cache/custom-fonts.css';
			$_f 			= fopen($file_pointer,'w');
			$_includes 		= '';
			$_fonts 		= '';
			foreach(array_unique($selected_fonts) as $font){
				if($font == '' || $font == 'none') continue;
				$_includes .= sprintf($base_google_path, urlencode($font));
			}
			foreach($selected_fonts as $selector=>$font){
				if($font == '' || $font == 'none') continue;
				$_fonts 	.= "$selector { font-family:'$font';}\n";
			}
			fwrite($_f, $_includes);
			fwrite($_f, $_fonts);
			fclose($_f);
		endif;
		return $data;
	}
	add_filter('of_options_before_save', 'fastwp_save_google_fonts');
}

if(!function_exists('fastwp_add_google_fonts')){
	function fastwp_add_google_fonts($existing){
		$fonts = array("ABeeZee", "Abel", "Abril Fatface", "Aclonica", "Acme", "Actor", "Adamina", "Advent Pro", "Aguafina Script", "Akronim", "Aladin", "Aldrich", "Alegreya", "Alegreya SC", "Alex Brush", "Alfa Slab One", "Alice", "Alike", "Alike Angular", "Allan", "Allerta", "Allerta Stencil", "Allura", "Almendra", "Almendra Display", "Almendra SC", "Amarante", "Amaranth", "Amatic SC", "Amethysta", "Anaheim", "Andada", "Andika", "Angkor", "Annie Use Your Telescope", "Anonymous Pro", "Antic", "Antic Didone", "Antic Slab", "Anton", "Arapey", "Arbutus", "Arbutus Slab", "Architects Daughter", "Archivo Black", "Archivo Narrow", "Arimo", "Arizonia", "Armata", "Artifika", "Arvo", "Asap", "Asset", "Astloch", "Asul", "Atomic Age", "Aubrey", "Audiowide", "Autour One", "Average", "Average Sans", "Averia Gruesa Libre", "Averia Libre", "Averia Sans Libre", "Averia Serif Libre", "Bad Script", "Balthazar", "Bangers", "Basic", "Battambang", "Baumans", "Bayon", "Belgrano", "Belleza", "BenchNine", "Bentham", "Berkshire Swash", "Bevan", "Bigelow Rules", "Bigshot One", "Bilbo", "Bilbo Swash Caps", "Bitter", "Black Ops One", "Bokor", "Bonbon", "Boogaloo", "Bowlby One", "Bowlby One SC", "Brawler", "Bree Serif", "Bubblegum Sans", "Bubbler One", "Buda", "Buenard", "Butcherman", "Butterfly Kids", "Cabin", "Cabin Condensed", "Cabin Sketch", "Caesar Dressing", "Cagliostro", "Calligraffitti", "Cambo", "Candal", "Cantarell", "Cantata One", "Cantora One", "Capriola", "Cardo", "Carme", "Carrois Gothic", "Carrois Gothic SC", "Carter One", "Caudex", "Cedarville Cursive", "Ceviche One", "Changa One", "Chango", "Chau Philomene One", "Chela One", "Chelsea Market", "Chenla", "Cherry Cream Soda", "Cherry Swash", "Chewy", "Chicle", "Chivo", "Cinzel", "Cinzel Decorative", "Clicker Script", "Coda", "Coda Caption", "Codystar", "Combo", "Comfortaa", "Coming Soon", "Concert One", "Condiment", "Content", "Contrail One", "Convergence", "Cookie", "Copse", "Corben", "Courgette", "Cousine", "Coustard", "Covered By Your Grace", "Crafty Girls", "Creepster", "Crete Round", "Crimson Text", "Croissant One", "Crushed", "Cuprum", "Cutive", "Cutive Mono", "Damion", "Dancing Script", "Dangrek", "Dawning of a New Day", "Days One", "Delius", "Delius Swash Caps", "Delius Unicase", "Della Respira", "Denk One", "Devonshire", "Didact Gothic", "Diplomata", "Diplomata SC", "Domine", "Donegal One", "Doppio One", "Dorsa", "Dosis", "Dr Sugiyama", "Droid Sans", "Droid Sans Mono", "Droid Serif", "Duru Sans", "Dynalight", "EB Garamond", "Eagle Lake", "Eater", "Economica", "Electrolize", "Elsie", "Elsie Swash Caps", "Emblema One", "Emilys Candy", "Engagement", "Englebert", "Enriqueta", "Erica One", "Esteban", "Euphoria Script", "Ewert", "Exo", "Expletus Sans", "Fanwood Text", "Fascinate", "Fascinate Inline", "Faster One", "Fasthand", "Federant", "Federo", "Felipa", "Fenix", "Finger Paint", "Fjalla One", "Fjord One", "Flamenco", "Flavors", "Fondamento", "Fontdiner Swanky", "Forum", "Francois One", "Freckle Face", "Fredericka the Great", "Fredoka One", "Freehand", "Fresca", "Frijole", "Fruktur", "Fugaz One", "GFS Didot", "GFS Neohellenic", "Gafata", "Galdeano", "Galindo", "Gentium Basic", "Gentium Book Basic", "Geo", "Geostar", "Geostar Fill", "Germania One", "Gilda Display", "Give You Glory", "Glass Antiqua", "Glegoo", "Gloria Hallelujah", "Goblin One", "Gochi Hand", "Gorditas", "Goudy Bookletter 1911", "Graduate", "Grand Hotel", "Gravitas One", "Great Vibes", "Griffy", "Gruppo", "Gudea", "Habibi", "Hammersmith One", "Hanalei", "Hanalei Fill", "Handlee", "Hanuman", "Happy Monkey", "Headland One", "Henny Penny", "Herr Von Muellerhoff", "Holtwood One SC", "Homemade Apple", "Homenaje", "IM Fell DW Pica", "IM Fell DW Pica SC", "IM Fell Double Pica", "IM Fell Double Pica SC", "IM Fell English", "IM Fell English SC", "IM Fell French Canon", "IM Fell French Canon SC", "IM Fell Great Primer", "IM Fell Great Primer SC", "Iceberg", "Iceland", "Imprima", "Inconsolata", "Inder", "Indie Flower", "Inika", "Irish Grover", "Istok Web", "Italiana", "Italianno", "Jacques Francois", "Jacques Francois Shadow", "Jim Nightshade", "Jockey One", "Jolly Lodger", "Josefin Sans", "Josefin Slab", "Joti One", "Judson", "Julee", "Julius Sans One", "Junge", "Jura", "Just Another Hand", "Just Me Again Down Here", "Kameron", "Karla", "Kaushan Script", "Kavoon", "Keania One", "Kelly Slab", "Kenia", "Khmer", "Kite One", "Knewave", "Kotta One", "Koulen", "Kranky", "Kreon", "Kristi", "Krona One", "La Belle Aurore", "Lancelot", "Lato", "League Script", "Leckerli One", "Ledger", "Lekton", "Lemon", "Libre Baskerville", "Life Savers", "Lilita One", "Limelight", "Linden Hill", "Lobster", "Lobster Two", "Londrina Outline", "Londrina Shadow", "Londrina Sketch", "Londrina Solid", "Lora", "Love Ya Like A Sister", "Loved by the King", "Lovers Quarrel", "Luckiest Guy", "Lusitana", "Lustria", "Macondo", "Macondo Swash Caps", "Magra", "Maiden Orange", "Mako", "Marcellus", "Marcellus SC", "Marck Script", "Margarine", "Marko One", "Marmelad", "Marvel", "Mate", "Mate SC", "Maven Pro", "McLaren", "Meddon", "MedievalSharp", "Medula One", "Megrim", "Meie Script", "Merienda", "Merienda One", "Merriweather", "Metal", "Metal Mania", "Metamorphous", "Metrophobic", "Michroma", "Milonga", "Miltonian", "Miltonian Tattoo", "Miniver", "Miss Fajardose", "Modern Antiqua", "Molengo", "Molle", "Monda", "Monofett", "Monoton", "Monsieur La Doulaise", "Montaga", "Montez", "Montserrat", "Montserrat Alternates", "Montserrat Subrayada", "Moul", "Moulpali", "Mountains of Christmas", "Mouse Memoirs", "Mr Bedfort", "Mr Dafoe", "Mr De Haviland", "Mrs Saint Delafield", "Mrs Sheppards", "Muli", "Mystery Quest", "Neucha", "Neuton", "New Rocker", "News Cycle", "Niconne", "Nixie One", "Nobile", "Nokora", "Norican", "Nosifer", "Nothing You Could Do", "Noticia Text", "Nova Cut", "Nova Flat", "Nova Mono", "Nova Oval", "Nova Round", "Nova Script", "Nova Slim", "Nova Square", "Numans", "Nunito", "Odor Mean Chey", "Offside", "Old Standard TT", "Oldenburg", "Oleo Script", "Oleo Script Swash Caps", "Open Sans", "Open Sans Condensed", "Oranienbaum", "Orbitron", "Oregano", "Orienta", "Original Surfer", "Oswald", "Over the Rainbow", "Overlock", "Overlock SC", "Ovo", "Oxygen", "Oxygen Mono", "PT Mono", "PT Sans", "PT Sans Caption", "PT Sans Narrow", "PT Serif", "PT Serif Caption", "Pacifico", "Paprika", "Parisienne", "Passero One", "Passion One", "Patrick Hand", "Patua One", "Paytone One", "Peralta", "Permanent Marker", "Petit Formal Script", "Petrona", "Philosopher", "Piedra", "Pinyon Script", "Pirata One", "Plaster", "Play", "Playball", "Playfair Display", "Playfair Display SC", "Podkova", "Poiret One", "Poller One", "Poly", "Pompiere", "Pontano Sans", "Port Lligat Sans", "Port Lligat Slab", "Prata", "Preahvihear", "Press Start 2P", "Princess Sofia", "Prociono", "Prosto One", "Puritan", "Purple Purse", "Quando", "Quantico", "Quattrocento", "Quattrocento Sans", "Questrial", "Quicksand", "Quintessential", "Qwigley", "Racing Sans One", "Radley", "Raleway", "Raleway Dots", "Rambla", "Rammetto One", "Ranchers", "Rancho", "Rationale", "Redressed", "Reenie Beanie", "Revalia", "Ribeye", "Ribeye Marrow", "Righteous", "Risque", "Roboto", "Roboto Condensed", "Rochester", "Rock Salt", "Rokkitt", "Romanesco", "Ropa Sans", "Rosario", "Rosarivo", "Rouge Script", "Ruda", "Rufina", "Ruge Boogie", "Ruluko", "Rum Raisin", "Ruslan Display", "Russo One", "Ruthie", "Rye", "Sacramento", "Sail", "Salsa", "Sanchez", "Sancreek", "Sansita One", "Sarina", "Satisfy", "Scada", "Schoolbell", "Seaweed Script", "Sevillana", "Seymour One", "Shadows Into Light", "Shadows Into Light Two", "Shanti", "Share", "Share Tech", "Share Tech Mono", "Shojumaru", "Short Stack", "Siemreap", "Sigmar One", "Signika", "Signika Negative", "Simonetta", "Sirin Stencil", "Six Caps", "Skranji", "Slackey", "Smokum", "Smythe", "Sniglet", "Snippet", "Snowburst One", "Sofadi One", "Sofia", "Sonsie One", "Sorts Mill Goudy", "Source Code Pro", "Source Sans Pro", "Special Elite", "Spicy Rice", "Spinnaker", "Spirax", "Squada One", "Stalemate", "Stalinist One", "Stardos Stencil", "Stint Ultra Condensed", "Stint Ultra Expanded", "Stoke", "Strait", "Sue Ellen Francisco", "Sunshiney", "Supermercado One", "Suwannaphum", "Swanky and Moo Moo", "Syncopate", "Tangerine", "Taprom", "Telex", "Tenor Sans", "Text Me One", "The Girl Next Door", "Tienne", "Tinos", "Titan One", "Titillium Web", "Trade Winds", "Trocchi", "Trochut", "Trykker", "Tulpen One", "Ubuntu", "Ubuntu Condensed", "Ubuntu Mono", "Ultra", "Uncial Antiqua", "Underdog", "Unica One", "UnifrakturCook", "UnifrakturMaguntia", "Unkempt", "Unlock", "Unna", "VT323", "Vampiro One", "Varela", "Varela Round", "Vast Shadow", "Vibur", "Vidaloka", "Viga", "Voces", "Volkhov", "Vollkorn", "Voltaire", "Waiting for the Sunrise", "Wallpoet", "Walter Turncoat", "Warnes", "Wellfleet", "Wendy One", "Wire One", "Yanone Kaffeesatz", "Yellowtail", "Yeseva One", "Yesteryear", "Zeyada");
		foreach($fonts as $font){
			$existing[urlencode($font)] = $font;
		}
		return $existing;
	}
	add_filter('fastwp_add_google_fonts', 'fastwp_add_google_fonts');
}
