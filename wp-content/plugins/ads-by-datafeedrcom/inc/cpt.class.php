<?php

/**
 * This class adds the necessary Custom Post Type ("Ads") and
 * adds the Custom Taxonomy ("Groups") to WP.
 */
class DFADS_Post_Type_Taxonomy {
	
	// Call necessary hooks.
	public function __construct () {
		add_action( 'init', array( $this,'create_post_type' ) );
		add_action( 'init', array( $this,'create_taxonomy' ) );
	}
	
	// Create the post type.
	function create_post_type() {
		$labels = array(
		    'name' => _x( 'Ads', DFADS_CONTEXT ),
		    'singular_name' => _x( 'Ad', DFADS_CONTEXT ),
		    'add_new' => _x( 'Add New Ad', DFADS_CONTEXT ),
		    'all_items' => _x( 'All Ads', DFADS_CONTEXT ),
		    'add_new_item' => _x( 'Add New Ad', DFADS_CONTEXT ),
		    'edit_item' => _x( 'Edit Ad', DFADS_CONTEXT ),
		    'new_item' => _x( 'New Ad', DFADS_CONTEXT ),
		    'view_item' => _x( 'View Ad', DFADS_CONTEXT ),
		    'search_items' => _x( 'Search Ads', DFADS_CONTEXT ),
		    'not_found' =>  _x( 'No ads found', DFADS_CONTEXT ),
		    'not_found_in_trash' => _x( 'No ads found in trash', DFADS_CONTEXT ),
		    'parent_item_colon' => _x( 'Parent Ad:', DFADS_CONTEXT ),
		    'menu_name' => _x( 'Ads', DFADS_CONTEXT )
		);
		$args = array(
			'labels' => $labels,
			'description' => "A content type for adding advertisements.",
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'show_ui' => true, 
			'show_in_nav_menus' => true, 
			'show_in_menu' => true,
			'show_in_admin_bar' => true,
			'menu_position' => 20,
			'menu_icon' => DFADS_PLUGIN_URL.'img/datafeedr-menu-icon.png',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'has_archive' => false,
			'rewrite' => false,
			'query_var' => false,
			'can_export' => true
		); 
		register_post_type( 'dfads', $args);
	}
	
	// Create the taxonomy.
	function create_taxonomy() {
		
		$labels = array( 
			'name' => _x( 'Group', DFADS_CONTEXT ),
			'singular_name' => _x( 'Group', DFADS_CONTEXT ),
			'search_items' => _x( 'Search Groups', DFADS_CONTEXT ),
			'popular_items' => _x( 'Popular Groups', DFADS_CONTEXT ),
			'all_items' => _x( 'All Groups', DFADS_CONTEXT ),
			'parent_item' => _x( 'Parent Group', DFADS_CONTEXT ),
			'parent_item_colon' => _x( 'Parent Group:', DFADS_CONTEXT ),
			'edit_item' => _x( 'Edit Group', DFADS_CONTEXT ),
			'update_item' => _x( 'Update Group', DFADS_CONTEXT ),
			'add_new_item' => _x( 'Add New Group', DFADS_CONTEXT ),
			'new_item_name' => _x( 'New Group', DFADS_CONTEXT ),
			'separate_items_with_commas' => _x( 'Separate groups with commas', DFADS_CONTEXT ),
			'add_or_remove_items' => _x( 'Add or remove Group', DFADS_CONTEXT ),
			'choose_from_most_used' => _x( 'Choose from most used groups', DFADS_CONTEXT ),
			'menu_name' => _x( 'Groups', DFADS_CONTEXT ),
		);

		$args = array( 
			'labels' => $labels,
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_tagcloud' => false,
			'show_admin_column' => true,
			'hierarchical' => true,
			'rewrite' => true,
			'query_var' => true
		);

		register_taxonomy( 'dfads_group', array( 'dfads' ), $args );
	}
}

new DFADS_Post_Type_Taxonomy();