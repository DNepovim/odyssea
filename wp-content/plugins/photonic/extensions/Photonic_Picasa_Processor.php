<?php
/**
 * Processor for Picasa. This extends the Photonic_Processor class and defines methods local to Picasa.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Picasa_Processor extends Photonic_OAuth2_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_picasa_client_id, $photonic_picasa_client_secret, $photonic_picasa_disable_title_link, $photonic_picasa_refresh_token;
		$this->client_id = trim($photonic_picasa_client_id);
		$this->client_secret = trim($photonic_picasa_client_secret);
		$this->provider = 'picasa';
		$this->oauth_version = '2.0';
		$this->response_type = 'code';
		$this->scope = 'https://picasaweb.google.com/data/';
		$this->link_lightbox_title = empty($photonic_picasa_disable_title_link);

		$cookie = Photonic::parse_cookie();
		global $photonic_picasa_allow_oauth;
		$this->oauth_done = false;
		$this->perform_back_end_authentication($photonic_picasa_refresh_token);

		if ($photonic_picasa_allow_oauth && isset($cookie['picasa']) && isset($cookie['picasa']['oauth_token']) && isset($cookie['picasa']['oauth_refresh_token'])) { // OAuth2, so no Access token secret
			if ($this->is_token_expired($cookie['picasa'])) {
				$this->refresh_token('picasa', $cookie['picasa']['oauth_refresh_token']);
				$cookie = Photonic::parse_cookie(); // Refresh the cookie object based on the results of the refresh token
				if ($this->is_token_expired($cookie['picasa'])) { // Tried refreshing, but didn't work
					$this->oauth_done = false;
				}
				else {
					$this->oauth_done = true;
				}
			}
			else {
				$this->oauth_done = true;
			}
		}
		else if (!isset($cookie['picasa']) || !isset($cookie['picasa']['oauth_token'])) {
			$this->oauth_done = false;
		}
		else if (isset($cookie['picasa']) && !isset($cookie['picasa']['oauth_refresh_token'])) {
			if (!$this->is_token_expired($cookie['picasa'])) {
				$this->oauth_done = true;
			}
			else {
				$this->refresh_token('picasa', $cookie['picasa']['oauth_refresh_token']);
				$cookie = Photonic::parse_cookie(); // Refresh the cookie object based on the results of the refresh token
				if ($this->is_token_expired($cookie['picasa'])) { // Tried refreshing, but didn't work
					$this->oauth_done = false;
				}
				else {
					$this->oauth_done = true;
				}
			}
		}
	}

	/**
	 *
	 * user_id
	 * kind
	 * album
	 * max_results
	 *
	 * thumb_size
	 * columns
	 * shorten caption
	 * show caption
	 *
	 * @param array $attr
	 * @return string
	 */
	function get_gallery_images($attr = array()) {
		global $photonic_picasa_use_desc;
		$attr = array_merge(
			$this->common_parameters,
			array(
				'caption' => $photonic_picasa_use_desc,
				'thumb_size' => '72c',
				'main_size' => '1600',
				'tile_size' => '1600',

				// Picasa ...
				'user_id' => '',
				'access' => 'public',
				'protection' => 'none',
				'start_index' => 1,
				'max_results' => 1000,
			),
			$attr);
		$attr = array_map('trim', $attr);

		extract($attr);

		if (empty($attr['user_id'])) {
			return '';
		}

		$query_url = 'https://picasaweb.google.com/data/feed/api/user/'.$attr['user_id'];

		if (!empty($attr['album'])) {
			$query_url .= '/album/'.urlencode($attr['album']);
		}

		if (!empty($attr['albumid'])) {
			$query_url .= '/albumid/'.urlencode($attr['albumid']);
		}

		$picasa_params = array();
		if (!empty($attr['kind']) && in_array(trim($attr['kind']), array('album', 'photo', 'tag'))) {
			$kind = trim($attr['kind']);
			$picasa_params['kind'] = $kind;
		}
		else {
			$kind = '';
		}

		if (empty($attr['view'])) {
			if ($kind == 'album') {
				$attr['view'] = 'album';
			}
			else if ($kind == '') {
				if (empty($attr['album']) && empty($attr['albumid'])) {
					$attr['view'] = 'album';
				}
				else {
					$attr['view'] = 'photo';
				}
			}
			else {
				$attr['view'] = $kind;
			}
		}

		global $photonic_archive_thumbs;
		if (is_archive()) {
			if (isset($photonic_archive_thumbs) && !empty($photonic_archive_thumbs)) {
				if (isset($attr['max_results']) && $photonic_archive_thumbs < $attr['max_results']) {
					$this->show_more_link = true;
					$picasa_params['max-results'] = $photonic_archive_thumbs;
				}
				else if (isset($attr['max_results'])) {
					$picasa_params['max-results'] = $attr['max_results'];
				}
			}
			else if (isset($attr['max_results'])) {
				$picasa_params['max-results'] = $attr['max_results'];
			}
		}
		else if (isset($attr['max_results'])) {
			$picasa_params['max-results'] = $attr['max_results'];
		}

		$picasa_params['start-index'] = $attr['start_index'];

		if (!empty($attr['authkey'])) {
			$picasa_params['authkey'] = $attr['authkey'];
		}

		if (!empty($attr['thumbsize'])) {
			$picasa_params['thumbsize'] = $attr['thumbsize'];
			$attr['thumb_size'] = $attr['thumbsize'];
		}
		else if (!empty($attr['thumb_size'])) {
			$picasa_params['thumbsize'] = $attr['thumb_size'];
		}
		else {
			$picasa_params['thumbsize'] = '72c';
		}

		if (!empty($attr['main_size'])) {
			$picasa_params['imgmax'] = $attr['main_size'];
		}
		else {
			$picasa_params['imgmax'] = '1600';
		}

		global $photonic_picasa_allow_oauth;
		$ret = '';
		if ($photonic_picasa_allow_oauth && !$this->oauth_done && $attr['display'] != 'popup') {
			$post_id = get_the_ID();
			$ret .= $this->get_login_box($post_id);
		}

		return $ret.$this->make_call($query_url, $picasa_params, $attr);
	}

	function make_call($query_url, $picasa_params, $attr) {
		global $photonic_picasa_allow_oauth, $photonic_picasa_refresh_token;
		extract($attr);

		foreach ($picasa_params as $name => $value) {
			$query_url = add_query_arg($name, $value, $query_url);
		}

		$rss = '';
		if (!empty($photonic_picasa_refresh_token) && !empty($this->access_token)) {
			$query_url = add_query_arg('access_token', $this->access_token, $query_url);
			$rss = $this->get_secure_curl_response($query_url);
			/* This works, but test further ...
			$rss = wp_remote_request($query_url, array(
				'timeout' => 30 ,
				'user-agent' => 'Photonic',
				'sslverify' => true // prevent some problems with Google in token request
			));
			*/
		}
		else if (isset($photonic_picasa_allow_oauth) && $photonic_picasa_allow_oauth && $this->oauth_done) {
			if (isset($_COOKIE['photonic-' . md5($this->client_secret) . '-oauth-token'])) {
				$query_url = add_query_arg('access_token', $_COOKIE['photonic-' . md5($this->client_secret) . '-oauth-token'], $query_url);
				$response = $this->get_secure_curl_response($query_url);
				if (!(strlen($response) == 0 || substr($response, 0, 1) != '<')) {
					$rss = $response;
				}
			}
		}
		else {
			$response = wp_remote_request($query_url);
			if (!is_wp_error($response) && $response['response']['code'] == 200) {
				$rss = $response['body'];
			}
		}

		$this->gallery_index++;
		$out = $this->process_response($rss, $attr);

		return $this->finalize_markup($out, $attr);
	}

	/**
	 * Reads the output from Picasa and parses it to generate the front-end output.
	 * In a later release this will be streamlined to use DOM-based parsing instead of event-based parsing.
	 *
	 * @param $rss
	 * @param array $short_code
	 * @return string
	 */
	function process_response($rss, $short_code = array()) {
		global $photonic_picasa_photo_title_display, $photonic_picasa_photo_pop_title_display, $photonic_picasa_photos_per_row_constraint,
			$photonic_picasa_photos_constrain_by_count, $photonic_picasa_photos_constrain_by_padding, $photonic_picasa_photos_pop_per_row_constraint,
			$photonic_picasa_photos_pop_constrain_by_count, $photonic_picasa_photos_pop_constrain_by_padding;
		$display = $short_code['display'];
		$picasa_result = simplexml_load_string($rss);
		$out = '';

		if (is_a($picasa_result, 'SimpleXMLElement')) {
			if (isset($picasa_result->entry) && count($picasa_result->entry) > 0) {
				$row_constraints = array('constraint-type' => $photonic_picasa_photos_per_row_constraint, 'padding' => $photonic_picasa_photos_constrain_by_padding, 'count' => $photonic_picasa_photos_constrain_by_count);

				if ($short_code['view'] == 'album' && $display == 'in-page') {
					$objects = $this->build_level_2_objects($picasa_result->entry, $short_code['thumb_size'], $short_code['filter'], $short_code['access'], $short_code['protection']);
					$out .= $this->display_level_2_gallery($objects,
						array(
							'row_constraints' => $row_constraints,
							'type' => 'albums',
							'singular_type' => 'album',
							'title_position' => $photonic_picasa_photo_title_display,
							'level_1_count_display' => false,
						),
						$short_code
					);
				}
				else {
					if ($display == 'in-page') {
						$title_position = $photonic_picasa_photo_title_display;
					}
					else {
						$row_constraints = array('constraint-type' => $photonic_picasa_photos_pop_per_row_constraint, 'padding' => $photonic_picasa_photos_pop_constrain_by_padding, 'count' => $photonic_picasa_photos_pop_constrain_by_count);
						$title_position = $photonic_picasa_photo_pop_title_display;
					}

					$level_2_meta = $picasa_result->children('openSearch',1);
					$level_2_meta = array(
						'total' => $level_2_meta->totalResults,
						'start' => $level_2_meta->startIndex,
						'end' => $level_2_meta->startIndex + $level_2_meta->itemsPerPage - 1 > $level_2_meta->totalResults ? $level_2_meta->totalResults : $level_2_meta->startIndex + $level_2_meta->itemsPerPage - 1,
						'per-page' => $level_2_meta->itemsPerPage,
					);

					$objects = $this->build_level_1_objects($picasa_result->entry);
					$out .= $this->display_level_1_gallery($objects,
						array(
							'title_position' => $title_position,
							'row_constraints' => $row_constraints,
							'parent' => 'album',
							'level_2_meta' => $level_2_meta,
						),
						$short_code
					);
				}
			}
		}

		if ($out != '') {
			if ($this->show_more_link && $short_code['popup'] == 'show') {
				$out .= $this->more_link_button(get_permalink().'#photonic-picasa-stream-'.$this->gallery_index);
			}
		}
		return $out;
	}

	function build_level_1_objects($photos) {
		$objects = array();
		foreach ($photos as $entry) {
			$media_photo = $entry->children('media', 1);
			$media_photo = $media_photo->group;
			if (stripos($media_photo->content->attributes()->type, 'video') !== false) {
				continue;
			}
			$gphoto_photo = $entry->children('gphoto', 1);
			$object = array();
			$object['thumbnail'] = $media_photo->thumbnail->attributes()->url;
			$object['main_image'] = $media_photo->content->attributes()->url;
			$matches = array();
			//preg_match('/\/[^\/]+\/([^\/]+)$/', $object['main_image'], $matches);
			preg_match('/\/([^\/]+)$/', $object['main_image'], $matches);

			$object['download'] = str_replace($matches[0], '-d'.$matches[0], $object['main_image']);
			if (isset($entry->link)) {
				foreach ($entry->link as $link) {
					$attributes = $link->attributes();
					if (isset($attributes['type']) && $attributes['type'] == 'text/html' && isset($attributes['href']) && isset($attributes['rel'])) {
						if ((stripos($attributes['rel'], 'http://schemas.google.com/photos') === 0 || stripos($attributes['rel'], 'http://schemas.google.com/photos') === 0) && stripos($attributes['rel'], '#canonical')) {
							$object['main_page'] = $attributes['href'];
							break;
						}
					}
				}
			}
			if (!isset($object['main_page'])) {
				$object['main_page'] = $object['main_image'];
			}

			$object['title'] = $entry->title;
			$object['alt_title'] = $object['title'];
			$object['description'] = $entry->summary;
			$object['id'] = "{$gphoto_photo->id}";

			$objects[] = $object;
		}
		return $objects;
	}

	function build_level_2_objects($albums, $thumb_size, $filter, $access, $protection) {
		global $photonic_picasa_use_desc;
		$objects = array();
		$filters = empty($filter) ? array() : explode(',', $filter);

		if (!is_array($access)) {
			$access = explode(',', $access);
		}

		if (!is_array($protection)) {
			$protection = explode(',', $protection);
		}

		foreach ($albums as $entry) {
			$media_photo = $entry->children('media', 1);
			$media_photo = $media_photo->group;
			$gphoto_photo = $entry->children('gphoto', 1);

			if (!empty($filters) && !in_array($gphoto_photo->id, $filters) && !in_array($gphoto_photo->name, $filters)) {
				continue;
			}
			if (!in_array($gphoto_photo->access, $access)) {
				continue;
			}

			$object = array();
			$object['id_1'] = "{$gphoto_photo->id}";
			$object['id_2'] = "{$gphoto_photo->user}";

			$object['thumbnail'] = $media_photo->thumbnail->attributes()->url;
			$object['tile_image'] = $media_photo->content->attributes()->url;
			if (isset($entry->link)) {
				foreach ($entry->link as $link) {
					$attributes = $link->attributes();
					if (isset($attributes['type']) && $attributes['type'] == 'text/html' && isset($attributes['href']) && isset($attributes['rel'])) {
						if (((stripos($attributes['rel'], 'http://schemas.google.com/photos') === 0 || stripos($attributes['rel'], 'http://schemas.google.com/photos') === 0) && stripos($attributes['rel'], '#canonical')) || $attributes['rel'] == 'alternate') {
							$object['main_page'] = $attributes['href'];
							break;
						}
					}
				}
			}
			if (!isset($object['main_page'])) {
				$object['main_page'] = $media_photo->content->attributes()->url;
			}
			if ($photonic_picasa_use_desc == 'desc' || ($photonic_picasa_use_desc == 'desc-title' && !empty($entry->summary))) {
				$object['title'] = esc_attr($entry->summary);
			}
			else {
				$object['title'] = esc_attr($entry->title);
			}
			$object['counter'] = $gphoto_photo->numphotos;
			$object['data_attributes'] = array('thumb-size' => $thumb_size);

			if (in_array('authkey', $protection) && $gphoto_photo->access == 'private' && stripos($object['main_page'], '?authkey=') !== FALSE) {
				$object['passworded'] = 1;
				$object['classes'] = array('photonic-picasa-passworded', 'photonic-picasa-passworded-authkey');
			}

			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'https://accounts.google.com/o/oauth2/token';
	}

	public function authentication_url() {
		return 'https://accounts.google.com/o/oauth2/auth';
	}

	function parse_token($response) {
		$token = array();
		if (!is_wp_error($response) && is_array($response)) {
			$body = $response['body'];
			$body = json_decode($body);
			$token['oauth_token'] = $body->access_token;
			$token['oauth_token_type'] = $body->token_type;
			$token['oauth_token_created'] = time();
			$token['oauth_token_expires'] = $body->expires_in;
		}
		return $token;
	}

	function get_secure_curl_response($query_url) {
		$cert = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $query_url);
		curl_setopt($ch, CURLOPT_HEADER, 0); // Don’t return the header, just the html
		curl_setopt($ch, CURLOPT_CAINFO, $cert); // Set the location of the CA-bundle
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return contents as a string

		$response = curl_exec ($ch);
		curl_close($ch);
		return $response;
	}

	/**
	 * @param $photonic_picasa_refresh_token
	 */
	public function perform_back_end_authentication($photonic_picasa_refresh_token) {
		$photonic_authentication = get_option('photonic_authentication');
		if (!isset($photonic_authentication)) {
			$photonic_authentication = array();
		}
		if (!isset($photonic_authentication['picasa']) && !empty($photonic_picasa_refresh_token)) {
			$token = $this->get_access_token_from_refresh('picasa', $photonic_picasa_refresh_token, true);
		}
		else if (isset($photonic_authentication['picasa'])) {
			$token = $photonic_authentication['picasa'];
			if (isset($token)) {
				if ($this->is_token_expired($token)) {
					$token = $this->get_access_token_from_refresh('picasa', $photonic_picasa_refresh_token, true);
				}
			}
		}
		if (!empty($token)) {
			$this->access_token = $token['oauth_token'];
		}
	}
}
