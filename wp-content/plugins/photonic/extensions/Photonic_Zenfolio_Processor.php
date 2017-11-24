<?php
/**
 * Processor for Zenfolio photos. This extends the Photonic_Processor class and defines methods local to Zenfolio.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Zenfolio_Processor extends Photonic_Processor {
	var $user_name, $user_agent, $token, $service_url, $secure_url, $unlocked_realms;
	function __construct() {
		parent::__construct();
		global $photonic_zenfolio_disable_title_link;
		$this->provider = 'zenfolio';
		$this->user_agent = "Photonic for ".get_home_url();
		$this->link_lightbox_title = empty($photonic_zenfolio_disable_title_link);
		$query_url = add_query_arg('dummy', 'dummy');
		$query_url = remove_query_arg('dummy');
		if (stripos($query_url, ':') === FALSE) {
			$protocol = 'http';
		}
		else {
			$protocol = substr($query_url, 0, stripos($query_url, ':'));
		}

		$this->service_url = 'http://api.zenfolio.com/api/1.8/zfapi.asmx';
		$this->secure_url = 'https://api.zenfolio.com/api/1.8/zfapi.asmx';
		$this->unlocked_realms = array();
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @param array $attr
	 * @return mixed|string|void
	 */
	public function get_gallery_images($attr = array()) {
		global $photonic_zenfolio_thumb_size, $photonic_zenfolio_main_size, $photonic_zenfolio_tile_size, $photonic_zenfolio_title_caption;

		$attr = array_merge(
			$this->common_parameters,
			array(
				'caption' => $photonic_zenfolio_title_caption,
				'thumb_size' => $photonic_zenfolio_thumb_size,
				'main_size' => $photonic_zenfolio_main_size,
				'tile_size' => $photonic_zenfolio_tile_size,

				'limit' => 20,
				'offset' => 0,
			), $attr);
		$attr = array_map('trim', $attr);

		extract($attr);

		if (isset($_COOKIE['photonic-zf-keyring'])) {
			$realms = $this->make_call('KeyringGetUnlockedRealms', array('keyring' => $_COOKIE['photonic-zf-keyring']));
			if (!empty($realms) && !empty($realms->result)) {
				$this->unlocked_realms = $realms->result;
			}
		}

		$chained_methods = array();
		$zenfolio_params = array();
		$attr['headers_already_called'] = true;
		if (!empty($attr['view'])) {
			switch ($attr['view']) {
				case 'photos':
					if (!empty($object_id)) {
						$chained_methods[] = 'LoadPhoto';
						if(($h = stripos($object_id, 'h')) !== false) {
							$object_id = substr($object_id, $h + 1);
							$object_id = hexdec($object_id);
						}
						else if (($p = stripos($object_id, 'p')) !== false) {
							$object_id = substr($object_id, $p + 1);
						}
						else if (strlen($object_id) == 7) {
							$object_id = hexdec($object_id);
						}

						$zenfolio_params['photoId'] = $object_id;
						$zenfolio_params['level'] = 'Full';
					}
					else if (!empty($text)) {
						$zenfolio_params['searchId'] = '';
						if (!empty($sort_order)) {
							$zenfolio_params['sortOrder'] = $sort_order; // Popularity | Date | Rank
						}
						else {
							$zenfolio_params['sortOrder'] = 'Date';
						}
						$zenfolio_params['query'] = $text;
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
						$chained_methods[] = 'SearchPhotoByText';
					}
					else if (!empty($category_code)) {
						$zenfolio_params['searchId'] = '';
						if (!empty($sort_order)) {
							$zenfolio_params['sortOrder'] = $sort_order; // Popularity | Date
						}
						else {
							$zenfolio_params['sortOrder'] = 'Date';
						}
						$zenfolio_params['categoryCode'] = $category_code;
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
						$chained_methods[] = 'SearchPhotoByCategory';
					}
					else if (!empty($kind)) {
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
						switch ($kind) {
							case 'popular':
								$chained_methods[] = 'GetPopularPhotos';
								break;

							case 'recent':
								$chained_methods[] = 'GetRecentPhotos';
								break;

							default:
								return __('Invalid <code>kind</code> parameter.', 'photonic');
						}
					}
					else {
						return __('The <code>kind</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					break;

				case 'photosets':
					if (!empty($object_id)) {
						if(($p = stripos($object_id, 'p')) !== false) {
							$object_id = substr($object_id, $p + 1);
						}

						$zenfolio_params['photosetId'] = $object_id;
						$zenfolio_params['level'] = 'Level1';
						$zenfolio_params['includePhotos'] = false;

						if (!empty($password) && empty($realm_id)) {
							$first_call = $this->make_call('LoadPhotoSet', $zenfolio_params);
							if (isset($first_call->result) && !empty($first_call->result)) {
								$photoset = $first_call->result;
								if (isset($photoset->AccessDescriptor)) {
									$realm_id = $photoset->AccessDescriptor->Id;
								}
							}
						}

						if (!empty($password) && !empty($realm_id)) {
							if (!in_array($realm_id, $this->unlocked_realms)) {
								$attr['headers_already_called'] = empty($attr['panel']); //false;
								$chained_methods[] = 'KeyringAddKeyPlain';
								$zenfolio_params['keyring'] = empty($_COOKIE['photonic-zf-keyring']) ? '' : $_COOKIE['photonic-zf-keyring'];
								$zenfolio_params['realmId'] = $realm_id;
								$zenfolio_params['password'] = $password;
							}
						}

						$chained_methods[] = 'LoadPhotoSet';
						$zenfolio_params['startingIndex'] = $attr['offset'];
						$zenfolio_params['numberOfPhotos'] = $attr['limit'];
						$chained_methods[] = 'LoadPhotoSetPhotos';
					}
					else if (!empty($text) && !empty($photoset_type)) {
						$zenfolio_params['searchId'] = '';
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$zenfolio_params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$zenfolio_params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						if (!empty($sort_order)) {
							$zenfolio_params['sortOrder'] = $sort_order; // Popularity | Date | Rank
						}
						else {
							$zenfolio_params['sortOrder'] = 'Rank';
						}
						$zenfolio_params['query'] = $text;
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
						$chained_methods[] = 'SearchSetByText';
					}
					else if (!empty($category_code) && !empty($photoset_type)) {
						$zenfolio_params['searchId'] = '';
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$zenfolio_params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$zenfolio_params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						if (!empty($sort_order)) {
							$zenfolio_params['sortOrder'] = $sort_order; // Popularity | Date
						}
						else {
							$zenfolio_params['sortOrder'] = 'Date';
						}
						$zenfolio_params['categoryCode'] = $category_code;
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
						$chained_methods[] = 'SearchSetByCategory';
					}
					else if (!empty($kind) && !empty($photoset_type)) {
						switch ($kind) {
							case 'popular':
								$chained_methods[] = 'GetPopularSets';
								break;

							case 'recent':
								$chained_methods[] = 'GetRecentSets';
								break;

							default:
								return __('Invalid <code>kind</code> parameter.', 'photonic');
						}
						if (strtolower($photoset_type) == 'gallery' || strtolower($photoset_type) == 'galleries') {
							$zenfolio_params['type'] = 'Gallery';
						}
						else if (strtolower($photoset_type) == 'collection' || strtolower($photoset_type) == 'collections') {
							$zenfolio_params['type'] = 'Collection';
						}
						else {
							return __('Invalid <code>photoset_type</code> parameter.', 'photonic');
						}

						// These have to be after the $params['type'] assignment
						$zenfolio_params['offset'] = $attr['offset'];
						$zenfolio_params['limit'] = $attr['limit'];
					}
					else if (!empty($filter)) {
						//
					}
					else if (empty($kind)) {
						return __('The <code>kind</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					else if (empty($photoset_type)) {
						return __('The <code>photoset_type</code> parameter is required if <code>object_id</code> is not specified.', 'photonic');
					}
					break;

				case 'hierarchy':
					if (empty($login_name)) {
						return __('The <code>login_name</code> parameter is required.', 'photonic');
					}
					$chained_methods[] = 'LoadGroupHierarchy';
					$zenfolio_params['loginName'] =  $login_name;
					break;

				case 'group':
					if (empty($object_id)) {
						return __('The <code>object_id</code> parameter is required.', 'photonic');
					}
					$chained_methods[] = 'LoadGroup';
					if(($f = stripos($object_id, 'f')) !== false) {
						$object_id = substr($object_id, $f + 1);
					}
					$zenfolio_params['groupId'] =  $object_id;
					$zenfolio_params['level'] = 'Full';
					$zenfolio_params['includeChildren'] = true;
					break;
			}
		}

		$this->gallery_index++;

		if (!empty($attr['panel'])) {
			$attr['display'] = 'popup';
		}
		else {
			$attr['display'] = 'in-page';
		}

		$header_display = $this->get_header_display($attr);
		$attr['header_display'] = $header_display;

		$level_2_meta = array(
			'start' => $attr['offset'],
			'per-page' => $attr['limit'],
		);
		$attr['level_2_meta'] = $level_2_meta;

		$call_return = $this->make_chained_calls($chained_methods, $zenfolio_params, $attr);

		if ($call_return == __('This album is password-protected. Please provide a valid password.', 'photonic')) {
			return __('This album is password-protected. Please provide a valid password.', 'photonic');
		}
		else if (empty($call_return)) {
			return '';
		}

		return $this->finalize_markup($call_return, $attr);
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public function parse_token($response) {
		// TODO: Update content when authentication gets supported
	}

	/**
	 * Calls a Zenfolio method with the passed parameters. The method is called using JSON-RPC. WP's wp_remote_request
	 * method doesn't work here because of specific CURL parameter requirements.
	 *
	 * @param $method
	 * @param $params
	 * @param bool $force_secure
	 * @return array|mixed
	 */
	function make_call($method, $params, $force_secure = false, $keyring = null) {
		$request['method'] = $method;
		$request['params'] = array_values($params);
		$request['id'] = 1;
		$bodyString = json_encode($request);
		$bodyLength = strlen($bodyString);

		$headers = array();
		$headers[] = 'Host: api.zenfolio.com';
		$headers[] = 'User-Agent: '.$this->user_agent;
		if($this->token) {
			$headers[] = 'X-Zenfolio-Token: '.$this->token;
		}
		if (isset($_COOKIE['photonic-zf-keyring'])) {
			$headers[] = 'X-Zenfolio-Keyring: '.$_COOKIE['photonic-zf-keyring'];
		}
		else if (!empty($keyring)) {
			$headers[] = 'X-Zenfolio-Keyring: '.$keyring;
		}
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Content-Length: '.$bodyLength."\r\n";
		$headers[] = $bodyString;

		if ($force_secure) {
//			$ch = curl_init($this->service_url);
			$ch = curl_init($this->secure_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//			curl_setopt($ch, CURLOPT_CAINFO, $cert); // Set the location of the CA-bundle
		}
		else {
			$ch = curl_init($this->service_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		$response = curl_exec($ch);

		curl_close($ch);

		//PHP/WP's json_decode() function really messes up the "long" ids returned by Zenfolio. The following takes care of this.
		// Can't pass the 4th argument as outlined here: http://php.net/manual/en/function.json-decode.php, since it only came into existence in PHP 5.4
		$response = preg_replace('/"Id":(\d+)/', '"Id":"$1"', $response);
		$response = preg_replace('/"RealmId":(\d+)/', '"Id":"$1"', $response);
		if ($method == 'KeyringGetUnlockedRealms') {
			$realm_ids = array();
			preg_match('/([\[^,\d]+\])/', $response, $realm_ids);
			if (!empty($realm_ids)) {
				$realm_ids = $realm_ids[0];
				$replace = $realm_ids;
				$replace = str_replace('[', '["', $replace);
				$replace = str_replace(']', '"]', $replace);
				$replace = str_replace(',', '","', $replace);
				$response = str_replace($realm_ids, $replace, $response);
			}
		}

		$response = json_decode($response);
		return $response;
	}

	/**
	 * Makes a sequence of calls to different Zenfolio methods. This is particularly useful in case of authenticated calls, where
	 * first the authentication happens, then the content is displayed, all in the same call.
	 *
	 * @param $methods
	 * @param $zenfolio_args
	 * @param array $short_code
	 * @return string|void
	 */
	function make_chained_calls($methods, $zenfolio_args, &$short_code = array()) {
		$ret = '';
		$keyring = null;
//		$force_secure = false;
		$force_secure = is_ssl();
		$original_params = array();
		foreach ($zenfolio_args as $param => $value) {
			$original_params[$param] = $value;
		}

		foreach ($methods as $method) {
			$keyring_params = array();
			if ($method == 'KeyringGetUnlockedRealms') {
				$keyring_params['keyring'] = $zenfolio_args['keyring'];
				$response = $this->make_call($method, $keyring_params, $force_secure);
				if (isset($response->result)) {
					$this->unlocked_realms = $response->result;
				}
			}
			else if ($method == 'KeyringAddKeyPlain') {
				if (in_array($zenfolio_args['realmId'], $this->unlocked_realms)) {
					continue;
				}
				$keyring_params['keyring'] = $zenfolio_args['keyring'];
				$keyring_params['realmId'] = $zenfolio_args['realmId'];
				$keyring_params['password'] = $zenfolio_args['password'];
				$response = $this->make_call($method, $keyring_params, $force_secure);

				if (!empty($response->result)) {
					// Sometimes the cookie isn't set by the setcookie command (happens when the password is passed as a shortcode parameter
					// instead of the password prompt)
					$keyring = $response->result;
					if (!in_array($keyring_params['realmId'], $this->unlocked_realms)) {
						$this->unlocked_realms[] = $keyring_params['realmId'];
					}

					if (!$short_code['headers_already_called']) {
						setcookie('photonic-zf-keyring', $response->result, time() + 60 * 60 * 24, COOKIEPATH);
					}
				}
				else {
					$ret = __('This album is password-protected. Please provide a valid password.', 'photonic');
					break;
				}
			}
			else {
				foreach ($original_params as $param => $value) {
					$zenfolio_args[$param] = $value;
				}

				$keyring_fields = array('keyring', 'realmId', 'password');
				foreach ($zenfolio_args as $param => $value) {
					if (in_array($param, $keyring_fields)) {
						unset($zenfolio_args[$param]);
					}
				}

				if ($method === 'LoadPhotoSetPhotos') {
					unset($zenfolio_args['level']);
					unset($zenfolio_args['includePhotos']);
				}
				else if ($method === 'LoadPhotoSet') {
					unset($zenfolio_args['startingIndex']);
					unset($zenfolio_args['numberOfPhotos']);
				}

				$response = $this->make_call($method, $zenfolio_args, $force_secure, $keyring);
				$ret .= $this->process_response($method, $response, $short_code);
			}
		}
		return $ret;
	}

	/**
	 * Routing function that takes the response and redirects it to the appropriate processing function.
	 *
	 * @param $method
	 * @param $response
	 * @param array $short_code
	 * @return mixed|string|void
	 */
	function process_response($method, $response, &$short_code = array()) {
		$header_display = $short_code['header_display'];
		$level_2_meta = $short_code['level_2_meta'];

		if (!empty($response->result)) {
			$result = $response->result;
			$ret = '';

			switch ($method) {
				case 'GetPopularPhotos':
				case 'GetRecentPhotos':
				case 'SearchPhotoByText':
				case 'SearchPhotoByCategory':
					$level_2_meta['total'] = $result->TotalCount;
					$short_code['level_2_meta'] = $level_2_meta;
					$ret = $this->process_photos($result, 'stream', $short_code);
					break;

				case 'LoadPhoto':
					$ret = $this->process_photo($result, $short_code);
					break;

				case 'GetPopularSets':
				case 'GetRecentSets':
				case 'SearchSetByText':
				case 'SearchSetByCategory':
					$ret = $this->process_sets($result, $short_code);
					break;

				case 'LoadPhotoSet':
				case 'LoadPhotoSetPhotos':
					if (isset($result->ImageCount)) {
						$level_2_meta['total'] = $result->ImageCount;
						$short_code['level_2_meta'] = $level_2_meta;
					}
					$ret = $this->process_set($result, array('header_display' => $header_display), $short_code);
					break;

				case 'LoadGroupHierarchy':
					$ret = $this->process_group_hierarchy($result, array('header_display' => $header_display), $short_code);
					break;

				case 'LoadGroup':
					$ret = $this->process_group($result, array('header_display' => $header_display), $short_code);
					break;
			}
			return $ret;
		}
		else if ($response == __('This album is password-protected. Please provide a valid password.', 'photonic')) {
			return $response;
		}
		else if (!empty($response->error)) {
			if (!empty($response->error->message)) {
				return $response->error->message;
			}
			else {
				return __('Unknown error', 'photonic');
			}
		}
		else {
			return __('Unknown error', 'photonic');
		}
	}

	/**
	 * Takes an array of photos and displays each as a thumbnail. Each thumbnail, upon clicking launches a lightbox.
	 *
	 * @param $response
	 * @param string $parent
	 * @param array $short_code
	 * @return mixed|string|void
	 */
	function process_photos($response, $parent, $short_code = array()) {
		if (!is_array($response)) {
			if (empty($response->Photos) || !is_array($response->Photos)) {
				return __('Response is not an array', 'photonic');
			}
			$response = $response->Photos;
		}

		global $photonic_zenfolio_photos_per_row_constraint, $photonic_zenfolio_photo_title_display, $photonic_zenfolio_photos_constrain_by_padding, $photonic_zenfolio_photos_constrain_by_count;
		$ret = '';
		$row_constraints = array('constraint-type' => $photonic_zenfolio_photos_per_row_constraint, 'padding' => $photonic_zenfolio_photos_constrain_by_padding, 'count' => $photonic_zenfolio_photos_constrain_by_count);
		$photo_objects = $this->build_level_1_objects($response, $short_code);

		$level_2_meta = $short_code['level_2_meta'];
		$level_2_meta['end'] = $level_2_meta['start'] + count($response);

		$ret .= $this->display_level_1_gallery($photo_objects,
			array(
				'title_position' => $photonic_zenfolio_photo_title_display,
				'row_constraints' => $row_constraints,
				'parent' => $parent,
				'level_2_meta' => $level_2_meta,
			),
			$short_code
		);

		return $ret;
	}

	function build_level_1_objects($response, $short_code = array()) {
		if (!is_array($response)) {
			if (empty($response->Photos) || !is_array($response->Photos)) {
				return __('Response is not an array', 'photonic');
			}
			$response = $response->Photos;
		}

		$tile_size = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $short_code['main_size'] : $short_code['tile_size'];

		$type = '$type';
		$photo_objects = array();
		foreach ($response as $photo) {
			if (empty($photo->$type) || $photo->$type != 'Photo') {
				continue;
			}
			$appendage = array();
			if (isset($photo->Sequence)) {
				$appendage[] = 'sn='.$photo->Sequence;
			}
			if (isset($photo->UrlToken)) {
				$appendage[] = 'tk='.$photo->UrlToken;
			}

			$photo_object = array();
			$photo_object['thumbnail'] = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$short_code['thumb_size'].'.jpg';
			$photo_object['main_image'] = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$short_code['main_size'].'.jpg';
			$photo_object['tile_image'] = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$tile_size.'.jpg';
			$photo_object['download'] = $photo_object['main_image'].'?'.implode('&', $appendage);
			$photo_object['title'] = $photo->Title;
			$photo_object['alt_title'] = $photo->Title;
			$photo_object['description'] = $photo->Caption;
			$photo_object['main_page'] = $photo->PageUrl;
			$photo_object['id'] = $photo->Id;

			$photo_objects[] = $photo_object;
		}

		return $photo_objects;
	}

	function build_level_2_objects($response, $short_code = array()) {
		global $photonic_zenfolio_hide_password_protected_thumbnail;
		$tile_size = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $short_code['main_size'] : $short_code['tile_size'];

		$objects = array();
		foreach ($response as $photoset) {
			if (empty($photoset->TitlePhoto)) {
				continue;
			}
			if (!empty($photoset->AccessDescriptor) && !empty($photoset->AccessDescriptor->AccessType) && $photoset->AccessDescriptor->AccessType == 'Password' && !empty($photonic_zenfolio_hide_password_protected_thumbnail)) {
				continue;
			}

			$object = array();

			$photo = $photoset->TitlePhoto;
			$object['id_1'] = $photoset->Id;
			$object['thumbnail'] = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$short_code['thumb_size'].'.jpg';
			$object['tile_image'] = 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$tile_size.'.jpg';
			$object['main_page'] = $photoset->PageUrl;
			$object['title'] = esc_attr($photoset->Title);
			$object['counter'] = $photoset->PhotoCount;
			$object['data_attributes'] = array('thumb-size' => $short_code['thumb_size']);

			if (!empty($photoset->AccessDescriptor) && !empty($photoset->AccessDescriptor->AccessType) && $photoset->AccessDescriptor->AccessType == 'Password') {
				if (!in_array($photoset->AccessDescriptor->Id, $this->unlocked_realms)) {
					$object['classes'] = array('photonic-zenfolio-passworded');
					$object['passworded'] = 1;
					$object['realm_id'] = $photoset->AccessDescriptor->Id;
					$object['data_attributes']['realm'] = $photoset->AccessDescriptor->Id;
				}
			}
			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Prints a single photo with the title as an <h3> and the caption as the image caption.
	 *
	 * @param $photo
	 * @param $short_code
	 * @return string
	 */
	function process_photo($photo, $short_code) {
		$type = '$type';
		if (empty($photo->$type) || $photo->$type != 'Photo') {
			return '';
		}

		return $this->generate_single_photo_markup('zenfolio', array(
				'src' => 'http://'.$photo->UrlHost.$photo->UrlCore.'-'.$short_code['main_size'].'.jpg',
				'href' => $photo->PageUrl,
				'title' => $photo->Title,
				'caption' => $photo->Caption,
			)
		);
	}

	/**
	 * Takes an array of photosets and displays a thumbnail for each of them. Password-protected thumbnails might be excluded via the options.
	 *
	 * @param $response
	 * @param array $short_code
	 * @return mixed|string|void
	 */
	function process_sets($response, $short_code = array()) {
		if (!is_array($response)) {
			if (empty($response->PhotoSets) || !is_array($response->PhotoSets)) {
				return __('Response is not an array', 'photonic');
			}
			$response = $response->PhotoSets;
		}

		global $photonic_zenfolio_sets_per_row_constraint, $photonic_zenfolio_sets_constrain_by_count, $photonic_picasa_photos_pop_constrain_by_padding,
			$photonic_zenfolio_set_title_display, $photonic_zenfolio_hide_set_photos_count_display;
		$row_constraints = array('constraint-type' => $photonic_zenfolio_sets_per_row_constraint, 'padding' => $photonic_picasa_photos_pop_constrain_by_padding, 'count' => $photonic_zenfolio_sets_constrain_by_count);
		$objects = $this->build_level_2_objects($response, $short_code);
		$ret = $this->display_level_2_gallery($objects,
			array(
				'row_constraints' => $row_constraints,
				'type' => 'photosets',
				'singular_type' => 'set',
				'title_position' => $photonic_zenfolio_set_title_display,
				'level_1_count_display' => $photonic_zenfolio_hide_set_photos_count_display,
			),
			$short_code
		);
		return $ret;
	}

	/**
	 * Displays a header with a basic summary for a photoset, along with thumbnails for all associated photos.
	 *
	 * @param $response
	 * @param array $options
	 * @param array $short_code
	 * @return string
	 */
	function process_set($response, $options = array(), &$short_code = array()) {
		$ret = '';
		$level_2_meta = $short_code['level_2_meta'];
		if (!is_array($response)) {
			global $photonic_zenfolio_link_set_page, $photonic_zenfolio_hide_set_thumbnail, $photonic_zenfolio_hide_set_title, $photonic_zenfolio_hide_set_photo_count;

			$header = $this->get_header_object($response, $short_code['thumb_size']);
			$hidden = array('thumbnail' => !empty($photonic_zenfolio_hide_set_thumbnail), 'title' => !empty($photonic_zenfolio_hide_set_title), 'counter' => !empty($photonic_zenfolio_hide_set_photo_count));
			$counters = array('photos' => $response->ImageCount);

			$level_2_meta['total'] = $response->ImageCount;
			$short_code['level_2_meta'] = $level_2_meta;
			$ret .= $this->process_object_header($header,
				array(
					'type' => 'set',
					'hidden' => $this->get_hidden_headers($options['header_display'], $hidden),
					'counters' => $counters,
					'link' => empty($photonic_zenfolio_link_set_page),
					'display' => $short_code['display'],
				)
			);
		}
		else {
			$ret .= $this->process_photos($response, 'set', $short_code);
		}
		return $ret;
	}

	/**
	 * Takes a Zenfolio response object and converts it into an associative array with a title, a thumbnail URL and a link URL.
	 *
	 * @param $object
	 * @param $thumb_size
	 * @return array
	 */
	public function get_header_object($object, $thumb_size) {
		$header = array();

		if (!empty($object->Title)) {
			$header['title'] = $object->Title;
			if (!empty($object->TitlePhoto)) {
				$photo = $object->TitlePhoto;
				$header['thumb_url'] = 'http://' . $photo->UrlHost . $photo->UrlCore . '-' . $thumb_size . '.jpg';
			}
			$header['link_url'] = $object->PageUrl;
		}

		return $header;
	}

	/**
	 * For a given user this prints out the group hierarchy. This starts with the root level and first prints all immediate
	 * children photosets. It then goes into each child group and recursively displays the photosets for each of them in separate sections.
	 *
	 * @param $response
	 * @param array $options
	 * @param array $short_code
	 * @return mixed|string|void
	 */
	function process_group_hierarchy($response, $options = array(), $short_code = array()) {
		if (empty($response->Elements)) {
			return __('No galleries, collections or groups defined for this user', 'photonic');
		}

		$ret = $this->process_group($response, $options, $short_code);
		return $ret;
	}

	/**
	 * For a given group this displays the immediate children photosets and then recursively displays all children groups.
	 *
	 * @param $group
	 * @param array $options
	 * @param array $short_code
	 * @return string
	 */
	function process_group($group, $options = array(), $short_code = array()) {
		$ret = '';
		$type = '$type';
		if (!isset($group->Elements)) {
			$object_id = $group->Id;
			$method = 'LoadGroup';
			if(($f = stripos($object_id, 'f')) !== false) {
				$object_id = substr($object_id, $f + 1);
			}
			$params = array();
			$params['groupId'] =  $object_id;
			$params['level'] = 'Full';
			$params['includeChildren'] = true;
			$response = $this->make_call($method, $params);
			if (!empty($response->result)) {
				$group = $response->result;
			}
		}

		if (empty($group->Elements)) {
			return '';
		}

		$elements = $group->Elements;
		$photosets = array();
		$groups = array();
		global $photonic_zenfolio_hide_password_protected_thumbnail;
		$image_count = 0;
		foreach ($elements as $element) {
			if ($element->$type == 'PhotoSet') {
				if (!empty($element->AccessDescriptor) && !empty($element->AccessDescriptor->AccessType) && $element->AccessDescriptor->AccessType == 'Password' && !empty($photonic_zenfolio_hide_password_protected_thumbnail)) {
					continue;
				}
				$photosets[] = $element;
				$image_count += $element->ImageCount;
			}
			else if ($element->$type == 'Group') {
				$groups[] = $element;
			}
		}

		global $photonic_zenfolio_hide_empty_groups;
		if (!empty($group->Title) && ($image_count > 0 || empty($photonic_zenfolio_hide_empty_groups))) {
			global $photonic_zenfolio_link_group_page, $photonic_zenfolio_hide_group_title, $photonic_zenfolio_hide_group_photo_count, $photonic_zenfolio_hide_group_group_count, $photonic_zenfolio_hide_group_set_count;
			$header = $this->get_header_object($group, $short_code['thumb_size']);

			$hidden = array(
				'thumbnail' => true,
				'title' => !empty($photonic_zenfolio_hide_group_title),
				'counter' => !(empty($photonic_zenfolio_hide_group_photo_count) || empty($photonic_zenfolio_hide_group_group_count) || empty($photonic_zenfolio_hide_group_set_count)),
			);
			$counters = array(
				'sets' => empty($photonic_zenfolio_hide_group_set_count) ? count($photosets) : 0,
				'groups' => empty($photonic_zenfolio_hide_group_group_count) ? count($groups) : 0,
				'photos' => empty($photonic_zenfolio_hide_group_photo_count)? $image_count : 0,
			);

			$ret .= $this->process_object_header($header,
				array(
					'type' => 'set',
					'hidden' => $this->get_hidden_headers($options['header_display'], $hidden),
					'counters' => $counters,
					'link' => empty($photonic_zenfolio_link_group_page),
					'display' => $short_code['display'],
				)
			);
		}

		$ret .= $this->process_sets($photosets, $short_code);

		foreach ($groups as $group) {
			$ret .= $this->process_group($group, $options, $short_code);
		}

		return $ret;
	}
}
