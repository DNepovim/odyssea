<?php
/**
 * Processor for native WP galleries. This extends the Photonic_Processor class and defines methods local to WP.
 *
 * @package Photonic
 * @subpackage Extensions
 */

class Photonic_Native_Processor extends Photonic_Processor {
	function __construct() {
		parent::__construct();
		global $photonic_wp_disable_title_link;
		$this->provider = 'wp';
		$this->link_lightbox_title = empty($photonic_wp_disable_title_link);
	}

	/**
	 * Gets all images associated with the gallery. This method is lifted almost verbatim from the gallery short-code function provided by WP.
	 * We will take the gallery images and do some fun stuff with styling them in other methods. We cannot use the WP function because
	 * this code is nested within the gallery_shortcode function and we want to tweak that (there is no hook that executes after
	 * the gallery has been retrieved.
	 *
	 * @param array $attr
	 * @return array|bool
	 */
	function get_gallery_images($attr = array()) {
		global $post, $photonic_wp_title_caption;
		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if (isset($attr['orderby'])) {
			$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
			if (!$attr['orderby'])
				unset($attr['orderby']);
		}

		$attr = array_merge(
			$this->common_parameters,
			array(
				'caption' => $photonic_wp_title_caption,

				'order' => 'ASC',
				'orderby' => 'menu_order ID',
				'id' => $post->ID,
				'itemtag' => 'dl',
				'icontag' => 'dt',
				'captiontag' => 'dd',
				'size' => 'thumbnail',
				'include' => '',
				'exclude' => '',

				'page' => 1,
				'count' => -1,

				'thumb_width' => 75,
				'thumb_height' => 75,
				'thumb_size' => 'thumbnail',
				'slide_size' => 'large',
				'slideshow_height' => 500,

				'controls' => 'hide',
			), $attr);
		$attr['layout'] = $attr['style'];

		$attr = array_map('trim', $attr);
		extract($attr);

		$id = intval($attr['id']);
		if ('RAND' == $attr['order'])
			$attr['orderby'] = 'none';

		$args = array('post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $attr['order'], 'orderby' => $attr['orderby'], 'paged' => $attr['page']);

		if (!empty($attr['include'])) {
			$include = preg_replace('/[^0-9,]+/', '', $attr['include']);
			$args['include'] = $include;
			$attr['count'] = -1; // 'include' always ignores the 'posts_per_page'. Having the original value here shows the "More" button though not required.
			$_attachments = get_posts($args);
			$total_posts = count($_attachments);

			$attachments = array();
			foreach ($_attachments as $key => $val) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		}
		else {
			$args['post_parent'] = $id;
			if (!empty($attr['exclude'])) {
				$exclude = preg_replace('/[^0-9,]+/', '', $attr['exclude']);
				$args['exclude'] = $exclude;
			}
			// First get the total
			$attachments = get_children($args);
			$total_posts = count($attachments);

			$args['posts_per_page'] = $attr['count'];
			$attachments = get_children($args);
		}

		$this->gallery_index++;
		$ret = $this->process_gallery($attachments, $attr, $total_posts);
		return $ret;
	}

	function build_level_1_objects($images, $shortcode_attr) {
		$photo_objects = array();
		$thumb_size = $shortcode_attr['thumb_size'];
		$main_size = $shortcode_attr['slide_size'];
		foreach ( $images as $id => $attachment ) {
			$wp_details = wp_prepare_attachment_for_js($id);
			$sources[$id] = wp_get_attachment_image_src($id, $main_size, false);
			$thumbs[$id] = wp_get_attachment_image_src($id, $thumb_size);

			if (isset($attachment->post_title)) {
				$title = wptexturize($attachment->post_title);
			}
			else {
				$title = '';
			}
			$title = apply_filters('photonic_modify_title', $title, $attachment);

			if (is_array($wp_details)) {
				$photo_object = array();
				$photo_object['thumbnail'] = $thumbs[$id][0];
				$photo_object['main_image'] = $sources[$id][0];
				$photo_object['tile_image'] = $sources[$id][0];
				$photo_object['title'] = esc_attr($title);
				$photo_object['alt_title'] = $photo_object['title'];
				$photo_object['description'] = esc_attr($wp_details['caption']);
				$photo_object['main_page'] = $wp_details['link'];
				$photo_object['id'] = $wp_details['id'];

				$photo_objects[] = $photo_object;
			}
		}
		return $photo_objects;
	}

	/**
	 * Builds the markup for a gallery when you choose to use a specific gallery style. The following styles are allowed:
	 *    1. strip-below: Shows thumbnails for the gallery below a larger image
	 *    2. strip-above: Shows thumbnails for the gallery above a larger image
	 *    3. no-strip: Doesn't show thumbnails. Useful if you are making it behave like an automatic slideshow.
	 *    4. launch: Shows a thumbnail for the gallery, which you can click to launch a slideshow.
	 *    5. random: Shows a random justified gallery.
	 *    6. default: Shows the native WP styling
	 *
	 * @param $attachments
	 * @param $shortcode_attr
	 * @param int $total_posts
	 * @return string
	 */
	function process_gallery($attachments, $shortcode_attr, $total_posts = -1) {
		global $photonic_wp_thumbnail_title_display;
		if ($shortcode_attr['style'] == 'default') {
			return '';
		}
		$photos = $this->build_level_1_objects($attachments, $shortcode_attr);

		$row_constraints = array('constraint-type' => $shortcode_attr['columns'] == 'auto' ? 'padding' : 'count', 'padding' => 0, 'count' => Photonic::check_integer($shortcode_attr['columns']) ? $shortcode_attr['columns'] : 3);
		$ret = $this->display_level_1_gallery($photos,
			array(
				'title_position' => $photonic_wp_thumbnail_title_display,
				'row_constraints' => $row_constraints,
				'parent' => 'stream',
				'level_2_meta' => array(
					'total' => $total_posts,
					'start' => $shortcode_attr['count'] < 0 ? 1 : ($shortcode_attr['page'] - 1) * $shortcode_attr['count'] + 1,
					'end' => ($shortcode_attr['count'] < 0 || $shortcode_attr['page'] * $shortcode_attr['count'] > $total_posts) ? $total_posts : $shortcode_attr['page'] * $shortcode_attr['count'],
					'per-page' => $shortcode_attr['count'],
				),
			),
			$shortcode_attr);
		$ret = $this->finalize_markup($ret, $shortcode_attr);
		return $ret;
	}

	/**
	 * Access Token URL
	 *
	 * @return string
	 */
	public function access_token_URL() {
		// TODO: Implement access_token_URL() method.
	}

	/**
	 * Authenticate URL
	 *
	 * @return string
	 */
	public function authenticate_URL() {
		// TODO: Implement authenticate_URL() method.
	}

	/**
	 * Authorize URL
	 *
	 * @return string
	 */
	public function authorize_URL() {
		// TODO: Implement authorize_URL() method.
	}

	/**
	 * Request Token URL
	 *
	 * @return string
	 */
	public function request_token_URL() {
		// TODO: Implement request_token_method() method.
	}

	public function end_point() {
		// TODO: Implement end_point() method.
	}

	function parse_token($response) {
		// TODO: Implement parse_token() method.
	}

	public function check_access_token_method() {
		// TODO: Implement check_access_token_method() method.
	}
}
