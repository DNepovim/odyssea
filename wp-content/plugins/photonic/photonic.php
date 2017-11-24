<?php
/**
 * Plugin Name: Photonic Gallery for Flickr, Picasa, SmugMug, 500px, Zenfolio and Instagram
 * Plugin URI: https://aquoid.com/plugins/photonic/
 * Description: Extends the native gallery shortcode to support Flickr, Picasa, SmugMug, 500px, Zenfolio and Instagram. JS libraries like Swipebox, Fancybox, Colorbox, PrettyPhoto, Image Lightbox, Lightcase and Lightgallery are supported. Photos are displayed in a grid of square or circular thumbnails, or a randomized set of tiles. The plugin also helps convert a regular WP gallery into a slideshow.
 * Version: 1.64
 * Author: Sayontan Sinha
 * Author URI: https://mynethome.net/
 * License: GNU General Public License (GPL), v3 (or newer)
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: photonic
 *
 * Copyright (c) 2011 - 2017 Sayontan Sinha. All rights reserved.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class Photonic {
	var $version, $registered_extensions, $defaults, $plugin_name, $options_page_name, $settings_page, $helper_page, $getting_started_page, $layout_manager_default, $layout_manager_slideshow;
	function __construct() {
		global $photonic_options, $photonic_setup_options, $photonic_is_IE;
		if (!defined('PHOTONIC_VERSION')) {
			define('PHOTONIC_VERSION', '1.64');
		}

		if (!defined('PHOTONIC_PATH')) {
			define('PHOTONIC_PATH', plugin_dir_path(__FILE__));
		}

		if (!defined('PHOTONIC_URL')) {
			define('PHOTONIC_URL', plugin_dir_url(__FILE__));
		}

		$upload_dir = wp_upload_dir();
		if (!defined('PHOTONIC_UPLOAD_DIR')) {
			define('PHOTONIC_UPLOAD_DIR', trailingslashit($upload_dir['basedir']).'photonic');
		}

		if (!defined('PHOTONIC_UPLOAD_URL')) {
			define('PHOTONIC_UPLOAD_URL', trailingslashit($upload_dir['baseurl']).'photonic');
		}

		//WP provides a global $is_IE, but we specifically need to find IE6x (or, heaven forbid, IE5x). Note that older versions of Opera used to identify themselves as IE6, so we exclude Opera.
		$photonic_is_IE = preg_match('/MSIE [56789]/i', $_SERVER['HTTP_USER_AGENT']);

		require_once(plugin_dir_path(__FILE__)."/options/photonic-options.php");

		$this->plugin_name = plugin_basename(__FILE__);
		$this->set_version();

		add_action('admin_menu', array(&$this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_admin_scripts'));
		add_action('admin_init', array(&$this, 'admin_init'));

		$photonic_options = get_option('photonic_options');
		if (isset($photonic_options) && is_array($photonic_options)) {
			foreach ($photonic_setup_options as $default_option) {
				if (isset($default_option['id'])) {
					$mod_key = 'photonic_'.$default_option['id'];
					global $$mod_key;
					if (isset($photonic_options[$default_option['id']])) {
						$$mod_key = $photonic_options[$default_option['id']];
					}
					else {
						$$mod_key = $default_option['std'];
					}
				}
			}
		}
		if (isset($photonic_options) && is_array($photonic_options)) {
			foreach ($photonic_options as $key => $value) {
				$mod_key = 'photonic_'.$key;
				global $$mod_key;
				$$mod_key = $value;
			}
		}

		add_action('admin_head', array($this, 'admin_head'));

		// Gallery
		if (!empty($photonic_alternative_shortcode)) {
			add_shortcode($photonic_alternative_shortcode, array(&$this, 'modify_gallery'));
		}
		else {
			add_filter('post_gallery', array(&$this, 'modify_gallery'), 20, 2);
		}
		add_action('wp_enqueue_scripts', array(&$this, 'add_scripts'), 20);
		add_action('wp_head', array(&$this, 'print_scripts'), 20);
		add_action('wp_loaded', array(&$this, 'check_authentication'), 20);

		add_action('wp_ajax_photonic_display_level_2_contents', array(&$this, 'display_level_2_contents'));
		add_action('wp_ajax_nopriv_photonic_display_level_2_contents', array(&$this, 'display_level_2_contents'));

		add_action('wp_ajax_photonic_display_level_3_contents', array(&$this, 'display_level_3_contents'));
		add_action('wp_ajax_nopriv_photonic_display_level_3_contents', array(&$this, 'display_level_3_contents'));

		add_action('wp_ajax_photonic_load_more', array(&$this, 'load_more'));
		add_action('wp_ajax_nopriv_photonic_load_more', array(&$this, 'load_more'));

		add_filter('media_upload_tabs', array(&$this, 'media_upload_tabs'));
		add_action('media_upload_photonic', array(&$this, 'media_upload_photonic'));

		add_action('print_media_templates', array(&$this, 'edit_gallery'));

		add_action('wp_ajax_photonic_invoke_helper', array(&$this, 'invoke_helper'));
		add_action('wp_ajax_photonic_obtain_token', array(&$this, 'obtain_token'));

		$this->registered_extensions = array();
		$this->add_extensions();

		add_action('template_redirect', array(&$this, 'get_oauth2_access_token'));

		add_action('wp_ajax_photonic_authenticate', array(&$this, 'authenticate'));
		add_action('wp_ajax_nopriv_photonic_authenticate', array(&$this, 'authenticate'));

		add_action('plugins_loaded', array(&$this, 'enable_translations'));

		add_filter('body_class', array(&$this, 'body_class'), 10, 2);
	}

	/**
	 * Adds a menu item to the "Settings" section of the admin page.
	 *
	 * @return void
	 */
	function add_admin_menu() {
		global $photonic_options_manager;
		$this->options_page_name = add_menu_page('Photonic', 'Photonic', 'edit_theme_options', 'photonic-options-manager', array(&$photonic_options_manager, 'render_settings_page'), plugins_url('include/images/Photonic-16.png', __FILE__));
		$this->settings_page = add_submenu_page('photonic-options-manager', 'Settings', 'Settings', 'edit_theme_options', 'photonic-options-manager', array(&$photonic_options_manager, 'render_settings_page'));
		$this->getting_started_page = add_submenu_page('photonic-options-manager', 'Getting Started', 'Getting Started', 'edit_theme_options', 'photonic-getting-started', array(&$photonic_options_manager, 'render_getting_started'));
		$this->helper_page = add_submenu_page('photonic-options-manager', 'Helpers', 'Helpers', 'edit_theme_options', 'photonic-helpers', array(&$photonic_options_manager, 'render_helpers'));

		$this->set_version();
	}

	/**
	 * Adds all scripts and their dependencies to the <head> of the Photonic administration page. This takes care to not add scripts on other admin pages.
	 *
	 * @param $hook
	 * @return void
	 */
	function add_admin_scripts($hook) {
		if ($this->options_page_name == $hook) {
			wp_enqueue_style('photonic-admin-css', plugins_url('include/css/admin.css', __FILE__), array('wp-color-picker'), $this->version);
			global $photonic_options;
			$js_array = array(
				'category' => isset($photonic_options) && isset($photonic_options['last-set-section']) ? $photonic_options['last-set-section'] : 'generic-settings',
			);
			wp_enqueue_script('photonic-admin-js', plugins_url('include/scripts/admin.js', __FILE__), array('jquery', 'wp-color-picker'), $this->version);
			wp_localize_script('photonic-admin-js', 'Photonic_Admin_JS', $js_array);
		}
		else if ($this->helper_page == $hook) {
			wp_enqueue_script('photonic-admin-js', plugins_url('include/scripts/admin-helpers.js', __FILE__), array('jquery'), $this->version);
			wp_enqueue_style('photonic-admin-css', plugins_url('include/css/admin.css', __FILE__), array(), $this->version);
			$js_array = array(
				'obtain_token' => esc_attr__('Step 2: Obtain Token', 'photonic')
			);
			wp_localize_script('photonic-admin-js', 'Photonic_Admin_JS', $js_array);
		}
		else if ($this->getting_started_page == $hook) {
			wp_enqueue_style('photonic-admin-css', plugins_url('include/css/admin.css', __FILE__), array(), $this->version);
		}
		else if ('media-upload-popup' == $hook) {
			wp_enqueue_script('jquery');
			wp_enqueue_style('photonic-upload', plugins_url('include/css/admin-form.css', __FILE__), array(), $this->version);
		}
		else if ('post-new.php' == $hook || 'post.php' == $hook) {
			global $photonic_alternative_shortcode, $photonic_default_gallery_type, $photonic_disable_editor;
			if (empty($photonic_disable_editor)) {
				$js_array = array(
					'shortcode' => empty($photonic_alternative_shortcode) ? 'gallery' : $photonic_alternative_shortcode,
					'default_gallery_type' => empty($photonic_default_gallery_type) ? 'default' : $photonic_default_gallery_type,
					'plugin_dir' => plugin_dir_url(__FILE__),
				);
				wp_enqueue_script('photonic-admin-js', plugins_url('include/scripts/gallery-settings.js', __FILE__), array('jquery', 'media-views'), $this->version);
				wp_localize_script('photonic-admin-js', 'Photonic_Admin_JS', $js_array);
				wp_enqueue_style('photonic-upload', plugins_url('include/css/admin-form.css', __FILE__), array(), $this->version);
				add_editor_style(plugins_url('include/css/admin-mce.css', __FILE__));
			}
		}
	}

	/**
	 * Adds all scripts and their dependencies to the <head> section of the page.
	 *
	 * @return void
	 */
	function add_scripts() {
		global $photonic_slideshow_library, $photonic_custom_lightbox_js, $photonic_custom_lightbox_css, $photonic_is_IE;

		$photonic_dependencies = array('jquery', 'jquery-ui-tooltip', 'jquery-ui-dialog',);

		wp_enqueue_script('photonic-slideshow', plugins_url('include/scripts/lightslider/lightslider.min.js', __FILE__), array('jquery'), $this->version);

		if ($photonic_is_IE) {
			wp_enqueue_script('photonic-masonry', plugins_url('include/scripts/masonry/masonry.pkgd.min.js', __FILE__), array('jquery'), $this->version);
			wp_enqueue_script('photonic-ie', plugins_url('include/scripts/photonic-ie.js', __FILE__), array('photonic'), $this->version);
		}

		if ($photonic_slideshow_library == 'thickbox') {
			wp_enqueue_script('thickbox');
			$photonic_dependencies[] = 'thickbox';
		}
		else if ($photonic_slideshow_library == 'custom') {
			$counter = 1;
			$dependencies = array('jquery');
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $photonic_custom_lightbox_js) as $line){
				wp_enqueue_script('photonic-lightbox-'.$counter, trim($line), $dependencies, $this->version);
//				$photonic_dependencies[] = 'photonic-lightbox-'.$counter;
				$counter++;
			}
		}
		else if ($photonic_slideshow_library != 'none') {
			wp_enqueue_script('photonic-lightbox', plugins_url('include/scripts/'.$photonic_slideshow_library.'/'.$photonic_slideshow_library.'.min.js', __FILE__), array('jquery'), $this->version);
			if ($photonic_slideshow_library == 'lightgallery') {
				global $photonic_enable_lg_zoom, $photonic_enable_lg_thumbnail, $photonic_enable_lg_fullscreen, $photonic_enable_lg_autoplay;
				$lightgallery_plugins = array();
				if (!empty($photonic_enable_lg_autoplay)) { $lightgallery_plugins[] = 'autoplay'; }
				if (!empty($photonic_enable_lg_fullscreen)) { $lightgallery_plugins[] = 'fullscreen'; }
				if (!empty($photonic_enable_lg_thumbnail)) { $lightgallery_plugins[] = 'thumbnail'; }
				if (!empty($photonic_enable_lg_zoom)) { $lightgallery_plugins[] = 'zoom'; }
				foreach ($lightgallery_plugins as $plugin) {
					wp_enqueue_script('photonic-lightbox-'.$plugin, plugins_url('include/scripts/'.$photonic_slideshow_library.'/lg-plugin-'.$plugin.'.min.js', __FILE__), array('photonic-lightbox'), $this->version);
				}
			}
			$photonic_dependencies[] = 'photonic-lightbox';
		}

		wp_enqueue_script('photonic', plugins_url('include/scripts/photonic.js', __FILE__), $photonic_dependencies, $this->version);

		$js_array = $this->get_localized_js_variables();
		wp_localize_script('photonic', 'Photonic_JS', $js_array);

		$template_directory = get_template_directory();
		$stylesheet_directory = get_stylesheet_directory();

		wp_enqueue_style("photonic-slideshow", plugins_url('include/scripts/lightslider/css/lightslider.css', __FILE__), array(), $this->version);

		if ($photonic_slideshow_library == 'fancybox') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/fancybox/jquery.fancybox-1.3.4.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'colorbox') {
			global $photonic_cbox_theme;
			if ($photonic_cbox_theme == 'theme' && @file_exists($stylesheet_directory.'/scripts/colorbox/colorbox.css')) {
				wp_enqueue_style("photonic-lightbox", get_stylesheet_directory_uri().'/scripts/colorbox/colorbox.css', array(), $this->version);
			}
			else if ($photonic_cbox_theme == 'theme' && @file_exists($template_directory.'/scripts/colorbox/colorbox.css')) {
				wp_enqueue_style("photonic-lightbox", get_template_directory_uri().'/scripts/colorbox/colorbox.css', array(), $this->version);
			}
			else if ($photonic_cbox_theme == 'theme') {
				wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/colorbox/style-1/colorbox.css', __FILE__), array(), $this->version);
			}
			else {
				wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/colorbox/style-'.$photonic_cbox_theme.'/colorbox.css', __FILE__), array(), $this->version);
			}
		}
		else if ($photonic_slideshow_library == 'prettyphoto') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/prettyphoto/css/prettyPhoto.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'swipebox') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/swipebox/css/swipebox.min.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'imagelightbox') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/imagelightbox/imagelightbox.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'lightcase') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/lightcase/lightcase.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'lightgallery') {
			wp_enqueue_style("photonic-lightbox", plugins_url('include/scripts/lightgallery/css/lightgallery.min.css', __FILE__), array(), $this->version);
		}
		else if ($photonic_slideshow_library == 'thickbox') {
			wp_enqueue_style('thickbox');
		}
		else if ($photonic_slideshow_library == 'custom') {
			$counter = 1;
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $photonic_custom_lightbox_css) as $line){
				wp_enqueue_style('photonic-lightbox-'.$counter, trim($line), array(), $this->version);
				$counter++;
			}
		}

		wp_enqueue_style('photonic', plugins_url('include/css/photonic.css', __FILE__), array(), $this->version);
		global $photonic_css_in_file;
		$file = trailingslashit(PHOTONIC_UPLOAD_DIR).'custom-styles.css';
		if (@file_exists($file) && !empty($photonic_css_in_file)) {
			wp_enqueue_style('photonic-custom', trailingslashit(PHOTONIC_UPLOAD_URL).'custom-styles.css', array(), $this->version);
		}
	}

	function print_scripts() {
		global $photonic_css_in_file;
		$file = trailingslashit(PHOTONIC_UPLOAD_DIR).'custom-styles.css';
		if (!@file_exists($file) || empty($photonic_css_in_file)) {
			$this->generate_css();
		}
	}

	/**
	 * Prints the dynamically generated CSS based on option selections.
	 *
	 * @param bool $header
	 * @return string
	 */
	function generate_css($header = true) {
		global $photonic_flickr_collection_set_constrain_by_padding, $photonic_flickr_photos_constrain_by_padding, $photonic_flickr_photos_pop_constrain_by_padding, $photonic_flickr_galleries_constrain_by_padding;
		global $photonic_picasa_photos_pop_constrain_by_padding, $photonic_picasa_photos_constrain_by_padding, $photonic_500px_photos_constrain_by_padding;
		global $photonic_smug_photos_constrain_by_padding, $photonic_smug_photos_pop_constrain_by_padding, $photonic_smug_albums_album_constrain_by_padding, $photonic_instagram_photos_constrain_by_padding;
		global $photonic_zenfolio_photos_constrain_by_padding, $photonic_zenfolio_sets_constrain_by_padding, $photonic_tile_spacing, $photonic_masonry_tile_spacing, $photonic_mosaic_tile_spacing, $photonic_masonry_min_width;

		$css = '';
		if ($header) {
			$css .= '<style type="text/css">'."\n";
		}
		$css .= ".photonic-flickr-stream .photonic-pad-photosets { margin: {$photonic_flickr_collection_set_constrain_by_padding}px; }\n";
		$css .= ".photonic-flickr-stream .photonic-pad-galleries { margin: {$photonic_flickr_galleries_constrain_by_padding}px; }\n";
		$css .= ".photonic-flickr-stream .photonic-pad-photos { padding: 5px {$photonic_flickr_photos_constrain_by_padding}px; }\n";

		$css .= ".photonic-picasa-stream .photonic-pad-photos { padding: 5px {$photonic_picasa_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-picasa-stream img { ".$this->get_border_css('photonic_picasa_photo_thumb_border').$this->get_padding_css('photonic_picasa_photo_thumb_padding')." }\n";
		$css .= ".photonic-panel .photonic-picasa-image img { ".$this->get_border_css('photonic_picasa_photo_pop_thumb_border').$this->get_padding_css('photonic_picasa_photo_pop_thumb_padding')." }\n";

		$css .= ".photonic-500px-stream .photonic-pad-photos { padding: 5px {$photonic_500px_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-500px-stream img { ".$this->get_border_css('photonic_500px_photo_thumb_border').$this->get_padding_css('photonic_500px_photo_thumb_padding')." }\n";

		$css .= ".photonic-zenfolio-stream .photonic-pad-photos { padding: 5px {$photonic_zenfolio_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-zenfolio-stream .photonic-pad-photosets { margin: 5px {$photonic_zenfolio_sets_constrain_by_padding}px; }\n";
		$css .= ".photonic-zenfolio-photo img { ".$this->get_border_css('photonic_zenfolio_photo_thumb_border').$this->get_padding_css('photonic_zenfolio_photo_thumb_padding')." }\n";
		$css .= ".photonic-zenfolio-set-thumb img { ".$this->get_border_css('photonic_zenfolio_sets_set_thumb_border').$this->get_padding_css('photonic_zenfolio_sets_set_thumb_padding')." }\n";

		$css .= ".photonic-instagram-stream .photonic-pad-photos { padding: 5px {$photonic_instagram_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-instagram-photo img { ".$this->get_border_css('photonic_instagram_photo_thumb_border').$this->get_padding_css('photonic_instagram_photo_thumb_padding')." }\n";

		$css .= ".photonic-smug-stream .photonic-pad-albums { margin: {$photonic_smug_albums_album_constrain_by_padding}px; }\n";
		$css .= ".photonic-smug-stream .photonic-pad-photos { padding: 5px {$photonic_smug_photos_constrain_by_padding}px; }\n";
		$css .= ".photonic-smug-stream img { ".$this->get_border_css('photonic_smug_photo_thumb_border').$this->get_padding_css('photonic_smug_photo_thumb_padding')." }\n";
		$css .= ".photonic-panel .photonic-smug-image img { ".$this->get_border_css('photonic_smug_photo_pop_thumb_border').$this->get_padding_css('photonic_smug_photo_pop_thumb_padding')." }\n";

		$css .= ".photonic-panel { ".$this->get_bg_css('photonic_flickr_gallery_panel_background').$this->get_border_css('photonic_flickr_set_popup_thumb_border')." }\n";

		$css .= ".photonic-panel .photonic-flickr-image img { ".$this->get_border_css('photonic_flickr_pop_photo_thumb_border').$this->get_padding_css('photonic_flickr_pop_photo_thumb_padding')." }\n";
		$css .= ".photonic-flickr-panel .photonic-pad-photos { padding: 10px {$photonic_flickr_photos_pop_constrain_by_padding}px; box-sizing: border-box; }\n";
		$css .= ".photonic-picasa-panel .photonic-pad-photos { padding: 10px {$photonic_picasa_photos_pop_constrain_by_padding}px; box-sizing: border-box; }\n";
		$css .= ".photonic-smug-panel .photonic-pad-photos { padding: 10px {$photonic_smug_photos_pop_constrain_by_padding}px; box-sizing: border-box; }\n";
		$css .= ".photonic-flickr-coll-thumb img { ".$this->get_border_css('photonic_flickr_coll_thumb_border').$this->get_padding_css('photonic_flickr_coll_thumb_padding')." }\n";
		$css .= ".photonic-flickr-set .photonic-flickr-set-solo-thumb img { ".$this->get_border_css('photonic_flickr_set_alone_thumb_border').$this->get_padding_css('photonic_flickr_set_alone_thumb_padding')." }\n";
		$css .= ".photonic-flickr-stream .photonic-flickr-photo img { ".$this->get_border_css('photonic_flickr_photo_thumb_border').$this->get_padding_css('photonic_flickr_photo_thumb_padding')." }\n";
		$css .= ".photonic-flickr-set-thumb img { ".$this->get_border_css('photonic_flickr_sets_set_thumb_border').$this->get_padding_css('photonic_flickr_sets_set_thumb_padding')." }\n";
		$css .= ".photonic-smug-album-thumb img { ".$this->get_border_css('photonic_smug_albums_album_thumb_border').$this->get_padding_css('photonic_smug_albums_album_thumb_padding')." }\n";

		$css .= ".photonic-random-layout .photonic-tiled-photo { padding: {$photonic_tile_spacing}px}\n";
		$css .= ".photonic-masonry-layout .photonic-thumb { padding: {$photonic_masonry_tile_spacing}px}\n";
		$css .= ".photonic-mosaic-layout .photonic-thumb { padding: {$photonic_mosaic_tile_spacing}px}\n";

		$css .= ".photonic-ie .photonic-masonry-layout .photonic-level-1, .photonic-ie .photonic-masonry-layout .photonic-level-2 { width: ".(Photonic::check_integer($photonic_masonry_min_width) ? $photonic_masonry_min_width : 200)."px; }\n";

		if ($header) {
			$css .= "\n</style>\n";
			echo $css;
		}
		return $css;
	}

	function set_version() {
		// Cannot use get_plugin_data in a non-admin view :-(
/*		$plugin_data = get_plugin_data(__FILE__);
		$this->version = $plugin_data['Version'];*/
		$this->version = PHOTONIC_VERSION;
	}

	function admin_init() {
		if (isset($_REQUEST['page']) && ('photonic-options-manager' == $_REQUEST['page'] || 'photonic-options' == $_REQUEST['page'] || 'photonic-helpers' == $_REQUEST['page']  || 'photonic-getting-started' == $_REQUEST['page'])) {
			global $photonic_options_manager;
			require_once(plugin_dir_path(__FILE__)."/photonic-options-manager.php");
			$photonic_options_manager = new Photonic_Options_Manager(__FILE__, $this);
			$photonic_options_manager->init();
		}
	}

	function add_extensions() {
		require_once(plugin_dir_path(__FILE__)."/extensions/Photonic_Processor.php");
		require_once(plugin_dir_path(__FILE__)."/extensions/Photonic_OAuth1_Processor.php");
		require_once(plugin_dir_path(__FILE__)."/extensions/Photonic_OAuth2_Processor.php");
		$this->register_extension('Photonic_Flickr_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Flickr_Processor.php");
		$this->register_extension('Photonic_Picasa_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Picasa_Processor.php");
		$this->register_extension('Photonic_Native_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Native_Processor.php");
		$this->register_extension('Photonic_500px_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_500px_Processor.php");
		$this->register_extension('Photonic_SmugMug_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_SmugMug_Processor.php");
		$this->register_extension('Photonic_Instagram_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Instagram_Processor.php");
		$this->register_extension('Photonic_Zenfolio_Processor', plugin_dir_path(__FILE__)."/extensions/Photonic_Zenfolio_Processor.php");

		require_once(plugin_dir_path(__FILE__).'/layouts/Layout_Default.php');
		require_once(plugin_dir_path(__FILE__).'/layouts/Layout_Slideshow.php');

		do_action('photonic_register_extensions');
	}

	public function register_extension($extension, $path) {
		if (@!file_exists($path)) {
			return;
		}
		require_once($path);
		if (!class_exists($extension) || is_subclass_of($extension, 'Photonic_Processor')) {
			return;
		}
		$this->registered_extensions[] = $extension;
	}

	/**
	 * Overrides the native gallery short code, and does a lot more.
	 *
	 * @param $content
	 * @param array $attr
	 * @return string
	 */
	function modify_gallery($content, $attr = array()) {
		global $post, $photonic_flickr_gallery, $photonic_picasa_gallery, $photonic_native_gallery, $photonic_500px_gallery,
			   $photonic_smugmug_gallery, $photonic_default_gallery_type, $photonic_nested_shortcodes, $photonic_instagram_gallery,
			   $photonic_zenfolio_gallery, $photonic_alternative_shortcode, $photonic_thumbnail_style;

		// If an alternative shortcode is used, then $content has the shortcode attributes
		if (!empty($photonic_alternative_shortcode)) {
			$attr = $content;
		}
		if ($attr == null) {
			$attr = array();
		}

		$attr = array_merge(array(
			// Specially for Photonic
			'type' 		=> $photonic_default_gallery_type,  //default, flickr, picasa
			'style' 	=> 'default',   //default, strip-below, strip-above, strip-right, strip-left, no-strip, launch
			'id'		=> $post->ID,
			'layout' => isset($photonic_thumbnail_style) ? $photonic_thumbnail_style : 'square',
		), $attr);

		if ($photonic_nested_shortcodes) {
			$attr = array_map('do_shortcode', $attr);
		}

		extract($attr);

		$type = strtolower($type);
		switch ($type) {
			case 'flickr':
				$this->initialize_extension('flickr');
				$images = $photonic_flickr_gallery->get_gallery_images($attr);
				break;

			case 'picasa':
				$this->initialize_extension('picasa');
				$images = $photonic_picasa_gallery->get_gallery_images($attr);
				break;

			case '500px':
				$this->initialize_extension('500px');
				$images = $photonic_500px_gallery->get_gallery_images($attr);
				break;

			case 'smugmug':
				$this->initialize_extension('smugmug');
				$images = $photonic_smugmug_gallery->get_gallery_images($attr);
				break;

			case 'instagram':
				$this->initialize_extension('instagram');
				$images = $photonic_instagram_gallery->get_gallery_images($attr);
				break;

			case 'zenfolio':
				$this->initialize_extension('zenfolio');
				$images = $photonic_zenfolio_gallery->get_gallery_images($attr);
				break;

			case 'default':
			default:
				$this->initialize_extension('native');
				$images = $photonic_native_gallery->get_gallery_images($attr);
				break;
		}

		if (isset($images) && !is_array($images)) {
			return $images;
		}

		return $content;
	}

	/**
	 * @param $provider
	 * @return Photonic_Processor|Photonic_Flickr_Processor|Photonic_Instagram_Processor|Photonic_Native_Processor|Photonic_Picasa_Processor|Photonic_SmugMug_Processor
	 */
	function initialize_extension($provider) {
		global $photonic_flickr_gallery, $photonic_picasa_gallery, $photonic_500px_gallery, $photonic_smugmug_gallery, $photonic_instagram_gallery, $photonic_zenfolio_gallery, $photonic_native_gallery;
		if ($provider == 'flickr') {
			if (!isset($photonic_flickr_gallery)) $photonic_flickr_gallery = new Photonic_Flickr_Processor();
			return $photonic_flickr_gallery;
		}
		else if ($provider == 'picasa') {
			if (!isset($photonic_picasa_gallery)) $photonic_picasa_gallery = new Photonic_Picasa_Processor();
			return $photonic_picasa_gallery;
		}
		else if ($provider == '500px') {
			if (!isset($photonic_500px_gallery)) $photonic_500px_gallery = new Photonic_500px_Processor();
			return $photonic_500px_gallery;
		}
		else if ($provider == 'smugmug' || $provider == 'smug') {
			if (!isset($photonic_smugmug_gallery)) $photonic_smugmug_gallery = new Photonic_SmugMug_Processor();
			return $photonic_smugmug_gallery;
		}
		else if ($provider == 'zenfolio') {
			if (!isset($photonic_zenfolio_gallery)) $photonic_zenfolio_gallery = new Photonic_Zenfolio_Processor();
			return $photonic_zenfolio_gallery;
		}
		else if ($provider == 'instagram') {
			if (!isset($photonic_instagram_gallery)) $photonic_instagram_gallery = new Photonic_Instagram_Processor();
			return $photonic_instagram_gallery;
		}
		else {
			if (!isset($photonic_native_gallery)) $photonic_native_gallery = new Photonic_Native_Processor();
			return $photonic_native_gallery;
		}
	}

	/**
	 * Clicking on a level 2 object (i.e. an Album / Set / Gallery) triggers this. This will fetch the contents of the level 2 object and generate the markup for it.
	 *
	 * @return void
	 */
	function display_level_2_contents() {
		$panel = esc_attr($_POST['panel_id']);
		$components = explode('-', $panel);

		if (count($components) <= 5) {
			die();
		}
		$panel = implode('-', array_slice($components, 4, 10, true));
		$args = array('display' => 'popup', 'panel' => $panel, 'password' => sanitize_text_field($_POST['password']));

		$provider = $components[1];
		$type = $components[2];
		if ($provider == 'smug') {
			$this->check_authentication_smug();
			$args['view'] = 'album';
			$args['album_key'] = $components[4];
			$gallery = $this->initialize_extension('smugmug');
		}
		else if ($provider == 'zenfolio') {
			$args['view'] = 'photosets';
			$args['object_id'] = $components[4];
			$args['thumb_size'] = sanitize_text_field($_POST['thumb_size']);
			$args['realm_id'] = sanitize_text_field($_POST['realm_id']);
			$gallery = $this->initialize_extension('zenfolio');
		}
		else if ($provider == '500px') {
			$args['view'] = 'collections';
			$args['view_id'] = $components[4];
			$gallery = $this->initialize_extension('500px');
		}
		else if ($provider == 'picasa') {
			$args['view'] = 'album';
			$args['albumid'] = $components[4];
			$args['user_id'] = $components[count($components) - 1];
			$args['thumbsize'] = sanitize_text_field($_POST['thumb_size']);
			$args['authkey'] = sanitize_text_field($_POST['authkey']);
			$gallery = $this->initialize_extension('picasa');
		}
		else if ($provider == 'flickr') {
			if ($type == 'gallery') {
				$args['gallery_id'] = $components[4].'-'.$components[5];
				$args['gallery_id_computed'] = true;
			}
			else if ($type = 'set') {
				$args['photoset_id'] = $components[4];
			}
			$gallery = $this->initialize_extension('flickr');
		}

		echo $gallery->get_gallery_images($args);
		die();
	}

	function display_level_3_contents() {
		$node = esc_attr($_POST['node']);
		$components = explode('-', $node);

		if (count($components) <= 3) {
			die();
		}

		$args = array('display' => 'in-page', 'headers' => '', 'layout' => esc_attr($_POST['layout']));

		$provider = $components[0];
		if ($provider == 'flickr') {
			$args['collection_id'] = implode('-', array_slice($components, 2, 2, true));
			$args['user_id'] = $components[4];
			$gallery = $this->initialize_extension('flickr');
		}

		echo $gallery->get_gallery_images($args);
		die();
	}

	/**
	 * Checks if a text being passed to it is an integer or not.
	 *
	 * @param $val
	 * @return bool
	 */
	static function check_integer($val) {
		if (substr($val, 0, 1) == '-') {
			$val = substr($val, 1);
		}
		return (preg_match('/^\d*$/', $val) == 1);
	}

	/**
	 * Converts a string to a boolean variable, if possible.
	 *
	 * @param $value
	 * @return bool
	 */
	static function string_to_bool($value) {
		if ($value == true || $value == 'true' || $value == 'TRUE' || $value == '1') {
			return true;
		}
		else if ($value == false || $value == 'false' || $value == 'FALSE' || $value == '0') {
			return false;
		}
		else {
			return $value;
		}
	}

	/**
	 * Constructs the CSS for a "background" option
	 *
	 * @param $option
	 * @return string
	 */
	function get_bg_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$val_array = array();
			$vals = explode(';', $option_val);
			foreach ($vals as $val) {
				if (trim($val) == '') { continue; }
				$pair = explode('=', $val);
				$val_array[$pair[0]] = $pair[1];
			}
			$option_val = $val_array;
		}
		$bg_string = "";
		$bg_rgba_string = "";
		if (!isset($option_val['colortype']) || $option_val['colortype'] == 'transparent') {
			$bg_string .= " transparent ";
		}
		else {
			if (isset($option_val['color'])) {
				if (substr($option_val['color'], 0, 1) == '#') {
					$color_string = substr($option_val['color'],1);
				}
				else {
					$color_string = $option_val['color'];
				}
				$rgb_str_array = array();
				if (strlen($color_string)==3) {
					$rgb_str_array[] = substr($color_string, 0, 1).substr($color_string, 0, 1);
					$rgb_str_array[] = substr($color_string, 1, 1).substr($color_string, 1, 1);
					$rgb_str_array[] = substr($color_string, 2, 1).substr($color_string, 2, 1);
				}
				else {
					$rgb_str_array[] = substr($color_string, 0, 2);
					$rgb_str_array[] = substr($color_string, 2, 2);
					$rgb_str_array[] = substr($color_string, 4, 2);
				}
				$rgb_array = array();
				$rgb_array[] = hexdec($rgb_str_array[0]);
				$rgb_array[] = hexdec($rgb_str_array[1]);
				$rgb_array[] = hexdec($rgb_str_array[2]);
				$rgb_string = implode(',',$rgb_array);
				$rgb_string = ' rgb('.$rgb_string.') ';

				if (isset($option_val['trans'])) {
					$bg_rgba_string = $bg_string;
					$transparency = (int)$option_val['trans'];
					if ($transparency != 0) {
						$trans_dec = $transparency/100;
						$rgba_string = implode(',', $rgb_array);
						$rgba_string = ' rgba('.$rgba_string.','.$trans_dec.') ';
						$bg_rgba_string .= $rgba_string;
					}
				}

				$bg_string .= $rgb_string;
			}
		}
		if (isset($option_val['image']) && trim($option_val['image']) != '') {
			$bg_string .= " url(".$option_val['image'].") ";
			$bg_string .= $option_val['position']." ".$option_val['repeat'];

			if (trim($bg_rgba_string) != '') {
				$bg_rgba_string .= " url(".$option_val['image'].") ";
				$bg_rgba_string .= $option_val['position']." ".$option_val['repeat'];
			}
		}

		if (trim($bg_string) != '') {
			$bg_string = "background: ".$bg_string." !important;\n";
			if (trim($bg_rgba_string) != '') {
				$bg_string .= "\tbackground: ".$bg_rgba_string." !important;\n";
			}
		}
		return $bg_string;
	}

	/**
	 * Generates the CSS for borders. Each border, top, right, bottom and left is generated as a separate line.
	 *
	 * @param $option
	 * @return string
	 */
	function get_border_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$edge_array = $this->build_edge_array($option_val);
			$option_val = $edge_array;
		}
		$border_string = '';
		foreach ($option_val as $edge => $selections) {
			$border_string .= "\tborder-$edge: ";
			if (!isset($selections['style'])) {
				$selections['style'] = 'none';
			}
			if ($selections['style'] == 'none') {
				$border_string .= "none";
			}
			else {
				if (isset($selections['border-width'])) {
					$border_string .= $selections['border-width'];
				}
				if (isset($selections['border-width-type'])) {
					$border_string .= $selections['border-width-type'];
				}
				else {
					$border_string .= "px";
				}
				$border_string .= " ".$selections['style']." ";
				if ($selections['colortype'] == 'transparent') {
					$border_string .= "transparent";
				}
				else {
					if (substr($selections['color'], 0, 1) == '#') {
						$border_string .= $selections['color'];
					}
					else {
						$border_string .= '#'.$selections['color'];
					}
				}
			}
			$border_string .= ";\n";
		}
		return "\n".$border_string;
	}

	/**
	 * Generates the CSS for use in padding. This generates individual padding strings for each side, top, right, bottom and left.
	 *
	 * @param $option
	 * @return string
	 */
	function get_padding_css($option) {
		global $$option;
		$option_val = $$option;
		if (!is_array($option_val)) {
			$option_val = stripslashes($option_val);
			$edge_array = $this->build_edge_array($option_val);
			$option_val = $edge_array;
		}

		$edges = array();
		foreach ($option_val as $edge => $selections) {
			$padding = '';
			if (isset($selections['padding'])) {
				$padding .= $selections['padding'];
			}
			else {
				$padding .= 0;
			}

			if ($padding != '0' && isset($selections['padding-type'])) {
				$padding .= $selections['padding-type'];
			}
			else if ($padding != '0') {
				$padding .= "px";
			}
			$edges[$edge] = $padding;
		}

		$edge_master = array('top', 'right', 'bottom', 'left');
		$consolidated = '';
		foreach ($edge_master as $edge) {
			$consolidated .= empty($edges[$edge]) ? '0 ' : $edges[$edge].' ';
		}
		$padding_string = "\tpadding: $consolidated;\n";

		return $padding_string;
	}

	public function build_edge_array($option_val) {
		$edge_array = array();
		$edges = explode('||', $option_val);
		foreach ($edges as $edge_val) {
			if (trim($edge_val) != '') {
				$edge_options = explode('::', trim($edge_val));
				if (is_array($edge_options) && count($edge_options) > 1) {
					$val_array = array();
					$vals = explode(';', $edge_options[1]);
					foreach ($vals as $val) {
						$pair = explode('=', $val);
						if (is_array($pair) && count($pair) > 1) {
							$val_array[$pair[0]] = $pair[1];
						}
					}
					$edge_array[$edge_options[0]] = $val_array;
				}
			}
		}
		return $edge_array;
	}

	/**
	 * Adds a "Photonic" tab to the "Add Media" panel.
	 *
	 * @param $tabs
	 * @return array
	 */
	function media_upload_tabs($tabs) {
		$tabs['photonic'] = 'Photonic';
		return $tabs;
	}

	/**
	 * Invokes the form to display the photonic insertion screen in the "Add Media" panel. The call to wp_iframe ensures that the right CSS and JS are called.
	 *
	 * @return void
	 */
	function media_upload_photonic() {
		wp_iframe(array(&$this, 'media_upload_photonic_form'));
	}

	/**
	 * First prints the standard buttons for media upload, then shows the UI for Photonic.
	 *
	 * @return void
	 */
	function media_upload_photonic_form() {
		media_upload_header();
		require_once(plugin_dir_path(__FILE__)."/admin/add-gallery.php");
	}

	function edit_gallery() {
		require_once(plugin_dir_path(__FILE__)."/admin/edit-gallery-templates.php");
	}

	static function get_image_sizes_selection($element_name, $show_full = false) {
		global $_wp_additional_image_sizes;
		$image_sizes = array();
		$standard_sizes = array('thumbnail', 'medium', 'large');
		if ($show_full) {
			$standard_sizes[] = 'full';
		}
		foreach ($standard_sizes as $standard_size) {
			if ($standard_size != 'full') {
				$image_sizes[$standard_size] = array('width' => get_option($standard_size.'_size_w'), 'height' => get_option($standard_size.'_size_h'));
			}
			else {
				$image_sizes[$standard_size] = array('width' => __('Original width', 'photonic'), 'height' => __('Original height', 'photonic'));
			}
		}
		if (is_array($_wp_additional_image_sizes)) {
			$image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
		}
		$ret = "<select name='$element_name'>";
		foreach ($image_sizes as $size_name => $size_attrs) {
			$ret .= "<option value='$size_name'>$size_name ({$size_attrs['width']} &times; {$size_attrs['height']})</option>";
		}
		$ret .= '</select>';
		return $ret;
	}

	/**
	 * Make an HTTP request
	 *
	 * @static
	 * @param $url
	 * @param string $method GET | POST | DELETE
	 * @param null $post_fields
	 * @param string $user_agent
	 * @param int $timeout
	 * @param bool $ssl_verify_peer
	 * @return array|WP_Error
	 */
	static function http($url, $method = 'POST', $post_fields = NULL, $user_agent = null, $timeout = 90, $ssl_verify_peer = false, $headers = array(), $cookies = array()) {
		$curl_args = array(
			'user-agent' => $user_agent,
			'timeout' => $timeout,
			'sslverify' => $ssl_verify_peer,
			'headers' => array_merge(array('Expect:'), $headers),
			'method' => $method,
			'body' => $post_fields,
			'cookies' => $cookies,
		);

		switch ($method) {
			case 'DELETE':
				if (!empty($post_fields)) {
					$url = "{$url}?{$post_fields}";
				}
				break;
		}

		$response = wp_remote_request($url, $curl_args);
		return $response;
	}

	/**
	 * Returns the page where the OAuth API is being invoked. The invocation happens through admin-ajax.php, but we don't want
	 * the validated user to land up there. Instead we want the users to reach the page where they clicked the "Login" button.
	 *
	 * @static
	 * @return string
	 */
	static function get_callback_url() {
		global $photonic_callback_url;
		if (isset($photonic_callback_url)) {
			return $photonic_callback_url;
		}

		$page_URL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$page_URL .= "s";
		}
		$page_URL .= "://";
		if (isset($_SERVER["SERVER_PORT"])) {
			$page_URL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else if (!isset($_SERVER["SERVER_PORT"]) && $page_URL == 'http://') {
			$page_URL .= $_SERVER["SERVER_NAME"].":80".$_SERVER["REQUEST_URI"];
		}
		else if (!isset($_SERVER["SERVER_PORT"]) && $page_URL == 'https://') {
			$page_URL .= $_SERVER["SERVER_NAME"].":443".$_SERVER["REQUEST_URI"];
		}
		else {
			$page_URL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $page_URL;
	}

	/**
	 * Checks if a user has authenticated a particular provider's services. When this is invoked we don't know if the page has
	 * a Flickr / 500px / SmugMug gallery, so we just invoke it and set some global variables.
	 *
	 * @return mixed
	 */
	function check_authentication() {
		if (is_admin()) {
			return;
		}
		global $photonic_flickr_allow_oauth, $photonic_500px_allow_oauth, $photonic_smug_allow_oauth, $photonic_picasa_allow_oauth;
		if (!$photonic_flickr_allow_oauth && !$photonic_500px_allow_oauth && !$photonic_smug_allow_oauth && !$photonic_picasa_allow_oauth) {
			return;
		}

		global $photonic_flickr_oauth_done, $photonic_500px_oauth_done, $photonic_smug_oauth_done;
		$photonic_flickr_oauth_done = $photonic_500px_oauth_done = $photonic_smug_oauth_done = false;

		$cookie = Photonic::parse_cookie();
		if ($photonic_flickr_allow_oauth && isset($cookie['flickr']) && isset($cookie['flickr']['oauth_token']) && isset($cookie['flickr']['oauth_token_secret'])) {
			global $photonic_flickr_gallery;
			$this->initialize_extension('flickr');
			$current_token = array(
				'oauth_token' => $cookie['flickr']['oauth_token'],
				'oauth_token_secret' => $cookie['flickr']['oauth_token_secret'],
			);
			if (isset($_REQUEST['oauth_verifier']) && isset($_REQUEST['oauth_token'])) {
				$current_token['oauth_token'] = $_REQUEST['oauth_token'];
				$current_token['oauth_verifier'] = $_REQUEST['oauth_verifier'];
				$new_token = $photonic_flickr_gallery->get_access_token($current_token);
				if (isset($new_token['oauth_token']) && isset($new_token['oauth_token_secret'])) {
					$photonic_flickr_oauth_done = true;
					$redirect = remove_query_arg(array('oauth_token', 'oauth_verifier'));
					wp_redirect($redirect);
					exit;
				}
			}
			else if (isset($cookie['flickr']['oauth_token_type']) && $cookie['flickr']['oauth_token_type'] == 'access') {
				$access_token_response = $photonic_flickr_gallery->check_access_token($current_token);
				if (is_wp_error($access_token_response)) {
					$photonic_flickr_gallery->is_server_down = true;
				}
				$photonic_flickr_oauth_done = $photonic_flickr_gallery->is_access_token_valid($access_token_response);
			}
		}

		if ($photonic_500px_allow_oauth && isset($cookie['500px']) && isset($cookie['500px']['oauth_token']) && isset($cookie['500px']['oauth_token_secret'])) {
			global $photonic_500px_gallery;
			$this->initialize_extension('500px');
			$current_token = array(
				'oauth_token' => $cookie['500px']['oauth_token'],
				'oauth_token_secret' => $cookie['500px']['oauth_token_secret'],
			);
			if (isset($_REQUEST['oauth_verifier']) && isset($_REQUEST['oauth_token'])) {
				$current_token['oauth_token'] = $_REQUEST['oauth_token'];
				$current_token['oauth_verifier'] = $_REQUEST['oauth_verifier'];
				$new_token = $photonic_500px_gallery->get_access_token($current_token);
				if (isset($new_token['oauth_token']) && isset($new_token['oauth_token_secret'])) {
					// Strip out the token and the verifier from the callback URL and send the user to the callback URL.
					$photonic_500px_oauth_done = true;
					$redirect = remove_query_arg(array('oauth_token', 'oauth_verifier'));
					wp_redirect($redirect);
					exit;
				}
			}
			else if (isset($cookie['500px']['oauth_token_type']) && $cookie['500px']['oauth_token_type'] == 'access') {
				$access_token_response = $photonic_500px_gallery->check_access_token($current_token);
				if (is_wp_error($access_token_response)) {
					$photonic_500px_gallery->is_server_down = true;
				}
				$photonic_500px_oauth_done = $photonic_500px_gallery->is_access_token_valid($access_token_response);
			}
		}

		$this->check_authentication_smug($cookie);

		if (isset($photonic_picasa_allow_oauth)) {
			$this->initialize_extension('picasa');
		}
	}

	/**
	 * Searches for specific cookies in the user's browser. It then builds an array with the available cookies. The keys of the array
	 * are the individual providers ('flickr', 'smug' etc) and the values are arrays of key-value mappings.
	 *
	 * @static
	 * @return array
	 */
	public static function parse_cookie() {
		$cookie = array(
			'flickr' => array(),
			'500px' => array(),
			'smug' => array(),
			'picasa' => array(),
		);
		$auth_types = array(
			'flickr' => 'oauth1',
			'500px' => 'oauth1',
			'smug' => 'oauth1',
			'picasa' => 'oauth2',
		);
		$cookie_keys = array('oauth_token', 'oauth_token_secret', 'oauth_token_type', 'access_token', 'access_token_type', 'oauth_token_created', 'oauth_token_expires', 'oauth_refresh_token');
		foreach ($cookie as $provider => $cookies) {
			$orig_secret = $auth_types[$provider] == 'oauth1' ? 'photonic_'.$provider.'_api_secret' : 'photonic_'.$provider.'_client_secret';
			global $$orig_secret;
			if (isset($$orig_secret)) {
				$secret = md5($$orig_secret, false);
				foreach ($cookie_keys as $cookie_key) {
					$key = '-'.str_replace('_', '-', $cookie_key);
					if (isset($_COOKIE['photonic-'.$secret.$key])) {
						$cookie[$provider][$cookie_key] = $_COOKIE['photonic-'.$secret.$key];
					}
				}
			}
		}
		return $cookie;
	}

	/**
	 * The initiation process for the authentication. When a user clicks on this button, a request token is obtained and the authorization
	 * is performed. Then the user is redirected to an authorization site, where the user can authorize this site.
	 */
	function authenticate() {
		if (isset($_POST['provider'])) {
			$provider = sanitize_text_field($_POST['provider']);
			$callback_id = sanitize_text_field($_POST['callback_id']);
			$post_id = substr($callback_id, 19);
			global $photonic_callback_url;
			$photonic_callback_url = get_permalink($post_id);

			$this->initialize_extension($provider);
			switch ($provider) {
				case 'flickr':
					global $photonic_flickr_gallery;
					$request_token = $photonic_flickr_gallery->get_request_token();
					$authorize_url = $photonic_flickr_gallery->get_authorize_URL($request_token);
					echo $authorize_url.'&perms=read';
					die;

				case '500px':
					global $photonic_500px_gallery;
					$request_token = $photonic_500px_gallery->get_request_token();
					$authorize_url = $photonic_500px_gallery->get_authorize_URL($request_token);
					echo $authorize_url;
					die;

				case 'smug':
					global $photonic_smugmug_gallery;
					$request_token = $photonic_smugmug_gallery->get_request_token();
					$authorize_url = $photonic_smugmug_gallery->get_authorize_URL($request_token);
					echo $authorize_url.'&Access=Full&Permissions=Read';
					die;
			}
		}
	}

	public function get_oauth2_access_token() {
		$parameters = Photonic_Processor::parse_parameters($_SERVER['QUERY_STRING']);
		global $photonic_picasa_client_secret;
		if ((isset($parameters['code']) || isset($parameters['token']) && isset($parameters['state']))) {
			$state_args = explode('::', $parameters['state']);
			if ($state_args[0] == md5($photonic_picasa_client_secret.'picasa')) { // Picasa response
				global $photonic_picasa_gallery;
				$this->initialize_extension('picasa');
				$photonic_picasa_gallery->get_access_token($parameters);
			}
		}
	}

	function obtain_token() {
		global $photonic_picasa_gallery;
		$provider = sanitize_text_field($_POST['provider']);
		if ($provider == 'picasa') {
			$code = esc_attr($_POST['code']);
			$this->initialize_extension('picasa');
			$response = Photonic::http($photonic_picasa_gallery->access_token_URL(), 'POST', array(
				'code' => $code,
				'grant_type' => 'authorization_code',
				'client_id' => $photonic_picasa_gallery->client_id,
				'client_secret' => $photonic_picasa_gallery->client_secret,
				'redirect_uri' => admin_url('admin.php?page=photonic-helpers'),
			));

			if (!is_wp_error($response) && is_array($response)) {
				echo($response['body']);
			}
			else {
			}
		}
		die();
	}

	function invoke_helper() {
		global $photonic_options_manager;
		require_once(plugin_dir_path(__FILE__)."/photonic-options-manager.php");
		$photonic_options_manager = new Photonic_Options_Manager(__FILE__, $this);
		$photonic_options_manager->init();
		$photonic_options_manager->invoke_helper();
	}

	/**
	 * @return array
	 */
	public function get_localized_js_variables() {
		global $photonic_fbox_title_position, $photonic_slideshow_library, $photonic_custom_lightbox,
			   $photonic_tile_spacing, $photonic_tile_min_height, $photonic_slideshow_mode, $photonic_slideshow_interval, $photonic_pphoto_theme,
			   $photonic_enable_swipebox_mobile_bars, $photonic_lightbox_for_all, $photonic_popup_panel_width, $photonic_deep_linking, $photonic_social_media,
			   $photonic_slideshow_prevent_autostart, $photonic_wp_slide_adjustment, $photonic_masonry_min_width, $photonic_mosaic_trigger_width;

		global $photonic_js_variables;

		$slideshow_library = $photonic_slideshow_library == 'custom' ? $photonic_custom_lightbox : $photonic_slideshow_library;
		$js_array = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'plugin_url' => PHOTONIC_URL,
			'fbox_show_title' => $photonic_fbox_title_position == 'none' ? false : true,
			'fbox_title_position' => $photonic_fbox_title_position == 'none' ? 'outside' : $photonic_fbox_title_position,

			'slide_adjustment' => empty($photonic_wp_slide_adjustment) ? 'adapt-height-width' : $photonic_wp_slide_adjustment,

			'deep_linking' => isset($photonic_deep_linking) ? $photonic_deep_linking : 'none',
			'social_media' => isset($photonic_deep_linking) ? $photonic_deep_linking != 'none' && empty($photonic_social_media) : '',

			'slideshow_library' => $slideshow_library,
			'tile_spacing' => (empty($photonic_tile_spacing) || !Photonic::check_integer($photonic_tile_spacing)) ? 0 : $photonic_tile_spacing,
			'tile_min_height' => (empty($photonic_tile_min_height) || !Photonic::check_integer($photonic_tile_min_height)) ? 200 : $photonic_tile_min_height,
			'masonry_min_width' => (empty($photonic_masonry_min_width) || !Photonic::check_integer($photonic_masonry_min_width)) ? 200 : (intval($photonic_masonry_min_width) > 0 ? $photonic_masonry_min_width : 200),
			'mosaic_trigger_width' => (empty($photonic_mosaic_trigger_width) || !Photonic::check_integer($photonic_mosaic_trigger_width)) ? 200 : (intval($photonic_mosaic_trigger_width) > 0 ? $photonic_mosaic_trigger_width : 200),

			'slideshow_mode' => (isset($photonic_slideshow_mode) && $photonic_slideshow_mode == 'on') ? true : false,
			'slideshow_interval' => (isset($photonic_slideshow_interval) && Photonic::check_integer($photonic_slideshow_interval)) ? $photonic_slideshow_interval : 5000,
			'pphoto_theme' => isset($photonic_pphoto_theme) ? $photonic_pphoto_theme : 'pp_default',
			'gallery_panel_width' => (empty($photonic_popup_panel_width) || !Photonic::check_integer($photonic_popup_panel_width) || $photonic_popup_panel_width > 100) ? 80 : $photonic_popup_panel_width,
			'enable_swipebox_mobile_bars' => !empty($photonic_enable_swipebox_mobile_bars),
			'lightbox_for_all' => !empty($photonic_lightbox_for_all),

			'slideshow_autostart' => !(isset($photonic_slideshow_prevent_autostart) && $photonic_slideshow_prevent_autostart == 'on'),

			'password_failed' => esc_attr__('This album is password-protected. Please provide a valid password.', 'photonic'),
			'incorrect_password' => esc_attr__('Incorrect password.', 'photonic'),
			'maximize_panel' => esc_attr__('Show', 'photonic'),
			'minimize_panel' => esc_attr__('Hide', 'photonic'),
		);
		$photonic_js_variables = $js_array;
		return $js_array;
	}

	function enable_translations() {
		load_plugin_textdomain('photonic', FALSE, FALSE);
	}

	/**
	 * @param $cookie
	 * @return void
	 * @internal param $photonic_smug_allow_oauth
	 * @internal param $photonic_smugmug_gallery
	 * @internal param $photonic_smug_oauth_done
	 */
	public function check_authentication_smug($cookie = null) {
		if ($cookie == null) {
			$cookie = Photonic::parse_cookie();
		}

		global $photonic_smugmug_gallery, $photonic_smug_allow_oauth, $photonic_smug_oauth_done;
		if ($photonic_smug_allow_oauth && isset($cookie['smug']) && isset($cookie['smug']['oauth_token']) && isset($cookie['smug']['oauth_token_secret'])) {
			$this->initialize_extension('smugmug');
			$current_token = array(
				'oauth_token' => $cookie['smug']['oauth_token'],
				'oauth_token_secret' => $cookie['smug']['oauth_token_secret']
			);

			if (!$photonic_smug_oauth_done &&
				((isset($cookie['smug']['oauth_token_type']) && $cookie['smug']['oauth_token_type'] == 'request') || !isset($cookie['smug']['oauth_token_type']))) {
				$current_token['oauth_verifier'] = $_REQUEST['oauth_verifier'];
				$new_token = $photonic_smugmug_gallery->get_access_token($current_token);
				if (isset($new_token['oauth_token']) && isset($new_token['oauth_token_secret'])) {
					$access_token_response = $photonic_smugmug_gallery->check_access_token($new_token);
					if (is_wp_error($access_token_response)) {
						$photonic_smugmug_gallery->is_server_down = true;
					}
					$photonic_smug_oauth_done = $photonic_smugmug_gallery->is_access_token_valid($access_token_response);
				}
			}
			else if (isset($cookie['smug']['oauth_token_type']) && $cookie['smug']['oauth_token_type'] == 'access') {
				$access_token_response = $photonic_smugmug_gallery->check_access_token($current_token);
				if (is_wp_error($access_token_response)) {
					$photonic_smugmug_gallery->is_server_down = true;
				}
				$photonic_smug_oauth_done = $photonic_smugmug_gallery->is_access_token_valid($access_token_response);
			}
		}
	}

	function load_more() {
		$provider = esc_attr($_POST['provider']);
		$query = sanitize_text_field($_POST['query']);
		$attr = wp_parse_args($query);

		$gallery = $this->initialize_extension($provider);
		if ($provider == 'flickr') {
			$attr['page'] = isset($attr['page']) ? $attr['page'] + 1 : 0;
			echo $gallery->get_gallery_images($attr);
		}
		else if ($provider == 'picasa') {
			$attr['start_index'] = $attr['start_index'] + $attr['max_results'];
			echo $gallery->get_gallery_images($attr);
		}
		else if ($provider == 'smug') {
			$attr['start'] = $attr['start'] + $attr['count'];
			echo $gallery->get_gallery_images($attr);
		}
		else if ($provider == 'zenfolio') {
			$attr['offset'] = $attr['offset'] + $attr['limit'];
			echo $gallery->get_gallery_images($attr);
		}
		else if ($provider == '500px') {
			$attr['page'] = $attr['page'] + 1;
			echo $gallery->get_gallery_images($attr);
		}
		else if ($provider == 'wp') {
			$attr['page'] = $attr['page'] + 1;
			echo $gallery->get_gallery_images($attr);
		}
		die();
	}

	static function title_caption_options() {
		$ret = array(
			'none' => __('No title / caption / description', 'photonic'),
			'title' => __('Always use the photo title, even if blank', 'photonic'),
			'desc' => __('Always use the photo description / caption, even if blank', 'photonic'),
			'desc-title' => __('Use the photo description / caption. If blank use the title', 'photonic'),
			'title-desc' => __('Use the photo title. If blank use the description / caption', 'photonic'),
		);
		return $ret;
	}

	static function layout_options($show_blank = false) {
		$ret = array();
		if ($show_blank) {
			$ret[''] = '';
		}
		return array_merge($ret, array(
			'strip-below' => __('Thumbnail strip below slideshow', 'photonic'),
			'strip-above' => __('Thumbnail strip above slideshow', 'photonic'),
			'strip-right' => __('Thumbnail strip to the right of the slideshow', 'photonic'),
			'no-strip' => __('Slideshow without thumbnails', 'photonic'),
			'square' => __('Square thumbnail grid, lightbox', 'photonic'),
			'circle' => __('Circular thumbnail grid, lightbox', 'photonic'),
			'random' => __('Random justified gallery, lightbox', 'photonic'),
			'masonry' => __('Masonry layout, lightbox', 'photonic'),
			'mosaic' => __('Mosaic layout, lightbox', 'photonic'),
		));
	}

	function admin_head() {
		// check user permissions
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
			return;
		}

		global $photonic_disable_editor;
		// check if WYSIWYG is enabled
		if ('true' == get_user_option('rich_editing') && empty($photonic_disable_editor)) {
			add_filter('mce_external_plugins', array($this ,'mce_photonic'), 5);
		}
	}

	function mce_photonic($plugin_array) {
		$plugin_array['photonic'] = plugins_url('include/scripts/mce.js' , __FILE__);
		return $plugin_array;
	}

	function body_class($classes = array(), $class = '') {
		if (!is_array($classes)) {
			$classes = explode(' ', $classes);
		}
		global $photonic_is_IE;
		if ($photonic_is_IE) {
			$classes[] = 'photonic-ie';
		}

		return $classes;
	}
}

add_action('init', 'photonic_init');

function photonic_init() {
	global $photonic;
	$photonic = new Photonic();
}
