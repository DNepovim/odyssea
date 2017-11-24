<?php
/**
 * Gallery processor class to be extended by individual processors. This class has an abstract method called <code>get_gallery_images</code>
 * that has to be defined by each inheriting processor.
 *
 * This is also where the OAuth support is implemented. The URLs are defined using abstract functions, while a handful of utility functions are defined.
 * Most utility functions have been adapted from the OAuth PHP package distributed here: http://code.google.com/p/oauth-php/.
 *
 * @package Photonic
 * @subpackage Extensions
 */

abstract class Photonic_Processor {
	public $library, $thumb_size, $full_size, $api_key, $api_secret, $provider, $nonce, $oauth_timestamp, $signature_parameters, $link_lightbox_title, $layout,
		$oauth_version, $oauth_done, $show_more_link, $is_server_down, $is_more_required, $login_shown, $login_box_counter, $gallery_index, $bypass_popup, $common_parameters;

	function __construct() {
		global $photonic_slideshow_library, $photonic_custom_lightbox, $photonic_enable_popup, $photonic_thumbnail_style;
		if ($photonic_slideshow_library != 'custom') {
			$this->library = $photonic_slideshow_library;
		}
		else {
			$this->library = $photonic_custom_lightbox;
		}
		$this->nonce = Photonic_Processor::nonce();
		$this->oauth_timestamp = time();
		$this->oauth_version = '1.0';
		$this->show_more_link = false;
		$this->is_server_down = false;
		$this->is_more_required = true;
		$this->login_shown = false;
		$this->login_box_counter = 0;
		$this->gallery_index = 0;
		$this->bypass_popup = !isset($photonic_enable_popup) || $photonic_enable_popup === false || $photonic_enable_popup == '' || $photonic_enable_popup == 'off';
		$this->common_parameters = array(
			'columns'    => 'auto',
			'layout' => !empty($photonic_thumbnail_style) ? $photonic_thumbnail_style : 'square',
			'more' => '',
			'display' => 'in-page',
			'panel' => '',
			'filter' => '',
			'fx' => 'slide', 	// LightSlider effects: fade and slide
			'timeout' => 4000, 	// Time between slides in ms
			'speed' => 1000,	// Time for each transition
			'pause' => true,	// Pause on hover
			'strip-style' => 'thumbs',
			'controls' => 'show',
			'popup' => $this->bypass_popup ? 'hide' : 'show',
		);
	}

	/**
	 * Main function that fetches the images associated with the shortcode.
	 *
	 * @abstract
	 * @param array $attr
	 */
	abstract protected function get_gallery_images($attr = array());

	public function oauth_signature_method() {
		return 'HMAC-SHA1';
	}

	/**
	 * Takes a token response from a request token call, then puts it in an appropriate array.
	 *
	 * @param $response
	 */
	public abstract function parse_token($response);

	/**
	 * Generates a nonce for use in signing calls.
	 *
	 * @static
	 * @return string
	 */
	public static function nonce() {
		$mt = microtime();
		$rand = mt_rand();
		return md5($mt . $rand);
	}

	/**
	 * Encodes the URL as per RFC3986 specs. This replaces some strings in addition to the ones done by a rawurlencode.
	 * This has been adapted from the OAuth for PHP project.
	 *
	 * @static
	 * @param $input
	 * @return array|mixed|string
	 */
	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array('Photonic_Processor', 'urlencode_rfc3986'), $input);
		}
		else if (is_scalar($input)) {
			return str_replace(
				'+',
				' ',
				str_replace('%7E', '~', rawurlencode($input))
			);
		}
		else {
			return '';
		}
	}

	/**
	 * Takes an array of parameters, then parses it and generates a query string. Prior to generating the query string the parameters are sorted in their natural order.
	 * Without sorting the signatures between this application and the provider might differ.
	 *
	 * @static
	 * @param $params
	 * @return string
	 */
	public static function build_query($params) {
		if (!$params) {
			return '';
		}
		$keys = array_map(array('Photonic_Processor', 'urlencode_rfc3986'), array_keys($params));
		$values = array_map(array('Photonic_Processor', 'urlencode_rfc3986'), array_values($params));
		$params = array_combine($keys, $values);

		// Sort by keys (natsort)
		uksort($params, 'strnatcmp');
		$pairs = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				natsort($value);
				foreach ($value as $v2) {
					$pairs[] = ($v2 == '') ? "$key=0" : "$key=$v2";
				}
			}
			else {
				$pairs[] = ($value == '') ? "$key=0" : "$key=$value";
			}
		}

		$string = implode('&', $pairs);
		return $string;
	}

	/**
	 * Takes a string of parameters in an HTML encoded string, then returns an array of name-value pairs, with the parameter
	 * name and the associated value.
	 *
	 * @static
	 * @param $input
	 * @return array
	 */
	public static function parse_parameters($input) {
		if (!isset($input) || !$input) return array();

		$pairs = explode('&', $input);

		$parsed_parameters = array();
		foreach ($pairs as $pair) {
			$split = explode('=', $pair, 2);
			$parameter = urldecode($split[0]);
			$value = isset($split[1]) ? urldecode($split[1]) : '';

			if (isset($parsed_parameters[$parameter])) {
				// We have already recieved parameter(s) with this name, so add to the list
				// of parameters with this name
				if (is_scalar($parsed_parameters[$parameter])) {
					// This is the first duplicate, so transform scalar (string) into an array
					// so we can add the duplicates
					$parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
				}

				$parsed_parameters[$parameter][] = $value;
			}
			else {
				$parsed_parameters[$parameter] = $value;
			}
		}
		return $parsed_parameters;
	}

	/**
	 * If authentication is enabled for this processor and the user has not authenticated this site to access his profile,
	 * this shows a login box.
	 *
	 * @param $post_id
	 * @return string
	 */
	public function get_login_box($post_id = '') {
		$login_box_option = 'photonic_'.$this->provider.'_login_box';
		$login_button_option = 'photonic_'.$this->provider.'_login_button';
		global $$login_box_option, $$login_button_option;
		$login_box = $$login_box_option;
		$login_button = $$login_button_option;
		$this->login_box_counter++;
		$ret = '<div id="photonic-login-box-'.$this->provider.'-'.$this->login_box_counter.'" class="photonic-login-box photonic-login-box-'.$this->provider.'">'."\n";
		if ($this->is_server_down) {
			$ret .= __("The authentication server is down. Please try after some time.", 'photonic');
		}
		else {
			$ret .=  "\t".wp_specialchars_decode($login_box, ENT_QUOTES)."\n";
			if (trim($login_button) == '') {
				$login_button = 'Login';
			}
			else {
				$login_button = wp_specialchars_decode($login_button, ENT_QUOTES);
			}
			$url = '#';
			$target = '';
			if ($this->provider == 'picasa' || $this->provider == 'instagram') {
				$url = $this->get_authorization_url();
				$target = 'target="_blank"';
			}

			if (!empty($post_id)) {
				$rel = "rel='auth-button-single-$post_id'";
			}
			else {
				$rel = '';
			}
			$ret .= "\t<p class='photonic-auth-button'>\n\t\t<a href='$url' $target class='auth-button auth-button-{$this->provider}' $rel>".$login_button."</a>\n\t</p>\n";
		}
		$ret .= "</div><!-- photonic-login-box -->\n";
		return $ret;
	}

	function more_link_button($link_to = '') {
		global $photonic_archive_link_more;
		if (empty($photonic_archive_link_more) && $this->is_more_required) {
			return "<div class='photonic-more-link-container'><a href='$link_to' class='photonic-more-button more-button-{$this->provider}'>See the rest</a></div>";
		}
		$this->is_more_required = true;
		return '';
	}

	/**
	 * Prints the header for a section. Typically used for albums / photosets / groups, where some generic information about the album / photoset / group is available.
	 * The <code>$options</code> array accepts the following prarameters:
	 * 	- string type Indicates what type of object is being displayed like gallery / photoset / album etc. This is added to the CSS class.
	 * 	- array $hidden Contains the elements that should be hidden from the header display.
	 * 	- array $counters Contains counts of the object that the header represents. In most cases this has just one value. Zenfolio objects have multiple values.
	 * 	- string $link Should clicking on the thumbnail / title take you anywhere?
	 * 	- string $display Indicates if this is on the page or in a popup
	 * 	- bool $iterate_level_3 If this is a level 3 header, this field indicates whether an expansion icon should be shown. This is to improve performance for Flickr collections.
	 * 	- string $provider What is the source of the data?
	 *
	 * @param array $header The header object, which contains the title, thumbnail source URL and the link where clicking on the thumb will take you
	 * @param array $options The options to display this header. Options contain the listed internal fields fields
	 * @return string
	 */
	function process_object_header($header, $options = array()) {
		$type = empty($options['type']) ? 'group' : $options['type'];
		$hidden = isset($options['hidden']) && is_array($options['hidden']) ? $options['hidden'] : array();
		$counters = isset($options['counters']) && is_array($options['counters']) ? $options['counters'] : array();
		$link = !isset($options['link']) ? true : $options['link'];
		$display = empty($options['display']) ? 'in-page' : $options['display'];
		$iterate_level_3 = !isset($options['iterate_level_3']) ? true : $options['iterate_level_3'];

		if ($this->bypass_popup && $display != 'in-page') {
			return '';
		}
		$ret = '';
		if (!empty($header['title'])) {
			global $photonic_external_links_in_new_tab;
			$title = esc_attr($header['title']);
			if (!empty($photonic_external_links_in_new_tab)) {
				$target = ' target="_blank" ';
			}
			else {
				$target = '';
			}

			$anchor = '';
			if (!empty($header['thumb_url'])) {
				$image = '<img src="'.$header['thumb_url'].'" alt="'.$title.'" />';

				if ($link) {
					$anchor = "<a href='".$header['link_url']."' class='photonic-header-thumb photonic-{$this->provider}-$type-solo-thumb' title='".$title."' $target>".$image."</a>";
				}
				else {
					$anchor = "<div class='photonic-header-thumb photonic-{$this->provider}-$type-solo-thumb'>$image</div>";
				}
			}

			if (empty($hidden['thumbnail']) || empty($hidden['title']) || empty($hidden['counter']) || empty($iterate_level_3)) {
				$popup_header_class = '';
				if ($display == 'popup') {
					$popup_header_class = 'photonic-panel-header';
				}
				$ret .= "<div class='photonic-object-header photonic-{$this->provider}-$type $popup_header_class'>";

				if (empty($hidden['thumbnail'])) {
					$ret .= $anchor;
				}
				if (empty($hidden['title']) || empty($hidden['counter']) || empty($iterate_level_3)) {
					$ret .= "<div class='photonic-header-details photonic-$type-details'>";
					if (empty($hidden['title']) || empty($iterate_level_3)) {
						$provider = $this->provider;
						$expand = empty($iterate_level_3) ? '<a href="#" title="'.esc_attr__('Show', 'photonic').'" class="photonic-level-3-expand photonic-level-3-expand-plus" data-photonic-level-3="'.$provider.'-'.$type.'-'.$header['id'].'" data-photonic-layout="'.$options['layout'].'">&nbsp;</a>' : '';

						if ($link) {
							$ret .= "<div class='photonic-header-title photonic-$type-title'><a href='".$header['link_url']."' $target>".$title.'</a>'.$expand.'</div>';
						}
						else {
							$ret .= "<div class='photonic-header-title photonic-$type-title'>".$title.$expand.'</div>';
						}
					}
					if (empty($hidden['counter'])) {
						$counter_texts = array();
						if (!empty($counters['groups'])) {
							$counter_texts[] = sprintf(_n('%s group', '%s groups', $counters['groups'], 'photonic'), $counters['groups']);
						}
						if (!empty($counters['sets'])) {
							$counter_texts[] = sprintf(_n('%s set', '%s sets', $counters['sets'], 'photonic'), $counters['sets']);
						}
						if (!empty($counters['photos'])) {
							$counter_texts[] = sprintf(_n('%s photo', '%s photos', $counters['photos'], 'photonic'), $counters['photos']);
						}

						apply_filters('photonic_modify_counter_texts', $counter_texts, $counters);

						if (!empty($counter_texts)) {
							$ret .= "<span class='photonic-header-info photonic-$type-photos'>".implode(', ', $counter_texts).'</span>';
						}
					}

					$ret .= "</div><!-- .photonic-$type-details -->";
				}
				$ret .= "</div>";
			}
		}

		return $ret;
	}

	/**
	 * Generates the markup for a single photo.
	 *
	 * @param $provider string Name of the photo provider. A CSS class is created in the header, photonic-single-<code>$provider</code>-photo-header
	 * @param $data array Pertinent pieces of information about the photo - the source (src), the photo page (href), title and caption
	 * @return string
	 */
	function generate_single_photo_markup($provider, $data) {
		$ret = '';
		$photo = array_merge(
			array('src' => '', 'href' => '', 'title' => '', 'caption' => ''),
			$data
		);

		if (empty($photo['src'])) {
			return $ret;
		}

		global $photonic_external_links_in_new_tab;
		if (!empty($photo['title'])) {
			$ret .= "\t".'<h3 class="photonic-single-photo-header photonic-single-'.$provider.'-photo-header">'.$photo['title']."</h3>\n";
		}

		$img = '<img src="'.$photo['src'].'" alt="'.esc_attr(empty($photo['caption']) ? $photo['title'] : $photo['caption']).'" />';
		if (!empty($photo['href'])) {
			$img = '<a href="'.$photo['href'].'" title="'.esc_attr(empty($photo['caption']) ? $photo['title'] : $photo['caption']).'" '.
				(!empty($photonic_external_links_in_new_tab) ? ' target="_blank" ' : '').'>'.$img.'</a>';
		}

		if (!empty($photo['caption'])) {
			$ret .= "\t".'<div class="wp-caption">'."\n\t\t".$img."\n\t\t".'<div class="wp-caption-text">'.$photo['caption']."</div>\n\t</div><!-- .wp-caption -->\n";
		}
		else {
			$ret .= $img;
		}

		return $ret;
	}

	/**
	 * Generates the HTML for the lowest level gallery, i.e. the photos. This is used for both, in-page and popup displays.
	 * This calls an individual layout generator for rendering the gallery.
	 * The code for the random layouts is handled in JS, but just the HTML markers for it are provided here.
	 *
	 * @param $photos
	 * @param array $options
	 * @param $short_code
	 * @return string
	 */
	function display_level_1_gallery($photos, $options, $short_code) {
		$layout = !empty($short_code['layout']) ? $short_code['layout'] : 'square';
		$layout_manager = $this->get_layout_manager($layout);
		$ret = $layout_manager->generate_level_1_gallery($photos, $options, $short_code, $this);
		return $ret;
	}

	function display_level_2_gallery($objects, $options, $short_code) {
		$layout = !isset($options['layout']) ? 'square' : $options['layout'];
		$layout_manager = $this->get_layout_manager($layout);
		$ret = $layout_manager->generate_level_2_gallery($objects, $options, $short_code, $this);
		return $ret;
	}

	function finalize_markup($content, $short_code) {
		if ($short_code['display'] != 'popup') {
			$ret = "<div class='photonic-{$this->provider}-stream photonic-stream' id='photonic-{$this->provider}-stream-{$this->gallery_index}'>\n";
		}
		else {
			$popup_id = "id='photonic-{$this->provider}-panel-" . $short_code['panel'] . "'";
			$ret = "<div class='photonic-{$this->provider}-panel photonic-panel' $popup_id>\n";
		}
		$ret .= $content."\n";
		$ret .= "</div><!-- .photonic-stream or .photonic-panel -->\n";
		return $ret;
	}

	function get_layout_manager($layout) {
		global $photonic_layout_manager_default, $photonic_layout_manager_slideshow;
		if (in_array($layout, array('strip-above', 'strip-below', 'strip-right', 'no-strip'))) {
			if (!isset($photonic_layout_manager_slideshow)) {
				$photonic_layout_manager_slideshow = new Photonic_Layout_Slideshow();
			}
			$layout_manager = $photonic_layout_manager_slideshow;
		}
		else {
			if (!isset($photonic_layout_manager_default)) {
				$photonic_layout_manager_default = new Photonic_Layout();
			}
			$layout_manager = $photonic_layout_manager_default;
		}
		return $layout_manager;
	}

	function get_header_display($args) {
		if (!isset($args['headers'])) {
			return array(
				'thumbnail' => 'inherit',
				'title' => 'inherit',
				'counter' => 'inherit',
			);
		}
		else if (empty($args['headers'])) {
			return array (
				'thumbnail' => 'none',
				'title' => 'none',
				'counter' => 'none',
			);
		}
		else {
			$header_array = explode(',', $args['headers']);
			return array(
				'thumbnail' => in_array('thumbnail', $header_array) ? 'show' : 'none',
				'title' => in_array('title', $header_array) ? 'show' : 'none',
				'counter' => in_array('counter', $header_array) ? 'show' : 'none',
			);
		}
	}

	function get_hidden_headers($arg_headers, $setting_headers) {
		return array(
			'thumbnail' => $arg_headers['thumbnail'] === 'inherit' ? $setting_headers['thumbnail'] : ($arg_headers['thumbnail'] === 'none' ? true : false),
			'title' => $arg_headers['title'] === 'inherit' ? $setting_headers['title'] : ($arg_headers['title'] === 'none' ? true : false),
			'counter' => $arg_headers['counter'] === 'inherit' ? $setting_headers['counter'] : ($arg_headers['counter'] === 'none' ? true : false),
		);
	}
}
