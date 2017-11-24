<?php
/**
 * Processor for 500px. This extends the Photonic_Processor class and defines methods local to 500px.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_500px_Processor extends Photonic_OAuth1_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_500px_api_key, $photonic_500px_api_secret, $photonic_500px_disable_title_link;
		$this->api_key = trim($photonic_500px_api_key);
		$this->api_secret = trim($photonic_500px_api_secret);
		$this->provider = '500px';
		$this->link_lightbox_title = empty($photonic_500px_disable_title_link);
	}

	/**
	 * A very flexible function to display photos from 500px. This makes use of the 500px API, hence it requires the user's Consumer API key.
	 * The API key is defined in the options. The function makes use of one API call:
	 *  <a href='http://developer.500px.com/docs/photos-index'>GET Photos</a> - for retrieving photos based on search critiera
	 *
	 * The following short-code parameters are supported:
	 * - feature: popular | upcoming | editors | fresh_today | fresh_yesterday | fresh_week | user | user_friends | user_favorites
	 * - user_id, username: Any one of them is required if feature = user | user_friends | user_favorites
	 * - only: 	Abstract | Animals | Black and White | Celebrities | City and Architecture | Commercial | Concert | Family | Fashion | Film |
	 * 			Fine Art | Food | Journalism | Landscapes | Macro | Nature | Nude | People | Performing Arts | Sport | Still Life | Street |
	 * 			Transportation | Travel | Underwater | Urban Exploration | Wedding
	 * - rpp: Number of photos
	 * - thumb_size: Size of the thumbnail. Can be 1 | 2 | 3, which correspond to 75 &times; 75 px, 140 &times; 140 px and 280 &times; 280 px respectively.
	 * - main_size: Size of the opened main photo. Can be 3 | 4, which correspond to 280 &times; 280 px and the full size respectively.
	 * - sort: created_at | rating | times_viewed | taken_at
	 *
	 * @param array $attr
	 * @return string|void
	 */
	function get_gallery_images($attr = array()) {
		global $photonic_500px_title_caption;
		$attr = array_merge(
			$this->common_parameters,
			array(
				// Common overrides ...
				'caption' => $photonic_500px_title_caption,
				'thumb_size'       => '1',
				'main_size'       => '4',
				'tile_size'       => '4',

				// 500px-Specific ...
				'date_to'   => strftime("%F", time() + 86400), // date format yyyy-mm-dd
				'date_from'   => '',
				//		'feature' => ''  // popular | upcoming | editors | fresh_today | fresh_yesterday | fresh_week
				'view' => 'photos',
				'rpp' => 100,
				'page' => 1,
				'username' => '',
			), $attr);
		$attr = array_map('trim', $attr);

		extract($attr);

		if (empty($this->api_key)) {
			return __("500px Consumer Key not defined", 'photonic');
		}

		$user_feature = false;
		$user_set = !empty($attr['user_id']) || !empty($attr['username']);

		$base_query = 'https://api.500px.com/v1/photos';
		if ((isset($view) && ($view == 'collections' || $view == 'sets' || $view == 'galleries'))) {
			if ($user_set) {
				$user = $this->get_user_details(!empty($attr['user_id']) ? $attr['user_id'] : $attr['username']);
				if (empty($user)) {
					return __("User is invalid", 'photonic');
				}
				$base_query = 'https://api.500px.com/v1/users/'.$user['id']."/galleries";
				$attr['username'] = $user['username'];
			}
			else {
				// Old format - only collection id has been passed
				$base_query = 'https://api.500px.com/v1/collections';
			}
		}
		else if ((isset($view) && $view == 'users')) {
			$base_query = 'https://api.500px.com/v1/users';
		}

		$is_collection = isset($view) && ($view == 'collections' || $view == 'sets' || $view == 'galleries');
		if (isset($view_id)) {
			$base_query .= '/'.$view_id;
			if ($is_collection && $base_query != 'https://api.500px.com/v1/collections/'.$view_id) {
				$base_query .= '/items';
			}
		}
		else if (!empty($attr['tag']) || !empty($attr['term'])) {
			$base_query .= '/search';
		}
		else if ((isset($view) && $view == 'users') && (isset($id) || !empty($attr['username']) || isset($email))) {
			$base_query .= '/show';
		}

		$dpx_params = array();
		$dpx_params['consumer_key'] = $this->api_key;

		$dpx_params['image_size'] = $attr['thumb_size'].','.$attr['main_size'];

		if (!empty($attr['feature'])) {
			$feature = esc_html($attr['feature']);
			$dpx_params['feature'] = $feature;
			if (in_array($feature, array('user', 'user_friends', 'user_favorites'))) {
				$user_feature = true;
			}
		}

		if (!empty($attr['user_id'])) {
			$dpx_params['user_id'] = $attr['user_id'];
			$dpx_params['id'] = $attr['user_id'];
		}
		else if (!empty($attr['username'])) {
			$dpx_params['username'] = $attr['username'];
		}

		if (isset($id) && $id != '' &&(!isset($view) || (isset($view) && $view != 'collections'))) {
			$dpx_params['id'] = $attr['id'];
		}

		if (!empty($attr['email'])) {
			$dpx_params['email'] = $attr['email'];
		}

		if ($user_feature && !$user_set) {
			return __("A user-specific feature has been requested, but the username or user_id is missing", 'photonic');
		}

		if (!empty($attr['only'])) {
			$only = urlencode($attr['only']);
			$dpx_params['only'] = $only;
		}

		if (!empty($attr['exclude'])) {
			$exclude = urlencode($attr['exclude']);
			$dpx_params['exclude'] = $exclude;
		}

		global $photonic_archive_thumbs;
		if (is_archive()) {
			if (!empty($photonic_archive_thumbs)) {
				if (isset($rpp) && $photonic_archive_thumbs < $rpp) {
					$rpp = $photonic_archive_thumbs;
					$this->show_more_link = true;
					$dpx_params['rpp'] = $photonic_archive_thumbs;
				}
				else if (isset($rpp)) {
					$dpx_params['rpp'] = $attr['rpp'];
				}
			}
			else if (isset($rpp)) {
				$dpx_params['rpp'] = $attr['rpp'];
			}
		}
		else if (isset($rpp)) {
			$dpx_params['rpp'] = $attr['rpp'];
		}

		if (!empty($attr['sort'])) {
			$dpx_params['sort'] = $attr['sort'];
		}

		if (!empty($attr['tag'])) {
			$dpx_params['tag'] = $attr['tag'];
		}

		if (!empty($attr['term'])) {
			$dpx_params['term'] = $attr['term'];
		}

		// Allow users to define additional query parameters
		$dpx_params = apply_filters('photonic_500px_query', $dpx_params, $attr);

		$ret = '';

		$this->gallery_index++;

		global $photonic_500px_allow_oauth, $photonic_500px_oauth_done;
		if ($photonic_500px_allow_oauth && is_single() && !$photonic_500px_oauth_done) {
			$post_id = get_the_ID();
			$ret .= $this->get_login_box($post_id);
		}

		$ret .= $this->process_response($base_query, $dpx_params, $attr);
		return $ret;
	}

	/**
	 * Queries the server, then parses through the response.
	 *
	 * @param $url
	 * @param array $short_code
	 * @return string
	 */
	function process_response($url, $dpx_params, $short_code) {
		$params = $this->get_query_params($url, $dpx_params, $short_code['page']);
		$response = Photonic::http($url, 'GET', $params);

		$error_message = '';
		if (is_wp_error($response)) {
			$error_message = "There was an error connecting to the server. Please try again later.";
		}
		else if ($response['response']['code'] == 401) { // Unauthorized
			$error_message = "Sorry, you need to be authorized to see this.";
		}
		else if ($response['response']['code'] != 200 && $response['response']['code'] != '200') { // Something went wrong
			$error_message = "<!-- Currently there is an error with the server. Code: ".$response['response']['code'].", Message: ".$response['response']['message']."-->";
		}
		if (!empty($error_message)) {
			return $this->finalize_markup($error_message, $short_code);
		}

		$ret = '';

		$content = $response['body'];
		$content = json_decode($content);

		if (isset($content->photos)) {
			$ret .= $this->process_photos($content, $url, $short_code);
		}
		else if (isset($content->photo)) {
			$ret .= $this->process_single_photo($content, $short_code['main_size']);
		}
		else if (isset($content->collections)) {
			if (count($content->collections) == 0) {
				$ret .= 'No collections found!';
			}
		}
		else if (isset($content->galleries)) {
			$ret .= $this->process_galleries($content, $short_code);
		}
		else if (isset($content->users)) {
			$ret .= $this->process_users($content, $short_code);
		}

		$ret = $this->finalize_markup($ret, $short_code);
		return $ret;
	}

	private function get_query_params($url, $dpx_params, $page_number) {
		global $photonic_500px_oauth_done;
		if (empty($dpx_params['page'])) {
			$dpx_params['page'] = $page_number;
		}

		$params = substr($url, strlen($this->end_point()));
		if (strlen($params) > 1) {
			$params = substr($params, 1);
		}
		$params = Photonic_Processor::parse_parameters($params);
		$params = array_merge($dpx_params, $params);

		if ($photonic_500px_oauth_done) {
			$signed_args = $this->sign_call($this->end_point(), 'GET', $params);
			$params = $signed_args;
		}
		return $params;
	}

	/**
	 * @param $content
	 * @param $url
	 * @param array $short_code
	 * @return string
	 */
	function process_photos($content, $url, $short_code, $dpx_params = array()) {
		global $photonic_500px_photos_per_row_constraint, $photonic_500px_photos_constrain_by_count, $photonic_500px_photos_constrain_by_padding, $photonic_500px_photo_title_display;
		$ret = '';
		$header_meta = array();
		if (isset($content->title)) { // A collection
			$header_meta['title'] = $content->title;
			if ($short_code['display'] == 'in-page' || $short_code['popup'] == 'show') {
				$ret .= '<h3>'.$content->title.'</h3>';
			}
		}
		$all_photos = $this->get_all_photos($content, $url, $short_code['date_from'], $short_code['date_to'], $short_code['rpp'], $short_code['rpp'], $short_code['page'], $dpx_params);
		$objects = $this->build_level_1_objects($all_photos, 'photos', $short_code['thumb_size'], $short_code['main_size']);
		$row_constraints = array('constraint-type' => $photonic_500px_photos_per_row_constraint, 'padding' => $photonic_500px_photos_constrain_by_padding, 'count' => $photonic_500px_photos_constrain_by_count);
		$level_2_meta = array(
			'total' => $content->total_items,
			'start' => ($content->current_page - 1) * $short_code['rpp'] + 1,
			'end' => $content->current_page * $short_code['rpp'] > $content->total_items ? $content->total_items : $content->current_page * $short_code['rpp'],
			'per-page' => $short_code['rpp'],
		);

		$ret .= $this->display_level_1_gallery($objects,
			array(
				'title_position' => $photonic_500px_photo_title_display,
				'row_constraints' => $row_constraints,
				'parent' => 'gallery',
				'level_2_meta' => $level_2_meta,
			),
			$short_code
		);

		if ($ret != '' && $short_code['page'] === 1) {
			if ($this->show_more_link) {
				$ret .= $this->more_link_button(get_permalink().'#photonic-500px-stream-'.$this->gallery_index);
			}
		}
		return $ret;
	}

	function build_level_1_objects($dpx_objects, $type, $thumb_size = '1', $main_size = '4') {
		$objects = array();
		foreach ($dpx_objects as $dpx_object) {
			$object = array();
			if ($type == 'photos') {
				$images = $dpx_object->images;
				foreach ($images as $image) {
					if ($image->size == $thumb_size) {
						$object['thumbnail'] = $image->https_url;
					}
					else if ($image->size == $main_size) {
						$object['main_image'] = $image->https_url;
					}
				}

				$object['main_page'] = "https://500px.com/photo/".$dpx_object->id;
				$object['title'] = $dpx_object->name;
				$object['alt_title'] = $object['title'];
				$object['description'] = $dpx_object->description;
				$object['id'] = $dpx_object->id;
			}
			else {
				if (isset($dpx_object->domain)) {
					$url = parse_url($dpx_object->domain);
					if (!isset($url['scheme'])) {
						$url = 'https://'.$url['path'];
					}
					else {
						$url = $url['scheme'].'://'.$url['path'];
					}
					$object['main_page'] = $url;
					$object['id'] = $dpx_object->id;
				}
				if (isset($dpx_object->userpic_url)) {
					$pic_url = parse_url($dpx_object->userpic_url);
					if (!isset($pic_url['scheme'])) {
						$pic_url = 'https://500px.com'.$pic_url['path'];
					}
					else {
						$pic_url = $dpx_object->userpic_url;
					}
					$object['thumbnail'] = $pic_url;
					$object['main_image'] = $pic_url;

					$alt = '';
					if (isset($dpx_object->fullname)) {
						$alt .= $dpx_object->fullname;
					}
					else {
						$alt .= $dpx_object->username;
					}
					if (isset($dpx_object->photos_count)) {
						$alt .= '<br/>'.sprintf(__('%1$s photos', 'photonic'), $dpx_object->photos_count);
					}
					if (isset($dpx_object->friends_count)) {
						$alt .= '<br/>'.sprintf(__('%1$s friends', 'photonic'), $dpx_object->friends_count);
					}
					if (isset($dpx_object->followers_count)) {
						$alt .= '<br/>'.sprintf(__('%1$s followers', 'photonic'), $dpx_object->followers_count);
					}
					$object['title'] = $alt;
					$object['alt_title'] = $alt;
				}
			}
			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Gets all photos within a date range.
	 * The date filtering capability was provided by Bart Kuipers (http://www.bartkuipers.com/).
	 *
	 * @param $content
	 * @param $url
	 * @param string $date_from_string
	 * @param string $date_to_string
	 * @param int $number_of_photos_to_go
	 * @param int $number_per_page
	 * @param int $page_number
	 * @return array
	 */
	function get_all_photos($content, $url, $date_from_string = '', $date_to_string = '', $number_of_photos_to_go = 20, $number_per_page = 20, $page_number = 1, $dpx_params) {
		$photos = $content->photos;
		$all_photos_found = false;

		$selected_photos = array();
		foreach ($photos as $photo) {
			$timestamp = strtotime($photo->created_at);
			if ($timestamp !== false) {
				$date_from = strtotime($date_from_string);
				if ($date_from === false) {
					$date_from = 1;
				}
				if ($timestamp < $date_from) {
					$all_photos_found = true;
					continue;
				}

				$date_to = strtotime($date_to_string);
				if ($date_to === false) {
					$date_to = gettimeofday();
					$date_to = $date_to['sec'] + 86400; // tomorrow's date
				}
				if ($timestamp > $date_to) {
					$all_photos_found = true;
					continue;
				}
			}
			if ($number_of_photos_to_go <= 0) {
				continue;
			}
			$selected_photos[] = $photo;
			$number_of_photos_to_go--;
		}
		if ($number_of_photos_to_go > 0 && $all_photos_found != true && count($photos) >= $number_per_page ) {
			$new_params = $this->get_query_params($url, $dpx_params, $page_number + 1);
			$more_photos = $this->get_all_photos($content, $url, $date_from_string, $date_to_string, $number_of_photos_to_go, $number_per_page, $page_number + 1, $new_params);
			$selected_photos = array_merge($selected_photos, $more_photos);
		}
		return $selected_photos;
	}

	function process_single_photo($content, $main_size = '4') {
		if (isset($content->photo)) {
			$photo = $content->photo;
			$images = $photo->images;
			$image_url = $photo->image_url;
			foreach ($images as $image) {
				if ($image->size == $main_size) {
					$image_url = $image->https_url;
					break;
				}
			}

			return $this->generate_single_photo_markup('500px', array(
					'src' => $image_url,
					'href' => '',
					'title' => $photo->name,
					'caption' => $photo->description,
				)
			);
		}
		else {
			return '';
		}
	}

	function process_users($content, $short_code) {
		if (isset($content->users)) {
			if (count($content->users) == 0) {
				return 'No users found!';
			}
			else {
				global $photonic_500px_photos_per_row_constraint, $photonic_500px_photos_constrain_by_padding, $photonic_500px_photos_constrain_by_count;
				$users = $content->users;
				$objects = $this->build_level_1_objects($users, 'users');
				$row_constraints = array('constraint-type' => $photonic_500px_photos_per_row_constraint, 'padding' => $photonic_500px_photos_constrain_by_padding, 'count' => $photonic_500px_photos_constrain_by_count);
				$short_code['layout'] = 'square';
				return $this->display_level_1_gallery($objects,
					array(
						'title_position' => 'tooltip',
						'row_constraints' => $row_constraints,
						'show_lightbox' => false,
						'type' => 'user',
						'parent' => 'stream',
					),
					$short_code
				);
			}
		}
		else {
			return '';
		}
	}


	private function process_galleries($content, $short_code) {
		if (isset($content->galleries)) {
			if (count($content->galleries) == 0) {
				return 'No galleries found!';
			}
			else {
				global $photonic_500px_gallery_photos_per_row_constraint, $photonic_500px_gallery_photos_constrain_by_padding,
					   $photonic_500px_gallery_photos_constrain_by_count, $photonic_500px_gallery_photo_title_display;
				$galleries = $content->galleries;
				$objects = $this->build_level_2_objects($galleries, 'gallery', $short_code['username'], $short_code['filter']);
				$row_constraints = array('constraint-type' => $photonic_500px_gallery_photos_per_row_constraint, 'padding' => $photonic_500px_gallery_photos_constrain_by_padding, 'count' => $photonic_500px_gallery_photos_constrain_by_count);
				$ret = $this->display_level_2_gallery($objects,
					array(
						'row_constraints' => $row_constraints,
						'type' => 'galleries',
						'singular_type' => 'gallery',
						'title_position' => $photonic_500px_gallery_photo_title_display,
						'level_1_count_display' => false,
					),
					$short_code
				);
				return $ret;
			}
		}
		else {
			return '';
		}
	}

	private function build_level_2_objects($dpx_objects, $type, $username, $filter = '') {
		$objects = array();
		$filters = array();
		if (!empty($filter)) {
			$filters = explode(',', $filter);
		}
		foreach ($dpx_objects as $dpx_object) {
			$object = array();
			if (!empty($filters) && !in_array($dpx_object->id, $filters) && !in_array($dpx_object->name, $filters)) {
				continue;
			}
			$object['id_1'] = $dpx_object->id;
//			$object['id_2'] = $dpx_object->id;
			$object['title'] = esc_attr($dpx_object->name);

			if ($type == 'gallery') {
				if (isset($dpx_object->thumbnail_photos)) {
					$thumbnails = $dpx_object->thumbnail_photos;
					$object['thumbnail'] = $thumbnails[0]->image_url;
					$object['tile_image'] = $thumbnails[0]->image_url;
				}
				$object['main_page'] = 'https://500px.com/'.$username.'/galleries/';
				$object['main_page'] .= !empty($dpx_object->custom_path) ? $dpx_object->custom_path : $dpx_object->id;
				$object['counter'] = $dpx_object->items_count;
				$object['classes'] = array("photonic-500px-gallery-thumb-{$dpx_object->id}");
			}

			$objects[] = $object;
		}
		return $objects;
	}

	private function get_user_details($user) {
		$base_query = 'https://api.500px.com/v1/users/show?consumer_key='.$this->api_key;
		$choices = array('id', 'username', 'email');

		foreach ($choices as $choice) {
			$response = Photonic::http($base_query.'&'.$choice.'='.$user);
			if (is_wp_error($response)) {
				return null;
			}
			if ($response['response']['code'] == 200) {
				$body = $response['body'];
				$body = json_decode($body);
				$user = $body->user;
				return array('id' => $user->id, 'username' => $user->username);
			}
		}
		return null;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'https://api.500px.com/v1/oauth/access_token';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		return 'https://api.500px.com/v1/oauth/authorize';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		return 'https://api.500px.com/v1/oauth/authorize';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_token_URL() {
		return 'https://api.500px.com/v1/oauth/request_token';
	}

	public function end_point() {
		return 'https://api.500px.com/v1/';
	}

	function parse_token($response) {
		$body = $response['body'];
		$token = Photonic_Processor::parse_parameters($body);
		return $token;
	}

	public function check_access_token_method() {
		// TODO: Implement check_access_token_method() method.
	}

	/**
	 * Method to validate that the stored token is indeed authenticated.
	 *
	 * @param $token
	 * @return array|WP_Error
	 */
	function check_access_token($token) {
		$signed_parameters = $this->sign_call('https://api.500px.com/v1/users', 'GET', array());
		$end_point = 'https://api.500px.com/v1/users?'.Photonic_Processor::build_query($signed_parameters);
		$response = Photonic::http($end_point, 'GET', null);

		return $response;
	}

	public function is_access_token_valid($response) {
		if (is_wp_error($response)) {
			return false;
		}
		$response = $response['response'];
		if ($response['code'] == 200) {
			return true;
		}
		return false;
	}
}