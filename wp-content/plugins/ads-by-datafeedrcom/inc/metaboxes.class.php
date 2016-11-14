<?php

/**
 * This class adds the necessary meta boxes to the
 * "Add Ad" page.
 */
class DFADS_Meta_Boxes {
	
	// Call the necessary hooks.
	function DFADS_Meta_Boxes() {
		add_action( 'init', array( $this, 'initialize_cmb_meta_boxes' ) );
		add_filter( 'cmb_meta_boxes', array( $this, 'date_range' ) );
		add_filter( 'cmb_meta_boxes', array( $this, 'impression_limit' ) );
		add_filter( 'cmb_validate_text', array( $this, 'validate_impression_limit' ), 10, 3 );
	}
	
	// Load Custom Meta Boxes For WordPress Plugin library.
	// https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
	function initialize_cmb_meta_boxes() {
		if ( DFADS_Admin::is_dfads_page() ) {
			if ( !class_exists( 'cmb_Meta_Box' ) ) {
				require_once( DFADS_PLUGIN_PATH . 'lib/metabox/init.php' );
			}
		}
	}
	
	// Add the date range meta box.
	function date_range( $meta_boxes ) {

		$meta_boxes[] = array(
			'id' => 'dfads_dates',
			'title' => 'Date Range',
			'pages' => array( 'dfads' ),
			'context' => 'normal',
			'priority' => 'default',
			'show_names' => true,
			'fields' => array(
				array(
					'name' => 'Start Date',
					'desc' => 'Date this ad should start appearing. Leave blank for immediately. Format: MM/DD/YYYY.',
					'id' => DFADS_METABOX_PREFIX . 'start_date',
					'type' => 'text_date_timestamp'
				),
				array(
					'name' => 'End Date',
					'desc' => 'Date this ad should stop appearing. Leave blank for never. Format: MM/DD/YYYY',
					'id' => DFADS_METABOX_PREFIX . 'end_date',
					'type' => 'text_date_timestamp'
				),
			),
		);
		return $meta_boxes;
	}

	// Add impression limit meta box.
	function impression_limit( $meta_boxes ) {

		$meta_boxes[] = array(
			'id' => 'dfads_impressions',
			'title' => 'Impression Limit',
			'pages' => array('dfads'),
			'context' => 'normal',
			'priority' => 'default',
			'show_names' => false,
			'fields' => array(
				array(
					'name' => 'Impressions',
					'desc' => 'Limit the number of impressions this ad is allowed to have.  Leave empty or 0 for no limit',
					'id'   => DFADS_METABOX_PREFIX . 'impression_limit',
					'type' => 'text',
				),
			),
		);

		return $meta_boxes;
	}

	// Validate impression limit.
	function validate_impression_limit( $new, $post_id, $field ) {
		if ($field['id'] != DFADS_METABOX_PREFIX . 'impression_limit') {
			return $new;
		}
		return $this->get_positive_int( $new );
	}
	
	// Gets a positive integer of impression limit (remove all non-numeric characters).
	function get_positive_int( $new ) {
		$new = trim( $new );
		if ( $new == '' || empty ( $new ) ) {
			return '0';
		}
		$new = preg_replace( "/[^0-9]/", "", $new );
		return ltrim( $new, '0' );
	}
}

new DFADS_Meta_Boxes();