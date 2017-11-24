<?php
/**
 * Processor for Instagram. This extends the Photonic_OAuth2_Processor class and defines methods local to Instagram.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Instagram_Processor extends Photonic_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_instagram_disable_title_link, $photonic_instagram_access_token;
		$this->provider = 'instagram';
		$this->oauth_version = '2.0';
		$this->response_type = 'token';
		$this->scope = 'basic public_content';
		$this->access_token = $photonic_instagram_access_token;
		$this->link_lightbox_title = empty($photonic_instagram_disable_title_link);
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @param array $attr
	 * @return mixed|string|void
	 */
	public function get_gallery_images($attr = array()) {
		global $photonic_instagram_main_size;
		$attr = array_merge(
			$this->common_parameters,
			array(
				// Common overrides ...
				'caption' => 'title',
				'thumb_size' => 75,
				'main_size' => $photonic_instagram_main_size,
				'tile_size' => $photonic_instagram_main_size,

				// Instagram-specific ...
				'show_captions' => false,
				'crop' => true,
				'count' => 1000,
				'distance' => 1000,
			), $attr);
		$attr = array_map('trim', $attr);

		extract($attr);

		if (!isset($this->access_token) || empty($this->access_token) || $this->is_token_expired($this->access_token)) {
			return __("Instagram Access Token not valid. Please reauthenticate.", 'photonic');
		}

		$base_url = 'https://api.instagram.com/v1/';
		$display_what = 'media';
		if (!empty($media_id)) {// Trumps all else. A single photo will be shown.
			$query_url = 'http://api.instagram.com/oembed?url='.urlencode('http://instagr.am/p/'.$media_id.'/');
			$display_what = 'single-media';
		}
		else if (!empty($user_id)) {
			$query_url = $base_url.'users/'.$user_id.'/media/recent'; // Doesn't matter what the other values are. User's recent photos will be shown.
		}
		else {
			if (!empty($tag_name) && (empty($view) || $view == 'tag')) {
				$query_url = $base_url.'tags/'.$tag_name.'/media/recent';
				if (isset($min_id) || isset($max_id)) {
					$query_url .= '?';
					if (isset($min_id)) {
						$query_url .= 'min_tag_id='.$min_id.'&';
					}
					if (isset($max_id)) {
						$query_url .= 'max_tag_id='.$max_id.'&';
					}
				}
			}
			else if (!empty($location_id) && (empty($view) || $view == 'location')) {
				$query_url = $base_url.'locations/'.$location_id.'/media/recent';
				if (isset($min_id) || isset($max_id)) {
					$query_url .= '?';
					if (isset($min_id)) {
						$query_url .= 'min_id='.$min_id.'&';
					}
					if (isset($max_id)) {
						$query_url .= 'max_id='.$max_id.'&';
					}
				}
			}
			else if (!empty($lat) && !empty($lng) && (empty($view) || $view == 'search')) {
				$query_url = $base_url.'media/search?';
				$query_url .= 'lat='.$lat.'&';
				$query_url .= 'lng='.$lng.'&';
				$query_url .= 'distance='.$attr['distance'].'&';
			}
			else if (empty($view)) {
				return __('The <code>view</code> parameter has to be defined.', 'photonic');
			}
			else {
				return __('Malformed shortcode. Either <code>media_id</code> or <code>user_id</code> or <code>tag_name</code> or <code>location_id</code> or <code>lat+lng</code> have to be defined. If you have specified one of them, the <code>view</code> parameter is inconsistent.', 'photonic');
			}
		}

		if (isset($count)) {
			if (!stripos($query_url, '?')) {
				$query_url .= '?count='.$count;
			}
			else if (substr($query_url, -1, 1) != '&' && substr($query_url, -1, 1) != '?') {
				$query_url .= '&count='.$count;
			}
			else {
				$query_url .= 'count='.$count;
			}
		}

		$ret = '';
		return $ret.$this->make_call($query_url, $display_what, $attr);
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public function parse_token($response) {
		// TODO: Implement parse_token() method.
	}

	protected function make_call($query_url, $display_what, $shortcode_attr) {
		$ret = '';
		$query = $query_url;
		if (substr($query, -1, 1) != '&' && !stripos($query, '?')) {
			$query .= '?';
		}
		else if (substr($query, -1, 1) != '&' && stripos($query, '?')) {
			$query .= '&';
		}

		if (isset($this->access_token) && !$this->is_token_expired($this->access_token)) {
			$query .= 'access_token='.$this->access_token;
		}
		else {
			return __("Instagram Access Token not valid. Please reauthenticate.", 'photonic');
		}

		$response = wp_remote_request($query, array(
			'sslverify' => false,
		));

		if (!is_wp_error($response)) {
			if (isset($response['response']) && isset($response['response']['code'])) {
				if ($response['response']['code'] == 200) {
					$body = json_decode($response['body']);
					if (isset($body->data) && $display_what != 'single-media') {
						$data = $body->data;
						$this->gallery_index++;
						$ret .= $this->process_media($data, $shortcode_attr);
					}
					else if ($display_what == 'single-media') {
						if (!empty($body->html)) {
							$ret .= $body->html;
						}
					}
					else {
						return __('No data returned. Unknown error', 'photonic');
					}
				}
				else if (isset($response['body'])) {
					$body = json_decode($response['body']);
					if (isset($body->meta) && isset($body->meta->error_message)) {
						return $body->meta->error_message;
					}
					else {
						return __('Unknown error', 'photonic');
					}
				}
				else if (isset($response['response']['message'])) {
					return $response['response']['message'];
				}
				else {
					return __('Unknown error', 'photonic');
				}
			}
		}
		else {
			return __('There was a problem connecting. Please try back after some time.', 'photonic');
		}
		return $ret;
	}

	function process_media($data, $short_code) {
		global $photonic_instagram_photos_per_row_constraint, $photonic_instagram_photos_constrain_by_padding, $photonic_instagram_photos_constrain_by_count, $photonic_instagram_photo_title_display;

		$photo_objects = $this->build_level_1_objects($data, $short_code['thumb_size']);
		$row_constraints = array('constraint-type' => $photonic_instagram_photos_per_row_constraint, 'padding' => $photonic_instagram_photos_constrain_by_padding, 'count' => $photonic_instagram_photos_constrain_by_count);

		$ret = $this->display_level_1_gallery($photo_objects,
			array(
				'title_position' => $photonic_instagram_photo_title_display,
				'row_constraints' => $row_constraints,
				'sizes' => array('thumb-width' => $short_code['thumb_size'], 'thumb-height' => $short_code['thumb_size']),
				'parent' => 'stream',
			),
			$short_code
		);
		$ret = $this->finalize_markup($ret, $short_code);
		return $ret;
	}

	function build_level_1_objects($data, $thumb_size) {
		global $photonic_instagram_main_size;
		$level_1_objects = array();
		if ($thumb_size <= 150) {
			$url_function = 'thumbnail';
		}
		else if ($thumb_size > 150 && $thumb_size <= 320) {
			$url_function = 'low_resolution';
		}
		else {
			$url_function = 'standard_resolution';
		}
		foreach ($data as $photo) {
			if (isset($photo->type) && $photo->type == 'image' && isset($photo->images)) {
				$photo_object = array();
				$photo_object['thumbnail'] = $photo->images->{$url_function}->url;

				if (!isset($photo->images->{$photonic_instagram_main_size})) { // Sizes such as 1080x1080 are not returned by Instagram
					$main_image = $photo->images->thumbnail->url;
					$main_image = str_replace('/s150x150/', '/'.$photonic_instagram_main_size.'/', $main_image);
				}
				else {
					$main_image = $photo->images->{$photonic_instagram_main_size}->url;
				}
				$photo_object['main_image'] = $main_image;

				if (isset($photo->caption) && isset($photo->caption->text)) {
					$photo_object['title'] = esc_attr($photo->caption->text);
				}
				else {
					$photo_object['title'] = '';
				}
				$photo_object['alt_title'] = $photo_object['title'];
				$photo_object['description'] = $photo_object['title'];
				$photo_object['main_page'] = $photo->link;
				$photo_object['id'] = $photo->id;

				$level_1_objects[] = $photo_object;
			}
		}
		return $level_1_objects;
	}

	function is_token_expired($token) {
		if (empty($token)) {
			return true;
		}

		$url = 'https://api.instagram.com/v1/users/self/?access_token='.$token;
		$response = wp_remote_request($url, array(
			'sslverify' => false,
		));

		if (isset($response['body'])) {
			$body = json_decode($response['body']);
			if (isset($body->meta) && isset($body->meta->code) && $body->meta->code == 200) {
				return false;
			}
		}
		return true;
	}
}