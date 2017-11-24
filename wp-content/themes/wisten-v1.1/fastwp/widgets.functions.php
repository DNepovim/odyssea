<?php
/**
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

 
 
class IonutFlickr_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'wisten-flickr-gallery-widget', 'Flickr Gallery Widget', array(
			'description' => 'Display up to 20 of your latest Flickr submissions in your sidebar.',
		) );
	}

	/**
	 * Displays the widget contents.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		$photos = $this->get_photos( array(
			'username' => $instance['username'],
			'count' => $instance['count'],
			'tags' => $instance['tags'],
		) );

		if ( is_wp_error( $photos ) ) {
			echo $photos->get_error_message();
		} else {
			foreach ( $photos as $photo ) {
				$link = esc_url( $photo->link );
				$src = esc_url( $photo->media->m );
				$title = esc_attr( $photo->title );

				$item = sprintf( '<a href="%s"><img src="%s" alt="%s" /></a>', $link, $src, $title );
				$item = sprintf( '<div class="fastwp-flickr-item">%s</div>', $item );
				echo $item;
			}
		}

		echo $args['after_widget'];
	}

	private function get_photos( $args = array() ) {
		$transient_key = md5( 'fastwp-flickr-cache-' . print_r( $args, true ) );
		$cached = get_transient( $transient_key );
		if ( $cached )
			return $cached;

		$username = isset( $args['username'] ) ? $args['username'] : '';
		$tags = isset( $args['tags'] ) ? $args['tags'] : '';
		$count = isset( $args['count'] ) ? absint( $args['count'] ) : 10;
		$query = array(
			'tagmode' => 'any',
			'tags' => $tags,
		);

		// If username is an RSS feed
		if ( preg_match( '#^https?://api\.flickr\.com/services/feeds/photos_public\.gne#', $username ) ) {
			$url = parse_url( $username );
			$url_query = array();
			wp_parse_str( $url['query'], $url_query );
			$query = array_merge( $query, $url_query );
		} else {
			$user = $this->request( 'flickr.people.findByUsername', array( 'username' => $username ) );
			if ( is_wp_error( $user ) )
				return $user;

			$user_id = $user->user->id;
			$query['id'] = $user_id;
		}

		$photos = $this->request_feed( 'photos_public', $query );

		if ( ! $photos )
			return new WP_Error( 'error', 'Could not fetch photos.' );

		$photos = array_slice( $photos, 0, $count );
		set_transient( $transient_key, $photos, apply_filters( 'fastwp_flickr_widget_cache_timeout', 3600 ) );
		return $photos;
	}

	private function request( $method, $args ) {
		$args['method'] = $method;
		$args['format'] = 'json';
		$args['api_key'] = 'd348e6e1216a46f2a4c9e28f93d75a48';
		$args['nojsoncallback'] = 1;
		$url = esc_url_raw( add_query_arg( $args, 'http://api.flickr.com/services/rest/' ) );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) )
			return false;

		$body = wp_remote_retrieve_body( $response );
 		$obj = json_decode( $body );

		if ( $obj && $obj->stat == 'fail' )
			return new WP_Error( 'error', $obj->message );

		return $obj ? $obj : false;
	}


	private function request_feed( $feed = 'photos_public', $args = array() ) {
		$args['format'] = 'json';
		$args['nojsoncallback'] = 1;
		$url = sprintf( 'http://api.flickr.com/services/feeds/%s.gne', $feed );
		$url = esc_url_raw( add_query_arg( $args, $url ) );

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) )
			return false;
		
		$body = wp_remote_retrieve_body( $response );
		$body = preg_replace( "#\\\\'#", "\\\\\\'", $body );
 		$obj = json_decode( $body );

		return $obj ? $obj->items : false;

	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['tags'] = strip_tags( $new_instance['tags'] );
		$instance['count'] = absint( $new_instance['count'] );
		return $new_instance;
	}
	
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : 'My photos';
		$username = isset( $instance['username'] ) ? $instance['username'] : '';
		$tags = isset( $instance['tags'] ) ? $instance['tags'] : '';
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 4;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ,'fastwp'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username or RSS:' ,'fastwp'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'tags' ); ?>"><?php _e( 'Tags:' ,'fastwp'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'tags' ); ?>" name="<?php echo $this->get_field_name( 'tags' ); ?>" type="text" value="<?php echo esc_attr( $tags ); ?>" /><br />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:' ,'fastwp'); ?></label><br />
			<input type="number" min="1" max="20" value="<?php echo esc_attr( $count ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" />
		</p>
		<?php
	}
}


 
class IonutSearch_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'fastwp_search_widget', 'Wisten search', array(
			'description' => 'Search widget for Wisten template sidebar.',
		) );
	}

	/**
	 * Displays the widget contents.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$button = (isset($instance['button']))? $instance['button'] : 'yes';

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

?>
<form action="<?php echo home_url(); ?>/" method="get" class="widget-search">
	<input type="text" name="s" class="search <?php echo $button;?>" placeholder="<?php _e('Search' ,'fastwp');?>">
	<?php
	switch($button){
		case 'yes':
		?>
		<input type="submit" class="btn btn-post" value="<?php _e('Search' ,'fastwp');?>">
		<?php
		break;
		case 'abs':
		?>
		<a href="javascript:this.form.submit()" class="btn btn-post btn-abs"><i class="fa fa-search"></i></a>
		<?php
		break;
		default:
		
		break;
	}
	?>

</form>
<?php
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['button'] = strip_tags( $new_instance['button'] );
		return $new_instance;
	}
	
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : 'Search';
		$button = isset( $instance['button'] ) ? $instance['button'] : 'yes';
		$sbutton = array('no'=> 'Hide button','yes' => 'Normal button','abs' => 'Button inside form');
		//echo esc_attr( $button ); 
		$options = '';
		foreach($sbutton as $k=>$v){
			$options .= '<option value="'.$k.'" '.(($k == $button)?'selected="selected"':'').'>'.$v.'</option>';
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ,'fastwp'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'button' ); ?>"><?php _e( 'Submit button type:' ,'fastwp'); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'button' ); ?>" name="<?php echo $this->get_field_name( 'button' ); ?>">
			<?php echo $options; ?>
			<select>
		</p>
		<?php
	}
}



add_action('widgets_init', 'register_eleven_widgets');
function register_eleven_widgets() {

	register_widget('IonutFlickr_Widget' );
	register_widget('IonutSearch_Widget' );

}