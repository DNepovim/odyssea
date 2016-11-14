<?php
/**
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
add_action( 'init', 'fastwp_create_custom_post_types' );
function fastwp_create_custom_post_types() {
	global $smof_data;
	register_post_type( 'fwp_testimonial',
		array(
			'labels' => array(
				'name' => __( 'Testimonials' ,'fastwp'),
				'singular_name' => __( 'Testimonial' ,'fastwp')
			),
		'public' 				=> true,
		'has_archive' 			=> false,
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'rewrite' 				=> array( 'slug' => _x( 'testimonial', 'URL slug', 'fastwp' ) ),
		)
	);
		register_post_type( 'fwp_service',
		array(
			'labels' => array(
				'name' => __( 'Services' ,'fastwp'),
				'singular_name' => __( 'Service' ,'fastwp')
			),
		'public' 				=> true,
		'has_archive' 			=> false,
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'rewrite' 				=> array( 'slug' => _x( 'service', 'URL slug', 'fastwp' ) ),
		)
	);
	
	register_post_type( 'fwp_team',
		array(
			'labels' => array(
				'name' => __( 'Members' ,'fastwp'),
				'singular_name' => __( 'Member' ,'fastwp')
			),
		'public' 				=> true,
		'has_archive' 			=> false,
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'rewrite' 				=> array( 'slug' => _x( 'member', 'URL slug', 'fastwp' ) ),
		'supports' 				=> array( 'title', 'editor', 'thumbnail' ),
		)
	);
	
	register_post_type( 'fwp_timeline',
		array(
			'labels' => array(
				'name' => __( 'Timeline' ,'fastwp'),
				'singular_name' => __( 'Timeline' ,'fastwp')
			),
		'public' 				=> true,
		'has_archive' 			=> false,
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> false,
		'rewrite' 				=> array( 'slug' => _x( 'timeline', 'URL slug', 'fastwp' ) ),
		)
	);
	register_taxonomy(
		'timeline_group',
		'fwp_timeline',
		array(
			'hierarchical' => true,
			'label' => 'Timeline group',
			'query_var' => true,
			'show_in_nav_menus'		=> false,
			'rewrite' => array('slug' => 'timeline-groups')
		)
	);
	
	
	$slug = (isset($smof_data['portfolio_permalink']) && !empty($smof_data['portfolio_permalink']))? $smof_data['portfolio_permalink']:'project';
	register_post_type( 'fwp_portfolio',
		array(
			'labels' => array(
				'name' => __( 'Portfolio' ,'fastwp'),
				'singular_name' => __( 'Portfolio' ,'fastwp')
			),
		'public' 				=> true,
		'has_archive' 			=> false,
		'exclude_from_search'	=> true,
		'show_in_nav_menus'		=> true,
		'rewrite' 				=> array( 'slug' => _x( $slug, 'URL slug', 'fastwp' ) ),
		'supports' 				=> array( 'title', 'editor', 'thumbnail' )
		)
	);
}

add_action('of_save_options_before', 'fastwp_flush_rules');
add_action('of_save_options_after', 'fastwp_flush_rules');
function fastwp_flush_rules(){
	flush_rewrite_rules();
}

add_action( 'init', 'fastwp_create_custom_taxonomy' );
function fastwp_create_custom_taxonomy(){

  $labels = array(
    'name'                => _x( 'Category', 'taxonomy general name','fastwp' ),
    'singular_name'       => _x( 'Category', 'taxonomy singular name','fastwp' ),
    'search_items'        => __( 'Search Categories','fastwp' ),
    'all_items'           => __( 'All Categories','fastwp' ),
    'parent_item'         => __( 'Parent Category','fastwp' ),
    'parent_item_colon'   => __( 'Parent Category:','fastwp' ),
    'edit_item'           => __( 'Edit Category','fastwp' ), 
    'update_item'         => __( 'Update Category','fastwp' ),
    'add_new_item'        => __( 'Add New Category','fastwp' ),
    'new_item_name'       => __( 'New Category','fastwp' ),
    'menu_name'           => __( 'Categories','fastwp' )
  ); 	

  $args = array(
    'hierarchical'        => true,
    'labels'              => $labels,
    'show_ui'             => true,
    'show_admin_column'   => true,
    'query_var'           => true,
	'show_in_nav_menus'		=> false,
    'rewrite'             => array( 'slug' => 'portfolio-category' )
  );

  register_taxonomy( 'portfolio-category', 'fwp_portfolio', $args );

}