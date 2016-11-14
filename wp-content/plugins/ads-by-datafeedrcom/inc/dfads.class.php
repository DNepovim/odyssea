<?php

/**
 * This class is responsible for querying the ads and
 * formatting their output.
 */
class DFADS {

	private $args;

	// This is called via the dfads() function.  It puts everything in motion.
	function get_ads( $args ) {
		
		$this->set_args( $args );
				
		// Return JS output for avoiding caching issues.
		// Return this before running query() to avoid:
		// - The query being run twice.
		// - Impressions being counted twice.
		if ( $this->args['return_javascript'] == '1' ) {
			return $this->get_javascript();
		}
		
		// Get ads.
		$ads = $this->query();
		
		if ( empty($ads) ) { return false; }
		
		// Count impressions.
		$this->update_impression_count( $ads );
		
		// Return user's own callback function.
		if ( function_exists( $this->args['callback_function'] ) ) {
			return call_user_func_array($this->args['callback_function'], array( $ads, $this->args ));
		}
				
		// Return default output function.
		return $this->output( $ads, $this->args );
	}
	
	// Set up default arguements that can be modified in dfads().
	function set_args( $args ) {
	
		// Undo anything that the text editor may have added.
		$args = str_replace ( 
			array( "&amp;", "&lt;", "&gt;", "&quote;", "%2C" ), 
			array( "&",     "",     "",     "\"",      ","   ), 
			$args 
		);
	
		// Now reformat
		$args = htmlentities( $args );
		
		// Create array of values.
		$args = explode("&amp;", $args);

		$new_args = array();
		foreach ($args as $arg) {
			$arr = explode( "=", $arg, 2 );
			$k = $arr[0];
			$v = $arr[1];
			// This section gets rid of the pesky "#038;" charcters WP changes "&" to.
			$k = str_replace( array( "#038;" ), array( "" ), $k );
			$new_args[$k] = $v;
		}

		$defaults = array (
			'groups' 			=> '-1',
			'limit' 			=> '-1',
			'orderby' 			=> 'random',
			'order' 			=> 'ASC',
			'container_id'    	=> '',
			'container_html' 	=> 'div',
			'container_class' 	=> '',
			'ad_html' 			=> 'div',
			'ad_class' 			=> '',
			'callback_function' => '',
			'return_javascript' => '',
		);
				
		$this->args = wp_parse_args( $new_args, $defaults );
	}
	
	// Build the SQL query to get the ads.
	function query() {
		// http://codex.wordpress.org/Displaying_Posts_Using_a_Custom_Select_Query
		global $wpdb;
		$tax_sql = $this->sql_get_taxonomy();
		$tax_join = $tax_sql['JOIN'];
		$tax_and = $tax_sql['AND'];
		$limit = $this->sql_get_limit();
		$orderby = $this->sql_get_orderby();
		$order = $this->sql_get_order();
		$sql = "
			SELECT
				p.*, 
				imp_count.meta_value AS ad_imp_count,
				imp_limit.meta_value AS ad_imp_limit,
				start_date.meta_value AS ad_start_date,
				end_date.meta_value AS ad_end_date
			FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta AS imp_limit 
				ON p.ID = imp_limit.post_id 	
				AND imp_limit.meta_key = '_dfads_impression_limit'
			LEFT JOIN $wpdb->postmeta AS imp_count 
				ON p.ID = imp_count.post_id
				AND imp_count.meta_key = '".DFADS_METABOX_PREFIX."impression_count'
			LEFT JOIN $wpdb->postmeta AS start_date 
				ON p.ID = start_date.post_id 	
				AND start_date.meta_key = '_dfads_start_date'
			LEFT JOIN $wpdb->postmeta AS end_date 
				ON p.ID = end_date.post_id
				AND end_date.meta_key = '_dfads_end_date'
			$tax_join
			WHERE p.post_status = 'publish'
			AND p.post_type = 'dfads'
			AND ( 
				CAST(imp_limit.meta_value AS UNSIGNED) = 0 
				OR CAST(imp_count.meta_value AS UNSIGNED) < CAST(imp_limit.meta_value AS UNSIGNED) 
				OR CAST(imp_count.meta_value AS UNSIGNED) IS NULL
			)
			AND (
				CAST(start_date.meta_value AS UNSIGNED) IS NULL
				OR ".time()." >= CAST(start_date.meta_value AS UNSIGNED)
			)
			AND (
				CAST(end_date.meta_value AS UNSIGNED) IS NULL
				OR ".time()." <= CAST(end_date.meta_value AS UNSIGNED)
			)
			$tax_and
			GROUP BY p.ID
			ORDER BY $orderby $order 
			LIMIT $limit
		";

		return $wpdb->get_results( $sql, OBJECT );		
	}
	
	// Build the taxonomy portion of the SQL statement.
	function sql_get_taxonomy() {
		global $wpdb;
		$sql = array();
		$sql['JOIN'] = '';
		$sql['AND'] = '';
		
		if ( !$group_ids = $this->get_group_term_ids( $this->args['groups'] ) ) {
			return $sql;
		}
		
		$ids = implode( ",", $group_ids );
		$sql['JOIN'] = " LEFT JOIN $wpdb->term_relationships AS tr ON (p.ID = tr.object_id) LEFT JOIN $wpdb->term_taxonomy AS tax ON (tr.term_taxonomy_id = tax.term_taxonomy_id) ";
		$sql['AND'] = " AND tax.taxonomy = 'dfads_group' AND tax.term_id IN($ids) ";
		return $sql;
	}

	// Build the LIMIT portion of the SQL statement.
	function sql_get_limit() {
		return ($this->args['limit'] == '-1') ? 999 : intval($this->args['limit']);
	}
	
	// Build the ORDER BY portion of the SQL statement.
	function sql_get_orderby() {
		$orderby_defaults = $this->orderby_array();
		return $orderby_defaults[$this->args['orderby']]['sql'];
	}

	// Build the ORDER portion of the SQL statement.	
	function sql_get_order() {
		return ($this->args['order'] == 'ASC') ? 'ASC' : 'DESC';
	}
	
	// A set of possibly values for the ORDER BY part of the SQL query.
	function orderby_array() {
		return array(
			'ID' => array( 'name'=>'ID', 'sql'=>'p.ID' ),
			'post_title' => array( 'name'=>'Ad Title', 'sql'=>'p.post_title' ),
			'post_date' => array( 'name'=>'Date Created', 'sql'=>'p.post_date' ),
			'post_modified' => array( 'name'=>'Date Modified', 'sql'=>'p.post_modified' ),
			'menu_order' => array( 'name'=>'Menu Order', 'sql'=>'p.menu_order' ),
			'impression_count' => array( 'name'=>'Impression Count', 'sql'=>'CAST(imp_count.meta_value AS UNSIGNED)' ),
			'impression_limit' => array( 'name'=>'Impression Limit', 'sql'=>'CAST(imp_limit.meta_value AS UNSIGNED)' ),
			'start_date' => array( 'name'=>'Start Date', 'sql'=>'CAST(start_date.meta_value AS UNSIGNED)' ),
			'end_date' => array( 'name'=>'End Date', 'sql'=>'CAST(end_date.meta_value AS UNSIGNED)' ),
			'random' => array( 'name'=>'Random', 'sql'=>'RAND()' ),
		);
	}
	
	// This loops through all ads returned and updates their impression count (default: if user != admin).
	function update_impression_count( $ads ) {
		
		// Don't count if admin AND admin impressions don't count.
		if ( current_user_can('level_10') ) {
			$output = get_option( 'dfads-settings' );
			if ( !isset( $output['dfads_enable_count_for_admin'] ) || $output['dfads_enable_count_for_admin'] != '1' ) {
				return;
			}
		}
		
		// Don't count if we've already set a block_id.
		// This is to handle impresion counts for when 'return_javascript' equals 1
		// because 'return_javascript' returns a <script> and <noscript> tag and
		// we have to avoid the impression being counted twice.  So we store this
		// ad groups unique "block_id" as a transient value to check subsequent calls
		// for the same ad block.  If it's already set, we don't count again.
		if ( get_transient( 'dfad_'.$this->args['_block_id'] ) ) {
			return;
		} else {
			set_transient ( 'dfad_'.$this->args['_block_id'], true, 5 );
		}
		
		foreach ($ads as $ad) {
			update_post_meta($ad->ID, DFADS_METABOX_PREFIX.'impression_count', (intval($ad->ad_imp_count)+1));
		}
		
	}
	
	// This formats and outputs the ads.  This is overridable if the user has defined $this->args['callback_function']
	function output( $ads, $args ) {
		
		$ad_count = count( $ads );
		$i = 1;
		$html = '';
		
		// Determine if we should include tags for containers and ad wrappers.
		// If 'none', then we remove the tag from output.
		$container_html = ( $args['container_html'] == 'none' ) ? '' : $args['container_html'];
		$ad_html = ( $args['ad_html'] == 'none' ) ? '' : $args['ad_html'];
		
		// If contain_html is not empty, get container's opening tag.
		if ( $container_html != '') {
			$html .= $this->open_tag( $container_html, $args['container_class'], $args['container_id'] );
		}
		
		// Loop through ads.
		foreach ($ads as $ad) {
		
			$first_last = ' ';
			if ( $i == 1 ) {
				$first_last = ' dfad_first ';
			} elseif ( $i == $ad_count ) {
				$first_last = ' dfad_last ';
			}
			
			// If ad_html is not empty, get the ads's opening tag.
			if ( $ad_html != '') {
				$html .= $this->open_tag( $ad_html, 'dfad dfad_pos_'.$i.$first_last.$args['ad_class'], $args['container_id'].'_ad_'.$ad->ID );
			}
			
			// Get ad content.
			$html .= $ad->post_content;
			
			// If ad_html is not empty, get the ads's closing tag.
			if ( $ad_html != '') {
				$html .= $this->close_tag( $ad_html );
			}
			
			$i++;
		}
		
		// If contain_html is not empty, get container's closing tag.
		if ( $container_html != '') {
			$html .= $this->close_tag( $container_html );
		}
		
		return $html;
	}
	
	function get_javascript() {
		
		$id = ( $this->args['container_id'] != '' ) ? $this->args['container_id'] : 'df'.$this->generate_random_string( 5 );
		
		// Set 'return_javascript' to '0' or else we end up with an infinite loop.
		$args = $this->args;
		$args['return_javascript'] = '0';
		$args['_block_id'] = $id;
		$args['container_html'] = 'none'; // Set to 'none' so we don't display the container HTML twice.
		
		return '
		<'.$this->args['container_html'].' id="'.$id.'" class="dfads-javascript-load"></'.$this->args['container_html'].'>
		<script>
		(function($) { 
			$("#'.$id .'").load("'.admin_url( 'admin-ajax.php?action=dfads_ajax_load_ads&'.http_build_query( $args ) ).'" );			
		})( jQuery );
		</script>
		<noscript>'.dfads( http_build_query( $args )  ).'</noscript>
		';
		
	}
	
	/**
	 * This gets the group IDs.
	 * 
	 * $groups could be any of the following:
	 * - ''
	 * - 'groups='
	 * - 'groups=1'
	 * - 'groups=sidebar'
	 * - 'groups=1,2'
	 * - 'groups=sidebar,header'
	 * 
	 * "-1" is equivalent to "All".
	 */
	function get_group_term_ids( $groups=false ) {
		
		if (!$groups || $groups == '-1' || $groups == '') { return false; }
	
		$groups = explode(",", $groups);
		$group_ids = array();
	
		foreach( $groups as $group ) {
			
			// Try to get term ID from id.
			if ($group_obj = get_term_by( 'id', $group, 'dfads_group' )) {
				$group_ids[] = intval($group_obj->term_id);
				continue;
			}
		
			// Try to get term ID from slug.
			if ($group_obj = get_term_by( 'slug', $group, 'dfads_group' )) {
				$group_ids[] = intval($group_obj->term_id);
				continue;
			}
		
			// Try to get term ID from name.
			if ($group_obj = get_term_by( 'name', $group, 'dfads_group' )) {
				$group_ids[] = intval($group_obj->term_id);
				continue;
			}
		}
	
		if (!empty($group_ids)) {
			return $group_ids;
		}
	
		return false;
	}
	
	// Formats an opening HTML tag with CSS classes and IDs.
	function open_tag( $tag='div', $class='', $id='' ) {
		$tag = ($tag == '') ? 'div' : trim($tag);
		$class = ($class != '') ? ' class="'.trim(esc_attr($class)).'"' : '';
		$id = ($id != '') ? ' id="'.trim(esc_attr($id)).'"' : '';
		return '<'.$tag.$class.$id.'>';
	}

	// Formats a closing HTML tag.
	function close_tag( $tag='div' ) {
		$tag = ($tag == '') ? 'div' : trim($tag);
		return '</'.$tag.'>';
	}
	
	// Helper function (http://stackoverflow.com/a/4356295)
	function generate_random_string( $length=10 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
}
