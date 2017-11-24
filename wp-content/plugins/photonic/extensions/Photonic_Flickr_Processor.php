<?php
/**
 * Processor for Flickr Galleries
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Flickr_Processor extends Photonic_OAuth1_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_flickr_api_key, $photonic_flickr_api_secret, $photonic_flickr_disable_title_link;
		$this->api_key = trim($photonic_flickr_api_key);
		$this->api_secret = trim($photonic_flickr_api_secret);
		$this->provider = 'flickr';
		$this->link_lightbox_title = empty($photonic_flickr_disable_title_link);
		$this->base_url = 'https://api.flickr.com/services/rest/';
	}

	/**
	 * A very flexible function to display a user's photos from Flickr. This makes use of the Flickr API, hence it requires the user's API key.
	 * The API key is defined in the options. The function makes use of three different APIs:
	 *  1. <a href='http://www.flickr.com/services/api/flickr.photos.search.html'>flickr.photos.search</a> - for retrieving photos based on search critiera
	 *  2. <a href='http://www.flickr.com/services/api/flickr.photosets.getPhotos.html'>flickr.photosets.getPhotos</a> - for retrieving photo sets
	 *  3. <a href='http://www.flickr.com/services/api/flickr.galleries.getPhotos.html'>flickr.galleries.getPhotos</a> - for retrieving galleries
	 *
	 * The following short-code parameters are supported:
	 * All
	 * - per_page: number of photos to display
	 * - view: photos | collections | galleries | photosets, displays hierarchically if user_id is passed
	 * Photosets
	 * - photoset_id
	 * Galleries
	 * - gallery_id
	 * Photos
	 * - user_id: can be obtained from the Helpers page
	 * - tags: comma-separated list of tags
	 * - tag_mode: any | all, tells whether any tag should be used or all
	 * - text: string for text search
	 * - sort: date-posted-desc | date-posted-asc | date-taken-asc | date-taken-desc | interestingness-desc | interestingness-asc | relevance
	 * - group_id: group id for which photos will be displayed
	 *
	 * @param array $attr
	 * @return string|void
	 * @since 1.02
	 */
	function get_gallery_images($attr = array()) {
		global $photonic_flickr_allow_oauth, $photonic_flickr_oauth_done, $photonic_flickr_title_caption, $photonic_flickr_thumb_size, $photonic_flickr_main_size, $photonic_flickr_tile_size;

		$attr = array_merge(
			$this->common_parameters,
			array(
				// Common overrides ...
				'caption' => $photonic_flickr_title_caption,
				'thumb_size' => $photonic_flickr_thumb_size,
				'main_size' => $photonic_flickr_main_size,
				'tile_size' => $photonic_flickr_tile_size,

				// Flickr-Specific ...
				//		'view' => 'photos'  // photos | collections | galleries | photosets: if only a user id is passed, what should be displayed?
				'privacy_filter' => '',
				'per_page' => 100,
				'page' => 1,
				'paginate' => false,
				'collections_display' => 'expanded',
				'user_id' => '',
				'collection_id' => '',
				'photoset_id' => '',
				'gallery_id' => '',
				'photo_id' => '',
			),
			$attr);
		$attr = array_map('trim', $attr);

		extract($attr);

		if (empty($this->api_key)) {
			return __("Flickr API Key not defined", 'photonic');
		}

		$query_urls = array();
		$flickr_params = array();

		$flickr_params['extras'] = 'description';

		$ret = "";
		$attr['iterate_level_3'] = $attr['collections_display'] === 'expanded';
		if (isset($view) && !empty($attr['user_id'])) {
			switch ($view) {
				case 'collections':
					if (empty($attr['collection_id'])) {
						$collections = $this->get_collection_list($attr['user_id'], '', $attr['filter']);
						foreach ($collections as $collection) {
							$query_urls[] = $this->base_url.'?method=flickr.collections.getTree&collection_id='.$collection['id'];
						}
					}
					break;

				case 'galleries':
					if (empty($attr['gallery_id'])) {
						$query_urls[] = $this->base_url.'?method=flickr.galleries.getList';
					}
					break;

				case 'photosets':
					if (empty($attr['photoset_id'])) {
						$query_urls[] = $this->base_url.'?method=flickr.photosets.getList';
					}
					break;

				case 'photo':
					if (!empty($attr['photo_id'])) {
						$query_urls[] = $this->base_url.'?method=flickr.photos.getInfo';
					}
					break;

				case 'photos':
				default:
					$query_urls[] = $this->base_url.'?method=flickr.photos.search';
					break;
			}
		}
		else if (isset($view) && $view == 'photos' && !empty($attr['group_id'])) {
			$query_urls[] = $this->base_url.'?method=flickr.photos.search';
		}
		else if (isset($view) && $view == 'photo' && !empty($attr['photo_id'])) {
			$query_urls[] = $this->base_url.'?method=flickr.photos.getInfo';
		}

		// Collection > galleries > photosets
		if (!empty($attr['collection_id'])) {
			$collections = $this->get_collection_list($attr['user_id'], $attr['collection_id']);
			$attr['iterate_level_3'] = true;
			foreach ($collections as $collection) {
				$query_urls[] = $this->base_url.'?method=flickr.collections.getTree&collection_id='.$collection['id'];
			}
		}
		else if (!empty($attr['gallery_id'])) {
			if (empty($gallery_id_computed)) {
				if (empty($attr['user_id'])) {
					return __('User id is required for displaying a single gallery', 'photonic');
				}

				$feed = $this->make_call($this->base_url.'?method=flickr.galleries.getList', $flickr_params);

				if (!is_wp_error($feed)) {
					if ($feed['response']['code'] == 200) {
						$feed = $feed['body'];
						$feed = json_decode($feed);
						if (isset($feed->galleries)) {
							$galleries = $feed->galleries;
							$galleries = $galleries->gallery;
							if (is_array($galleries) && count($galleries) > 0) {
								$gallery = $galleries[0];
								$global_dbid = $gallery->id;
								$global_dbid = substr($global_dbid, 0, stripos($global_dbid, '-'));
							}
						}
					}
				}

				if (isset($global_dbid)) {
					$attr['gallery_id'] = $global_dbid.'-'.$attr['gallery_id'];
				}
			}
			$query_urls[] = $this->base_url.'?method=flickr.galleries.getInfo';
			$query_urls[] = $this->base_url.'?method=flickr.galleries.getPhotos';
		}
		else if (!empty($attr['photoset_id'])) {
			$query_urls[] = $this->base_url.'?method=flickr.photosets.getInfo';
			$query_urls[] = $this->base_url.'?method=flickr.photosets.getPhotos';
		}

		if (!empty($attr['user_id'])) {
			$flickr_params['user_id'] = $attr['user_id'];
		}

		if (!empty($attr['collection_id'])) {
			$flickr_params['collection_id'] = $attr['collection_id'];
		}
		else if (!empty($attr['gallery_id'])) {
			$flickr_params['gallery_id'] = $attr['gallery_id'];
		}
		else if (!empty($attr['photoset_id'])) {
			$flickr_params['photoset_id'] = $attr['photoset_id'];
		}
		else if (!empty($attr['photo_id'])) {
			$flickr_params['photo_id'] = $attr['photo_id'];
		}

		if (!empty($attr['tags'])) {
			$flickr_params['tags'] = $attr['tags'];
		}

		if (!empty($attr['tag_mode'])) {
			$flickr_params['tag_mode'] = $attr['tag_mode'];
		}

		if (!empty($attr['text'])) {
			$flickr_params['text'] = $attr['text'];
		}

		if (!empty($attr['sort'])) {
			$flickr_params['sort'] = $attr['sort'];
		}

		if (!empty($attr['group_id'])) {
			$flickr_params['group_id'] = $attr['group_id'];
		}

		global $photonic_archive_thumbs;
		if (is_archive()) {
			if (isset($photonic_archive_thumbs) && !empty($photonic_archive_thumbs)) {
				if (!empty($attr['per_page']) && $photonic_archive_thumbs < $attr['per_page']) {
					$flickr_params['per_page'] = $photonic_archive_thumbs;
					$this->show_more_link = true;
				}
				else if (!empty($attr['per_page'])) {
					$flickr_params['per_page'] = $attr['per_page'];
				}
			}
			else if (!empty($attr['per_page'])) {
				$flickr_params['per_page'] = $attr['per_page'];
			}
		}
		else if (!empty($attr['per_page'])) {
			$flickr_params['per_page'] = $attr['per_page'];
		}

		if (!empty($attr['page'])) {
			$flickr_params['page'] = $attr['page'];
		}

		$login_required = false;
		if (!empty($attr['privacy_filter'])) {
			$flickr_params['privacy_filter'] = $attr['privacy_filter'];

			$login_required = $attr['privacy_filter'] == 1 ? false : true;
		}

		// Allow users to define additional query parameters
		$query_urls = apply_filters('photonic_flickr_query_urls', $query_urls, $attr);

		if ($photonic_flickr_allow_oauth && is_singular() && !$photonic_flickr_oauth_done && $login_required) {
			$post_id = get_the_ID();
			$ret .= $this->get_login_box($post_id);
		}

		$header_display = $this->get_header_display($attr);
		$attr['header_display'] = $header_display;

		$call_return = '';
		foreach ($query_urls as $query_url) {
			$method = 'flickr.photos.getInfo';
			$iterator = array();
			if (is_array($query_url)) {
				$iterator = $query_url;
			}
			else {
				$iterator[] = $query_url;
			}

			foreach ($iterator as $nested_query_url) {
				$this->gallery_index++;

				$method = wp_parse_args(substr($nested_query_url, stripos($nested_query_url, '?') + 1));
				$method = $method['method'];
				$response = $this->make_call($nested_query_url, $flickr_params);
				$flickr_params['method'] = $method;

				$call_return .= $this->process_query($response, $flickr_params, $attr);
			}

			if ($this->show_more_link && $method != 'flickr.photosets.getInfo' && $method != 'flickr.photos.getInfo' && $method != 'flickr.galleries.getInfo') {
				$call_return .= $this->more_link_button(get_permalink().'#photonic-flickr-stream-'.$this->gallery_index);
			}
		}

		$ret .= $this->finalize_markup($call_return, $attr);

		return $ret;
	}

	function make_call($query, $flickr_params) {
		global $photonic_flickr_oauth_done;
		$params = substr($query, strlen($this->base_url));
		if (strlen($params) > 1) {
			$params = substr($params, 1);
		}
		$params = Photonic_Processor::parse_parameters($params);
		$params['format'] = 'json';
		$params['nojsoncallback'] = 1;
		$params['api_key'] = $this->api_key;

		$params = array_merge($flickr_params, $params);

		// We only worry about signing the call if the authentication is done. Otherwise we just show what is available.
		if ($photonic_flickr_oauth_done) {
			$signed_args = $this->sign_call($this->base_url, 'GET', $params);
			$params = $signed_args;
		}

//		$response = Photonic::http($query, 'GET', $params);
		$response = Photonic::http($this->base_url, 'GET', $params);
		return $response;
	}

	/**
	 * Retrieves a list of collection objects for a given user. This first invokes the web-service, then iterates through the collections returned.
	 * For each collection returned it recursively looks for nested collections and sets.
	 *
	 * @param $user_id
	 * @param string $collection_id
	 * @param string $filters
	 * @return array
	 */
	function get_collection_list($user_id, $collection_id = '', $filters = '') {
		$query = $this->base_url.'?method=flickr.collections.getTree';
		$flickr_params = array();
		if (!empty($collection_id)) {
			$flickr_params['collection_id'] = $collection_id;
		}
		if (!empty($user_id)) {
			$flickr_params['user_id'] = $user_id;
		}

		$collection_list = array();
		if (!empty($filters)) {
			$collection_list = explode(',', $filters);
		}

		$feed = $this->make_call($query, $flickr_params);
		if (!is_wp_error($feed) && 200 == $feed['response']['code']) {
			$feed = $feed['body'];
			$feed = json_decode($feed);
			if ($feed->stat == 'ok') {
				$collections = $feed->collections;
				$collections = $collections->collection;
				$ret = array();
				$processed = array();
				foreach ($collections as $collection) {
					if (isset($collection->id)) {
						if (!in_array($collection->id, $processed)) {
							$iterative = $this->get_nested_collections($collection, $processed);
							$ret = array_merge($ret, $iterative);
						}
					}
				}

				$filtered_ret = array();
				if (!empty($collection_list)) {
					foreach ($ret as $collection) {
						if (in_array($collection['id'], $collection_list)) {
							$filtered_ret[] = $collection;
						}
					}
					return $filtered_ret;
				}

				return $ret;
			}
		}
		return array();
	}

	/**
	 * Goes through a Flickr collection and recursively fetches all sets and other collections within it. This is returned as
	 * a flattened array.
	 *
	 * @param $collection
	 * @param $processed
	 * @return array
	 */
	function get_nested_collections($collection, &$processed) {
		$id = isset($collection->id) ? (string)$collection->id : '';
		if (in_array($id, $processed)) {
			return array();
		}
		$processed[] = $id;
		$id = substr($id, strpos($id, '-') + 1);
		$title = isset($collection->title) ? (string)$collection->title : '';
		$description = isset($collection->description) ? (string)$collection->description : '';
		$thumb = isset($collection->iconsmall) ? (string)$collection->iconsmall : (isset($collection->iconlarge) ? (string)$collection->iconlarge : '');

		$ret = array();

		$inner_sets = $collection->set;
		$sets = array();
		if (count($inner_sets) > 0) {
			foreach ($inner_sets as $inner_set) {
				$sets[] = array(
					'id' => (string)$inner_set->id,
					'title' => (string)$inner_set->title,
					'description' => (string)$inner_set->description,
				);
			}
		}
		$ret[] = array(
			'id' => $id,
			'title' => $title,
			'description' => $description,
			'thumb' => $thumb,
			'sets' => $sets,
		);

		if (isset($collection->collection)) {
			$inner_collections = $collection->collection;
			if (count($inner_collections) > 0) {
				foreach ($inner_collections as $inner_collection) {
					$processed[] = $inner_collection->id;
				}
			}
		}
		return $ret;
	}

	function process_query($response, $flickr_params, $short_code = array()) {
		$ret = '';

		$filter_list = array();
		if (!empty($short_code['filter'])) {
			$filter_list = explode(',', $short_code['filter']);
		}

		if (!is_wp_error($response)) {
			if ($response['response']['code'] == 200) {
				$body = $response['body'];
				$body = json_decode($body);
				switch ($flickr_params['method']) {
					case 'flickr.photos.getInfo':
						if (isset($body->photo)) {
							$photo = $body->photo;
							$ret .= $this->process_photo($photo, $short_code);
						}
						break;

					case 'flickr.photos.search':
						if (isset($body->photos) && isset($body->photos->photo)) {
							$photos = $body->photos->photo;
							$ret .= $this->process_photos($photos, '', 'stream', $short_code,
								array(
									'total' => $body->photos->total,
									'start' => ($body->photos->page - 1) * $body->photos->perpage + 1,
									'end' => $body->photos->page * $body->photos->perpage > $body->photos->total ? $body->photos-> total : $body->photos->page * $body->photos->perpage,
									'per-page' => $body->photos->perpage,
								)
							);
						}
						break;

					case 'flickr.photosets.getInfo':
						if (isset($body->photoset)) {
							$photoset = $body->photoset;
							$ret .= $this->process_photoset_header($photoset, $short_code);
						}
						break;

					case 'flickr.photosets.getPhotos':
						if (isset($body->photoset)) {
							$photoset = $body->photoset;
							if (isset($photoset->photo) && isset($photoset->owner)) {
								$owner = $photoset->owner;
								$ret .= $this->process_photos($photoset->photo, $owner, 'set', $short_code,
									array(
										'total' => $photoset->total,
										'start' => ($photoset->page - 1) * $photoset->perpage + 1,
										'end' => $photoset->page * $photoset->perpage > $photoset->total ? $photoset-> total : $photoset->page * $photoset->perpage,
										'per-page' => $photoset->perpage,
									)
								);
							}
						}
						break;

					case 'flickr.photosets.getList':
						if (isset($body->photosets)) {
							$photosets = $body->photosets;
							$ret .= $this->process_photosets($photosets, $filter_list, $short_code);
						}
						break;

					case 'flickr.galleries.getInfo':
						if (isset($body->gallery)) {
							$gallery = $body->gallery;
							$ret .= $this->process_gallery_header($gallery, $short_code);
						}
						break;

					case 'flickr.galleries.getPhotos':
						if (isset($body->photos)) {
							$photos = $body->photos;
							if (isset($photos->photo)) {
								$ret .= $this->process_photos($photos->photo, '', 'gallery', $short_code,
									array(
										'total' => $body->photos->total,
										'start' => ($body->photos->page - 1) * $body->photos->perpage + 1,
										'end' => $body->photos->page * $body->photos->perpage > $body->photos->total ? $body->photos-> total : $body->photos->page * $body->photos->perpage,
										'per-page' => $body->photos->perpage,
									)
								);
							}
						}
						break;

					case 'flickr.galleries.getList':
						if (isset($body->galleries)) {
							$galleries = $body->galleries;
							$ret .= $this->process_galleries($galleries, $filter_list, $short_code);
						}
						break;

					case 'flickr.collections.getTree':
						if (isset($body->collections)) {
							$collections = $body->collections;
							$ret .= $this->process_collections($collections, $short_code);
						}
						break;
				}
			}
		}
		return $ret;
	}

	/**
	 * Prints a single photo with the title as an <h3> and the caption as the image caption.
	 *
	 * @param $photo
	 * @return string
	 */
	function process_photo($photo, $short_code) {
		return $this->generate_single_photo_markup('flickr', array(
				'src' => "https://farm".$photo->farm.".static.flickr.com/".$photo->server."/".$photo->id."_".$photo->secret.($short_code['main_size'] == 'none' ? '' : '_'.$short_code['main_size']).".jpg",
				'href' => (isset($photo->urls) && isset($photo->urls->url) && count($photo->urls->url) > 0) ? $photo->urls->url[0]->_content : '',
				'title' => isset($photo->title) ? $photo->title->_content : '',
				'caption' => isset($photo->description) ? $photo->description->_content : '',
			)
		);
	}

	/**
	 * Prints thumbnails for all photos returned in a query. This is used for printing the results of a search, tag, photoset or gallery.
	 * The photos are printed in-page.
	 *
	 * @param $photos
	 * @param string $owner
	 * @param string $parent
	 * @param $short_code
	 * @param array $level_2_meta
	 * @return string
	 */
	function process_photos($photos, $owner = '', $parent = 'stream', $short_code, $level_2_meta = array()) {
		global $photonic_flickr_photo_title_display, $photonic_flickr_photo_pop_title_display;
		global $photonic_flickr_photos_per_row_constraint, $photonic_flickr_photos_constrain_by_padding, $photonic_flickr_photos_constrain_by_count;
		global $photonic_flickr_photos_pop_per_row_constraint, $photonic_flickr_photos_pop_constrain_by_padding, $photonic_flickr_photos_pop_constrain_by_count;

		if ($short_code['display'] == 'in-page') {
			$title_position = $photonic_flickr_photo_title_display;
			$row_constraints = array('constraint-type' => $photonic_flickr_photos_per_row_constraint, 'padding' => $photonic_flickr_photos_constrain_by_padding, 'count' => $photonic_flickr_photos_constrain_by_count);
		}
		else {
			$title_position = $photonic_flickr_photo_pop_title_display;
			$row_constraints = array('constraint-type' => $photonic_flickr_photos_pop_per_row_constraint, 'padding' => $photonic_flickr_photos_pop_constrain_by_padding, 'count' => $photonic_flickr_photos_pop_constrain_by_count);
		}
		$photo_objects = $this->build_level_1_objects($photos, $owner, $short_code);
		$ret = $this->display_level_1_gallery($photo_objects,
			array(
				'title_position' => $title_position,
				'row_constraints' => $row_constraints,
				'parent' => $parent,
				'level_2_meta' => $level_2_meta,
			),
			$short_code
		);
		return $ret;
	}

	function build_level_1_objects($photos, $owner = '', $short_code) {
		$photo_objects = array();

		$main_size = $short_code['main_size'] == 'none' ? '' : '_'.$short_code['main_size'];
		$tile_size = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $main_size : ($short_code['tile_size'] == 'none' ? '' : '_'.$short_code['tile_size']);

		foreach ($photos as $photo) {
			$photo_object = array();
			$photo_object['thumbnail'] = 'https://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.'_'.$short_code['thumb_size'].'.jpg';
			$photo_object['main_image'] = 'https://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.$main_size.'.jpg';
			$photo_object['download'] = 'https://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.$main_size.'_d.jpg';
			$photo_object['tile_image'] = 'https://farm'.$photo->farm.'.static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.$tile_size.'.jpg';
			$photo_object['alt_title'] = esc_attr($photo->title);
			if (isset($photo->owner)) {
				$owner = $photo->owner;
			}
			$url = "https://www.flickr.com/photos/".$owner."/".$photo->id;
			$photo_object['main_page'] = $url;

			$title = $photo->title;
			$photo_object['title'] = $title;

			if (isset($photo->description)) {
				$photo_object['description'] = $photo->description->_content;
			}
			else {
				$photo_object['description'] = '';
			}

			$photo_object['id'] = $photo->id;
			$photo_objects[] = $photo_object;
		}

		return $photo_objects;
	}

	function build_level_2_objects($flickr_objects, $type, $filter_list = array(), $short_code = array()) {
		$main_size = $short_code['main_size'] == 'none' ? '' : '_'.$short_code['main_size'];
		$tile_size = (empty($short_code['tile_size']) || $short_code['tile_size'] == 'same') ? $main_size : ($short_code['tile_size'] == 'none' ? '' : '_'.$short_code['tile_size']);

		$objects = array();

		foreach ($flickr_objects as $flickr_object) {
			if (!empty($filter_list) && (($type == 'photoset' &&!in_array($flickr_object->id, $filter_list)) || ($type == 'gallery' &&!in_array(substr($flickr_object->id, stripos($flickr_object->id, '-') + 1), $filter_list)))) {
				continue;
			}

			$object = array();
			$object['id_1'] = $flickr_object->id;
//			$object['id_2'] = $flickr_object->id;
			$object['title'] = esc_attr($flickr_object->title->_content);
			if ($type == 'gallery') {
				$object['thumbnail'] = "https://farm".$flickr_object->primary_photo_farm.".static.flickr.com/".$flickr_object->primary_photo_server."/".$flickr_object->primary_photo_id."_".$flickr_object->primary_photo_secret."_".$short_code['thumb_size'].".jpg";
				$object['tile_image'] = "https://farm".$flickr_object->primary_photo_farm.".static.flickr.com/".$flickr_object->primary_photo_server."/".$flickr_object->primary_photo_id."_".$flickr_object->primary_photo_secret.$tile_size.".jpg";
				$object['main_page'] = $flickr_object->url;
				$object['counter'] = $flickr_object->count_photos;
				$object['classes'] = array("photonic-flickr-gallery-thumb-user-{$short_code['user_id']}");
			}
			else if ($type == 'photoset') {
				$object['thumbnail'] = "https://farm".$flickr_object->farm.".static.flickr.com/".$flickr_object->server."/".$flickr_object->primary."_".$flickr_object->secret."_".$short_code['thumb_size'].".jpg";
				$object['tile_image'] = "https://farm".$flickr_object->farm.".static.flickr.com/".$flickr_object->server."/".$flickr_object->primary."_".$flickr_object->secret.$tile_size.".jpg";
				$owner = isset($flickr_object->owner) ? $flickr_object->owner : $short_code['user_id'];
				$object['main_page'] = "https://www.flickr.com/photos/$owner/sets/{$flickr_object->id}";
				$object['counter'] = $flickr_object->photos;
			}
			$objects[] = $object;
		}
		return $objects;
	}

	/**
	 * Prints the header for an in-page photoset.
	 *
	 * @param $photoset
	 * @param array $short_code
	 * @return string
	 */
	function process_photoset_header($photoset, $short_code = array()) {
		global $photonic_flickr_hide_set_thumbnail, $photonic_flickr_hide_set_title, $photonic_flickr_hide_set_photo_count;
		$owner = $photoset->owner;
		$header = array(
			'title' => $photoset->title->_content,
			'thumb_url' => "https://farm".$photoset->farm.".static.flickr.com/".$photoset->server."/".$photoset->primary."_".$photoset->secret."_".$short_code['thumb_size'].".jpg",
			'link_url' => 'https://www.flickr.com/photos/'.$owner.'/sets/'.$photoset->id,
		);

		$hidden = array('thumbnail' => !empty($photonic_flickr_hide_set_thumbnail), 'title' => !empty($photonic_flickr_hide_set_title), 'counter' => !empty($photonic_flickr_hide_set_photo_count));
		$counters = array('photos' => $photoset->photos);

		$ret = $this->process_object_header($header,
			array(
				'type' => 'set',
				'hidden' => $this->get_hidden_headers($short_code['header_display'], $hidden),
				'counters' => $counters,
				'link' => true,
				'display' => $short_code['display'],
			)
		);

		return $ret;
	}

	/**
	 * Prints thumbnails for each photoset returned in a query.
	 *
	 * @param $photosets
	 * @param array $filter_list
	 * @param array $short_code
	 * @return string
	 */
	function process_photosets($photosets, $filter_list = array(), $short_code = array()) {
		global $photonic_flickr_collection_set_per_row_constraint, $photonic_flickr_collection_set_constrain_by_count, $photonic_flickr_collection_set_constrain_by_padding,
			$photonic_flickr_collection_set_title_display, $photonic_flickr_hide_collection_set_photos_count_display;
		$objects = $this->build_level_2_objects($photosets->photoset, 'photoset', $filter_list, $short_code);
		$row_constraints = array('constraint-type' => $photonic_flickr_collection_set_per_row_constraint, 'padding' => $photonic_flickr_collection_set_constrain_by_padding, 'count' => $photonic_flickr_collection_set_constrain_by_count);
		$ret = $this->display_level_2_gallery($objects,
			array(
				'row_constraints' => $row_constraints,
				'type' => 'photosets',
				'singular_type' => 'set',
				'title_position' => $photonic_flickr_collection_set_title_display,
				'level_1_count_display' => $photonic_flickr_hide_collection_set_photos_count_display,
			),
			$short_code
		);
		return $ret;
	}

	/**
	 * Shows the header for a gallery invoked in-page.
	 *
	 * @param $gallery
	 * @param $short_code
	 * @return string
	 */
	function process_gallery_header($gallery, $short_code) {
		global $photonic_flickr_hide_gallery_thumbnail, $photonic_flickr_hide_gallery_title, $photonic_flickr_hide_gallery_photo_count;
		$header = array(
			'title' => $gallery->title->_content,
			'thumb_url' => "https://farm".$gallery->primary_photo_farm.".static.flickr.com/".$gallery->primary_photo_server."/".$gallery->primary_photo_id."_".$gallery->primary_photo_secret."_".$short_code['thumb_size'].".jpg",
			'link_url' => $gallery->url,
		);

		$hidden = array('thumbnail' => !empty($photonic_flickr_hide_gallery_thumbnail), 'title' => !empty($photonic_flickr_hide_gallery_title), 'counter' => !empty($photonic_flickr_hide_gallery_photo_count));
		$counters = array('photos' => $gallery->count_photos);

		$ret = $this->process_object_header($header,
			array(
				'type' => 'gallery',
				'hidden' => $this->get_hidden_headers($short_code['header_display'], $hidden),
				'counters' => $counters,
				'link' => true,
				'display' => $short_code['display'],
			)
		);
		return $ret;
	}

	/**
	 * Prints out the thumbnails for all galleries belonging to a user.
	 *
	 * @param $galleries
	 * @param array $filter_list
	 * @param array $short_code
	 * @return string
	 */
	function process_galleries($galleries, $filter_list = array(), $short_code = array()) {
		global $photonic_flickr_galleries_per_row_constraint, $photonic_flickr_galleries_constrain_by_padding,
			$photonic_flickr_galleries_constrain_by_count, $photonic_flickr_gallery_title_display, $photonic_flickr_hide_gallery_photos_count_display;

		$objects = $this->build_level_2_objects($galleries->gallery, 'gallery', $filter_list, $short_code);
		$row_constraints = array('constraint-type' => $photonic_flickr_galleries_per_row_constraint, 'padding' => $photonic_flickr_galleries_constrain_by_padding, 'count' => $photonic_flickr_galleries_constrain_by_count);
		$ret = $this->display_level_2_gallery($objects,
			array(
				'row_constraints' => $row_constraints,
				'type' => 'galleries',
				'singular_type' => 'gallery',
				'title_position' => $photonic_flickr_gallery_title_display,
				'level_1_count_display' => $photonic_flickr_hide_gallery_photos_count_display,
			),
			$short_code
		);
		return $ret;
	}

	/**
	 * Prints a collection header, followed by thumbnails of all sets in that collection.
	 *
	 * @param $collections
	 * @param array $short_code
	 * @return string
	 */
	function process_collections($collections, $short_code = array()) {
		global $photonic_flickr_hide_empty_collection_details, $photonic_flickr_collection_set_per_row_constraint, $photonic_flickr_collection_set_constrain_by_padding,
			   $photonic_flickr_collection_set_constrain_by_count, $photonic_flickr_hide_collection_thumbnail, $photonic_flickr_hide_collection_title, $photonic_flickr_hide_collection_set_count, $photonic_flickr_collection_set_title_display, $photonic_flickr_hide_collection_set_photos_count_display;
		$ret = '';

		$row_constraints = array('constraint-type' => $photonic_flickr_collection_set_per_row_constraint, 'padding' => $photonic_flickr_collection_set_constrain_by_padding, 'count' => $photonic_flickr_collection_set_constrain_by_count);
		foreach ($collections->collection as $collection) {
			$dont_show = false;
			if (empty($collection->set) && !empty($photonic_flickr_hide_empty_collection_details)) {
				$dont_show = true;
			}
			$id = $collection->id;
			if (!$dont_show) {
				$url_id = substr($id, stripos($id, '-') + 1);
				$header = array('id' => $id.'-'.$short_code['user_id'], 'title' => $collection->title, 'thumb_url' => $collection->iconsmall, 'link_url' => "https://www.flickr.com/photos/".$short_code['user_id']."/collections/".$url_id);
				$hidden = array('thumbnail' => !empty($photonic_flickr_hide_collection_thumbnail), 'title' => !empty($photonic_flickr_hide_collection_title), 'counter' => !empty($photonic_flickr_hide_collection_set_count));
				$counters = array();
				if (isset($collection->set)) {
					$photosets = $collection->set;
					$counters['sets'] = count($photosets);
				}

				$ret .= $this->process_object_header($header,
					array(
						'type' => 'collection',
						'hidden' => $this->get_hidden_headers($short_code['header_display'], $hidden),
						'counters' => $counters,
						'link' => true,
						'iterate_level_3' => $short_code['iterate_level_3'],
						'layout' => $short_code['layout'],
					)
				);
			}

			if (isset($collection->set) && !empty($collection->set) && $short_code['iterate_level_3']) {
				$flickr_objects = array();
				$photosets = $collection->set;
				foreach ($photosets as $set) {
					$set_url = $this->base_url.'?method=flickr.photosets.getInfo';
					$set_response = $this->make_call($set_url, array('photoset_id' => $set->id));

					if (!is_wp_error($set_response) && isset($set_response['response']) && isset($set_response['response']['code']) && $set_response['response']['code'] == 200) {
						$set_response = json_decode($set_response['body']);
						if ($set_response->stat != 'fail' && isset($set_response->photoset)) {
							$photoset = $set_response->photoset;
							$flickr_objects[] = $photoset;
						}
					}
				}
				$objects = $this->build_level_2_objects($flickr_objects, 'photoset', array(), $short_code); // No filters passed for this
				$ret .= $this->display_level_2_gallery($objects,
					array(
						'row_constraints' => $row_constraints,
						'type' => 'photosets',
						'singular_type' => 'set',
						'title_position' => $photonic_flickr_collection_set_title_display,
						'level_1_count_display' => $photonic_flickr_hide_collection_set_photos_count_display,
					),
					$short_code
				);
			}
		}
		return $ret;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		return 'https://www.flickr.com/services/oauth/access_token';
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		return 'https://www.flickr.com/services/oauth/authorize';
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		return 'https://www.flickr.com/services/oauth/authorize';
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_token_URL() {
		return 'https://www.flickr.com/services/oauth/request_token';
	}

	public function end_point() {
		return $this->base_url;
	}

	function parse_token($response) {
		$body = $response['body'];
		$token = Photonic_Processor::parse_parameters($body);
		return $token;
	}

	public function check_access_token_method() {
		return 'flickr.test.login';
	}

	/**
	 * Method to validate that the stored token is indeed authenticated.
	 *
	 * @param $token
	 * @return array|WP_Error
	 */
	function check_access_token($token) {
		$parameters = array('method' => $this->check_access_token_method(), 'format' => 'json', 'nojsoncallback' => 1);
		$signed_parameters = $this->sign_call($this->end_point(), 'GET', $parameters);

		$end_point = $this->end_point();
		$end_point .= '?'.Photonic_Processor::build_query($signed_parameters);
		$parameters = null;

		$response = Photonic::http($end_point, 'GET', $parameters);
		return $response;
	}
}
