<?php
class Photonic_SmugMug_Processor extends Photonic_OAuth1_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_smug_api_key, $photonic_smug_api_secret, $photonic_smug_disable_title_link;
		$this->api_key = trim($photonic_smug_api_key);
		$this->api_secret = trim($photonic_smug_api_secret);
		$this->provider = 'smug';
		$this->link_lightbox_title = empty($photonic_smug_disable_title_link);
		$this->oauth_version = '1.0a';
		$this->base_url = 'https://api.smugmug.com/api/v2/';
	}

	/**
	 * The main gallery builder for SmugMug. SmugMug takes the following parameters:
	 * 	- nick_name = The nickname of the user. This is mandatory for SmugMug.
	 * 	- view = tree | albums | album | images. If left blank, a value of 'tree' is assumed.
	 * 	- columns = The number of columns to show the output in
	 *	- album or album_key = The album slug, which is the AlbumKey. This is needed if view='album' or 'images'. Prior to version 1.57 album_id was required
	 *	- empty = true | false. If true, empty albums and categories are returned in the response, otherwise they are ignored.
	 *	- columns = The number of columns to return the output in. Optional.
	 *
	 * @param array $attr
	 * @return string|void
	 */
	function get_gallery_images($attr = array()) {
		global $photonic_smug_allow_oauth, $photonic_smug_oauth_done, $photonic_smug_title_caption;
		global $photonic_smug_thumb_size, $photonic_smug_main_size, $photonic_smug_tile_size;

		if (empty($this->api_key)) {
			return __("SmugMug API Key not defined", 'photonic');
		}

		$attr = array_merge(
			$this->common_parameters,
			array(
				'caption' => $photonic_smug_title_caption,
				'thumb_size' => $photonic_smug_thumb_size,
				'main_size' => $photonic_smug_main_size,
				'tile_size' => $photonic_smug_tile_size,

				'empty' => 'false',
				'view' => 'tree',
				'nick_name' => '',
				'start' => 1,
				'count' => 100,
			),
			$attr);

		$attr = array_map('trim', $attr);
		extract($attr);

		$args = array(
			'APIKey' => $this->api_key,
			'_accept' => 'application/json'
		);

		$chained_calls = array();

		$args['_expandmethod'] = 'inline';
		$args['_verbosity'] = '1';

		if ($attr['view'] == 'tree' || $attr['view'] == 'albums') {
			if (empty($attr['nick_name'])) {
				return "";
			}
		}

		switch ($attr['view']) {
			case 'albums':
				if (!empty($attr['site_password'])) {
					$chained_calls[] = $this->base_url.'user/'.$attr['nick_name'].'!unlock';
					$args['Password'] = $attr['site_password'];
				}

				$chained_calls[] = $this->base_url.'user/'.$attr['nick_name'].'!albums';
				$args['_expand'] = 'HighlightImage.ImageSizes';

				break;

			case 'album':
			case 'images':
				$album_field = '';
				if (!empty($attr['album_key'])) {
					$album_field = $attr['album_key'];
				}
				else if (!empty($attr['album'])) {
					if (stripos($attr['album'], '_') === FALSE) {
						$album_field = $attr['album'];
					}
					else {
						$album_field = substr($attr['album'], stripos($attr['album'], '_') + 1);
					}
				}
				if (!empty($attr['password']) || !empty($attr['site_password'])) {
					$args['Password'] = !empty($attr['password']) ? $attr['password'] : $attr['site_password'];
					$chained_calls[] = $this->base_url.'album/'.$album_field.'!unlock';
				}

				/*
					Weird issue: If _expand is called with a comma-separated list, and is passed in $args[], it doesn't expand everything.
					So, for comma-separated values, _expand is appended to the URL.
					However, if we do this, and OAuth is being used, the signature generation gets messed up, as the signature is generated with the "?_expand=...",
					which doesn't match the signature for when we make a call with $signed_args, because SmugMug internally parses out the URL and puts _expand=...
					somewhere in the middle. So, to fix, for authenticated calls we are separating out the _expand parameter and making two calls. Additionally we are
					moving the parameter from the URL to the array in the generate_signature method.

					Update, v1.59 - Using _expand and combining both the expansions prevents the usage of the <code>start</code> and <code>count</code>
					attributes from the album node. So, splitting the call anyway, to introduce the album!images node...
				*/
				$chained_calls[] = $this->base_url.'album/'.$album_field.'?_expand=HighlightImage.ImageSizes';
				$chained_calls[] = $this->base_url.'album/'.$album_field.'!images?_expand=ImageSizes';

				break;

			case 'folder':
				$expand = array();
				$expand[] = 'ChildNodes.ChildNodes.ChildNodes.ChildNodes.ChildNodes';
				$expand[] = 'ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'ChildNodes.ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'ChildNodes.ChildNodes.ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand_str = '_expand='.implode(',', $expand);

				if (!empty($attr['folder'])) {
					if ($photonic_smug_allow_oauth && $photonic_smug_oauth_done) {
						foreach ($expand as $idx => $expansion) {
							$chained_calls[] = $this->base_url.'node/'.$attr['folder'].'?_expand='.$expansion;
						}
					}
					else {
						$chained_calls[] = $this->base_url.'node/'.$attr['folder'].'?'.$expand_str;
					}
				}
				break;

			case 'tree':
			default:
				if (!empty($attr['site_password'])) {
					$chained_calls[] = $this->base_url.'user/'.$attr['nick_name'].'!unlock';
					$args['Password'] = $attr['site_password'];
				}

				$expand = array();
				$expand[] = 'Node.ChildNodes.ChildNodes.ChildNodes.ChildNodes.ChildNodes';
				$expand[] = 'Node.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'Node.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'Node.ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'Node.ChildNodes.ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand[] = 'Node.ChildNodes.ChildNodes.ChildNodes.ChildNodes.ChildNodes.Album.HighlightImage.ImageSizes';
				$expand_str = '_expand='.implode(',', $expand);
				// Passing _expand as a parameter somehow doesn't work, but concatenating it to the URL does. So...
				if ($photonic_smug_allow_oauth && $photonic_smug_oauth_done) {
					foreach ($expand as $idx => $expansion) {
						if ($idx !== 0) {
							$chained_calls[] = $this->base_url.'user/'.$attr['nick_name'].'?_expand='.$expansion;
						}
					}
				}
				else {
					$chained_calls[] = $this->base_url.'user/'.$attr['nick_name'].'?'.$expand_str;
				}

				break;
		}

		if (!empty($attr['nick_name'])) {
			$args['NickName'] = $attr['nick_name'];
		}

		if (!empty($attr['start'])) {
			$args['start'] = $attr['start'];
		}

		if (!empty($attr['count'])) {
			$args['count'] = $attr['count'];
		}

		$ret = '';
		if ($photonic_smug_allow_oauth && is_singular() && !$photonic_smug_oauth_done) {
			$post_id = get_the_ID();
			$ret .= $this->get_login_box($post_id);
		}

		$header_display = $this->get_header_display($attr);
		$attr['header_display'] = $header_display;

		return $ret.$this->make_chained_calls($chained_calls, $args, $attr);
	}

	/**
	 * Runs a sequence of web-service calls to get information. Most often a single web-service call with the "Extras" parameter suffices for SmugMug.
	 * But there are some scenarios, e.g. clicking on an album to get a popup of all images in that album, where you need to chain the calls for the header.
	 *
	 * @param $chained_calls
	 * @param $smug_args
	 * @param $short_code
	 * @return string
	 */
	function make_chained_calls($chained_calls, $smug_args, $short_code) {
		$header_done = false;

		if (is_array($chained_calls) && count($chained_calls) > 0) {
			$this->gallery_index++;
			extract($short_code);

			$ret = '';
			global $photonic_smug_oauth_done;

			$cookies = array();
			$insert = '';
			foreach ($chained_calls as $chained_call) {
				if (stripos($chained_call, '!unlock') !== FALSE) {
					$response = Photonic::http($chained_call, 'POST', $smug_args);
					if (is_array($response['response']) && isset($response['response']['code']) && $response['response']['code'] === 200) {
						$cookies = $response['cookies'];
						foreach($response['cookies'] as $cookie) {
							$cookies[$cookie->name] = $cookie->value;
						}
					}
				}

				// The following is NOT an "else" because we are modifying the headers with the cookies, if required
				if (stripos($chained_call, '!unlock') === FALSE) {
					if ($photonic_smug_oauth_done && empty($cookies)) {
						$signed_args = $this->sign_call($chained_call, 'GET', $smug_args);
						$response = Photonic::http($chained_call, 'GET', $signed_args, null, 30, false, array(), $cookies);
					}
					else {
						$response = Photonic::http($chained_call, 'GET', $smug_args, null, 30, false, array(), $cookies);
					}

					if (!is_wp_error($response)) {
						$body = $response['body'];
						$body = json_decode($body);

						if ($body->Code === 200) {
							$body = $body->Response;

							if (stripos($chained_call, '!albums') !== FALSE) {
								$albums = $body->Album;
								if (is_array($albums) && count($albums) > 0) {
									$album_text = $this->process_albums($albums, '', $short_code['filter'], $short_code);
									if (!empty($album_text)) {
										$ret .= $this->finalize_markup($album_text, $short_code);
									}
								}
							}
							else if (stripos($chained_call, '/album/') !== FALSE && stripos($chained_call, '!unlock') === FALSE) {
								$password_check_passed = true;
								if (isset($body->Album)) {
									$album = $body->Album;
									$password_check_passed = ($album->SecurityType == 'Password' && isset($album->Uris->AlbumImages)) || $album->SecurityType != 'Password';

									$header_object = array();
									$header_object['title'] = $album->Name;
									$header_object['link_url'] = $album->WebUri;

									if (isset($album->Uris->HighlightImage->Image)) {
										$header_object['thumb_url'] = $album->Uris->HighlightImage->Image->ThumbnailUrl;
									}
									else if (isset($album->Uris->AlbumImages->AlbumImage) && is_array($album->Uris->AlbumImages->AlbumImage)) {
										$rand = rand(0, $album->ImageCount - 1);
										$header_object['thumb_url'] = $album->Uris->AlbumImages->AlbumImage[$rand]->ThumbnailUrl;
									}

									global $photonic_smug_disable_title_link, $photonic_smug_hide_album_thumbnail, $photonic_smug_hide_album_title, $photonic_smug_hide_album_photo_count;
									$hidden = array(
										'thumbnail' => !empty($photonic_smug_hide_album_thumbnail),
										'title' => !empty($photonic_smug_hide_album_title),
										'counter' => !empty($photonic_smug_hide_album_photo_count),
									);
									$counters = array('photos' => $album->ImageCount);

									if (empty($display)) {
										$display = 'in-page';
									}

									if (!$header_done) {
										$insert = $this->process_object_header($header_object,
											array(
												'type' => 'album',
												'hidden' => $this->get_hidden_headers($short_code['header_display'], $hidden),
												'counters' => $counters,
												'link' => empty($photonic_smug_disable_title_link),
												'display' => $display,
											)
										);
										$header_done = true;
									}

								}
								if ($password_check_passed) {
									$ret .= $this->process_images($response, $short_code, $insert);
								}
								else {
									$ret .= __('This album is password-protected. Please provide a valid password.', 'photonic');
								}
							}
							else if (stripos($chained_call, '/user/') !== FALSE) {
								$root_node = $body->User->Uris->Node->Node;
								$ret .= $this->process_node($root_node, $short_code);
							}
							else if (stripos($chained_call, '/node/') !== FALSE) {
								$ret .= $this->process_node($body->Node, $short_code);
							}
						}
					}
					else {
						return '';
					}
				}
			}
			return $ret;
		}
		return '';
	}

	function process_node($node, $short_code, $indent = '') {
		$start = $indent."<ul class='photonic-tree'>\n";
		$ret = $start;

		if ($node->Type == 'Folder') {
			if (isset($node->Uris->ChildNodes->Node)) {
				$child_nodes = $node->Uris->ChildNodes->Node;
				$albums = array();
				$folders = array();
				foreach ($child_nodes as $child) {
					if ($child->Type == 'Album'){
						if (isset($child->Uris->Album->Album)) {
							$albums[] = $child->Uris->Album->Album;
						}
					}
					else if ($child->Type == 'Folder') {
						$folders[] = $child;
					}
				}

				if (count($albums) > 0) {
					$album_text = $this->process_albums($albums, $indent . "\t\t", '', $short_code);
					if (!empty($album_text)) {
						$ret .= $indent."\t<li>\n";
						$header_object = array(
							'title' => $node->Name,
						);
						$hidden = array('thumbnail' => true, 'title' => false, 'counter' => false, );
						$insert = $this->process_object_header($header_object,
							array(
								'type' => 'folder',
								'hidden' => $this->get_hidden_headers($short_code['header_display'], $hidden),
								'counters' => array(),
								'link' => false,
								'display' => 'in-page',
							)
						);
						$ret .= $insert;
						$ret .= $album_text;
						$ret .= $indent."\t</li>\n";
					}
				}

				if (count($folders) > 0) {
					$folder_tree = '';
					foreach ($folders as $folder) {
						$folder_tree .= $this->process_node($folder, $short_code, $indent."\t\t");
					}
					if (!empty($folder_tree)) {
						$ret .= $indent."\t<li>\n";
						$ret .= $folder_tree;
						$ret .= $indent."\t</li>\n";
					}
				}
			}
		}

		if ($ret != $start) {
			$ret .= $indent."</ul>\n";
		}
		else {
			// Nothing was found ...
			$ret = "";
		}
		return $ret;
	}

	/**
	 * Parse an array of album objects returned by the SmugMug API, then return an appropriate response.
	 *
	 * @param $albums
	 * @param string $indent
	 * @param string $album_filter
	 * @param $short_code
	 * @return string
	 */
	function process_albums($albums, $indent = '', $album_filter = '', $short_code) {
		global $photonic_smug_albums_album_per_row_constraint, $photonic_smug_albums_album_constrain_by_count, $photonic_smug_albums_album_constrain_by_padding, $photonic_smug_albums_album_title_display, $photonic_smug_hide_albums_album_photos_count_display;
		$objects = $this->build_level_2_objects($albums, $album_filter, $short_code);
		$row_constraints = array('constraint-type' => $photonic_smug_albums_album_per_row_constraint, 'padding' => $photonic_smug_albums_album_constrain_by_padding, 'count' => $photonic_smug_albums_album_constrain_by_count);
		$ret = $this->display_level_2_gallery($objects,
			array(
				'row_constraints' => $row_constraints,
				'type' => 'albums',
				'singular_type' => 'album',
				'title_position' => $photonic_smug_albums_album_title_display,
				'level_1_count_display' => $photonic_smug_hide_albums_album_photos_count_display,
				'indent' => $indent,
			),
			$short_code
		);
		return $ret;
	}

	/**
	 * Takes a response, then parses out the images from that response and returns a set of thumbnails for it. This method handles
	 * both, in-page images as well as images in a popup panel.
	 *
	 * @param $response
	 * @param array $attr
	 * @param null $header
	 * @return string
	 */
	function process_images($response, $attr, $header) {
		global $photonic_smug_photos_per_row_constraint, $photonic_smug_photos_constrain_by_count, $photonic_smug_photos_constrain_by_padding,
			   $photonic_smug_photos_pop_per_row_constraint, $photonic_smug_photos_pop_constrain_by_count,
			   $photonic_smug_photos_pop_constrain_by_padding, $photonic_smug_photo_title_display, $photonic_smug_photo_pop_title_display;
		$body = $response['body'];
		$body = json_decode($body);

		if ($body->Message == 'Ok') {
			$body = $body->Response;
			if (isset($body->Album)) {
				$album = $body->Album;
				$images = $album->Uris->AlbumImages;
			}
			else {
				$images = $body;
			}

			$photo_objects = array();
			if (isset($images->AlbumImage)) {
				$photo_objects = $this->build_level_1_objects($images->AlbumImage, $attr);
			}
			$level_2_meta = array();
			if (isset($images->Pages)) {
				$level_2_meta['total'] = $images->Pages->Total;
				$level_2_meta['start'] = $images->Pages->Start;
				$level_2_meta['end'] = $images->Pages->Start + $images->Pages->Count - 1;
				$level_2_meta['per-page'] = $images->Pages->RequestedCount;
				$level_2_meta['provider'] = 'smug';
			}

			$ret = "";
			if (!empty($photo_objects)) {
				if (isset($attr['display']) && $attr['display'] == 'popup') {
					$ret .= $header;
					$row_constraints = array('constraint-type' => $photonic_smug_photos_pop_per_row_constraint, 'padding' => $photonic_smug_photos_pop_constrain_by_padding, 'count' => $photonic_smug_photos_pop_constrain_by_count);
					$ret .= $this->display_level_1_gallery($photo_objects,
						array(
							'title_position' => $photonic_smug_photo_pop_title_display,
							'row_constraints' => $row_constraints,
							'parent' => 'album',
						),
						$attr
					);
				}
				else {
					$ret .= $header;
					$row_constraints = array('constraint-type' => $photonic_smug_photos_per_row_constraint, 'padding' => $photonic_smug_photos_constrain_by_padding, 'count' => $photonic_smug_photos_constrain_by_count);
					$ret .= $this->display_level_1_gallery($photo_objects,
						array(
							'title_position' => $photonic_smug_photo_title_display,
							'row_constraints' => $row_constraints,
							'parent' => 'album',
							'level_2_meta' =>  $level_2_meta,
						),
						$attr
					);
				}
				return $this->finalize_markup($ret, $attr);
			}
		}
		return "";
	}

	function build_level_1_objects($images, $short_code) {
		$thumb = "{$short_code['thumb_size']}ImageUrl";
		$main = "{$short_code['main_size']}ImageUrl";
		$tile = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $main : "{$short_code['tile_size']}ImageUrl";

		$photo_objects = array();
		if (is_array($images) && count($images) > 0) {
			foreach ($images as $image) {
				$image_sizes = $image->Uris->ImageSizes->ImageSizes;
				$photo_object = array();
				$photo_object['thumbnail'] = $image_sizes->{$thumb};
				$photo_object['main_image'] = $image_sizes->{$main};
				$photo_object['tile_image'] = $image_sizes->{$tile};
				$photo_object['title'] = $image->Title;
				$photo_object['alt_title'] = $image->Title;
				$photo_object['description'] = $image->Caption;
				$photo_object['main_page'] = $image->WebUri;
				$photo_object['buy_link'] = $image->WebUri.'/buy';
				if (isset($image->ArchivedUri)) {
					$photo_object['download'] = $image->ArchivedUri;
				}
				$photo_object['id'] = $image->ImageKey;

				$photo_objects[] = $photo_object;
			}
		}
		return $photo_objects;
	}

	function build_level_2_objects($albums, $album_filter = '', $short_code) {
		global $photonic_smug_hide_password_protected_thumbnail;

		$named_albums = array();
		if ($album_filter != '') {
			$named_albums = explode(',', $album_filter);
		}

		foreach ($named_albums as $idx => $album) {
			if (stripos($album, '_') !== FALSE) {
				$named_albums[$idx] = substr($album, stripos($album, '_') + 1);
			}
		}

		$objects = array();
		if (is_array($albums) && count($albums) > 0) {
			foreach ($albums as $album) {
				if (!empty($photonic_smug_hide_password_protected_thumbnail) && isset($album->SecurityType) && $album->SecurityType != 'None') {
					continue;
				}

				$main = "{$short_code['main_size']}ImageUrl";
				$tile = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $main : "{$short_code['tile_size']}ImageUrl";

				$highlight = $album->Uris->HighlightImage;
				if ((!isset($album->ImageCount) || $album->ImageCount != 0) && isset($highlight->Image)) {
					$thumbURL = $highlight->Image->Uris->ImageSizes->ImageSizes->{$short_code['thumb_size'].'ImageUrl'};
					$tileURL = $highlight->Image->Uris->ImageSizes->ImageSizes->$tile;

					$object = array();
					$object['id_1'] = $album->AlbumKey;
					$object['thumbnail'] = $thumbURL;
					$object['tile_image'] = $tileURL;
					$object['main_page'] = $album->WebUri;
					$object['title'] = esc_attr($album->Name);
					if (isset($album->ImageCount)) {
						$object['counter'] = $album->ImageCount;
					}

					if (isset($album->SecurityType) && $album->SecurityType == 'Password') {
						$object['classes'] = array('photonic-smug-passworded');
						$object['passworded'] = 1;
					}

					if ((count($named_albums) > 0 && in_array($album->AlbumKey, $named_albums)) || count($named_albums) === 0) {
						$objects[] = $object;
					}
				}
			}
		}
		return $objects;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
//		return 'smugmug.auth.getAccessToken';
		return 'http://api.smugmug.com/services/oauth/1.0a/getAccessToken';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
//		return 'http://api.smugmug.com/services/oauth/authorize.mg';
		return 'http://api.smugmug.com/services/oauth/1.0a/authorize';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
//		return 'http://api.smugmug.com/services/oauth/authorize.mg';
		return 'http://api.smugmug.com/services/oauth/1.0a/authorize';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_token_URL() {
//		return 'smugmug.auth.getRequestToken';
		return 'http://api.smugmug.com/services/oauth/1.0a/getRequestToken';
	}

	public function end_point() {
//		return 'https://secure.smugmug.com/services/api/json/1.3.0/';
		return 'https://api.smugmug.com/api/v2/';
	}

	function parse_token($response) {
		$body = $response['body'];
		$token = Photonic_Processor::parse_parameters($body);
		return $token;
	}

	public function check_access_token_method() {
		//return 'smugmug.auth.checkAccessToken';
	}

	/**
	 * Tests to see if the OAuth Access Token that is cached is still valid. This is important because a user might have manually revoked
	 * access for your app through the provider's control panel.
	 *
	 * @param $token
	 * @return array|WP_Error
	 */
	function check_access_token($token) {
		$signed_parameters = $this->sign_call($this->base_url.'user/sayontan', 'GET', array());
		$end_point = $this->base_url.'user/sayontan?'.Photonic_Processor::build_query($signed_parameters);
		$response = Photonic::http($end_point, 'GET', null);

		return $response;
	}

	/**
	 * Takes the response for the "Check access token", then tries to determine whether the check was successful or not.
	 *
	 * @param $response
	 * @return bool
	 */
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
