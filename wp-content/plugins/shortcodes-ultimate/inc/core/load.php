<?php
class Shortcodes_Ultimate {

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'plugins_loaded',             array( __CLASS__, 'init' ) );
		add_action( 'init',                       array( __CLASS__, 'register' ) );
		add_action( 'init',                       array( __CLASS__, 'update' ), 20 );
		register_activation_hook( SU_PLUGIN_FILE, array( __CLASS__, 'activation' ) );
		register_activation_hook( SU_PLUGIN_FILE, array( __CLASS__, 'deactivation' ) );
	}

	/**
	 * Plugin init
	 */
	public static function init() {
		// Make plugin available for translation
		load_plugin_textdomain( 'shortcodes-ultimate', false, dirname( plugin_basename( SU_PLUGIN_FILE ) ) . '/languages/' );
		// Setup admin class
		$admin = new Sunrise4( array(
				'file'       => SU_PLUGIN_FILE,
				'slug'       => 'su',
				'prefix'     => 'su_option_',
				'textdomain' => 'su'
			) );
		// Top-level menu
		$admin->add_menu( array(
				'page_title'  => __( 'Settings', 'shortcodes-ultimate' ) . ' &lsaquo; ' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ),
				'menu_title'  => apply_filters( 'su/menu/shortcodes', __( 'Shortcodes', 'shortcodes-ultimate' ) ),
				'capability'  => 'manage_options',
				'slug'        => 'shortcodes-ultimate',
				'icon_url'    => 'dashicons-editor-code',
				'position'    => '80.11',
				'options'     => array(
					array(
						'type' => 'opentab',
						'name' => __( 'About', 'shortcodes-ultimate' )
					),
					array(
						'type'     => 'about',
						'callback' => array( 'Su_Admin_Views', 'about' )
					),
					array(
						'type'    => 'closetab',
						'actions' => false
					),
					array(
						'type' => 'opentab',
						'name' => __( 'Settings', 'shortcodes-ultimate' )
					),
					array(
						'type'    => 'checkbox',
						'id'      => 'custom-formatting',
						'name'    => __( 'Custom formatting', 'shortcodes-ultimate' ),
						'desc'    => __( 'Disable this option if you have some problems with other plugins or content formatting', 'shortcodes-ultimate' ) . '<br /><a href="http://gndev.info/kb/custom-formatting/" target="_blank">' . __( 'Documentation article', 'shortcodes-ultimate' ) . '</a>',
						'default' => 'on',
						'label'   => __( 'Enabled', 'shortcodes-ultimate' )
					),
					array(
						'type'    => 'checkbox',
						'id'      => 'skip',
						'name'    => __( 'Skip default values', 'shortcodes-ultimate' ),
						'desc'    => __( 'Enable this option and the generator will insert a shortcode without default attribute values that you have not changed. As a result, the generated code will be shorter.', 'shortcodes-ultimate' ),
						'default' => 'on',
						'label'   => __( 'Enabled', 'shortcodes-ultimate' )
					),
					array(
						'type'    => 'text',
						'id'      => 'prefix',
						'name'    => __( 'Shortcodes prefix', 'shortcodes-ultimate' ),
						'desc'    => sprintf( __( 'This prefix will be added to all shortcodes by this plugin. For example, type here %s and you\'ll get shortcodes like %s and %s. Please keep in mind: this option is not affects your already inserted shortcodes and if you\'ll change this value your old shortcodes will be broken', 'shortcodes-ultimate' ), '<code>su_</code>', '<code>[su_button]</code>', '<code>[su_column]</code>' ),
						'default' => 'su_'
					),
					array(
						'type'    => 'text',
						'id'      => 'hotkey',
						'name'    => __( 'Insert shortcode Hotkey', 'shortcodes-ultimate' ),
						'desc'    => sprintf( '%s<br><a href="https://rawgit.com/jeresig/jquery.hotkeys/master/test-static-01.html" target="_blank">%s</a> | <a href="https://github.com/jeresig/jquery.hotkeys#notes" target="_blank">%s</a>', __( 'Here you can define custom hotkey for the Insert shortcode popup window. Leave this field empty to disable hotkey', 'shortcodes-ultimate' ), __( 'Hotkey examples', 'shortcodes-ultimate' ), __( 'Additional notes', 'shortcodes-ultimate' ) ),
						'default' => 'alt+i'
					),
					array(
						'type'    => 'hidden',
						'id'      => 'skin',
						'name'    => __( 'Skin', 'shortcodes-ultimate' ),
						'desc'    => __( 'Choose global skin for shortcodes', 'shortcodes-ultimate' ),
						'default' => 'default'
					),
					array(
						'type' => 'closetab'
					),
					array(
						'type' => 'opentab',
						'name' => __( 'Custom CSS', 'shortcodes-ultimate' )
					),
					array(
						'type'     => 'custom_css',
						'id'       => 'custom-css',
						'default'  => '',
						'callback' => array( 'Su_Admin_Views', 'custom_css' )
					),
					array(
						'type' => 'closetab'
					)
				)
			) );
		// Settings submenu
		$admin->add_submenu( array(
				'parent_slug' => 'shortcodes-ultimate',
				'page_title'  => __( 'Settings', 'shortcodes-ultimate' ) . ' &lsaquo; ' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ),
				'menu_title'  => apply_filters( 'su/menu/settings', __( 'Settings', 'shortcodes-ultimate' ) ),
				'capability'  => 'manage_options',
				'slug'        => 'shortcodes-ultimate',
				'options'     => array()
			) );
		// Examples submenu
		$admin->add_submenu( array(
				'parent_slug' => 'shortcodes-ultimate',
				'page_title'  => __( 'Examples', 'shortcodes-ultimate' ) . ' &lsaquo; ' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ),
				'menu_title'  => apply_filters( 'su/menu/examples', __( 'Examples', 'shortcodes-ultimate' ) ),
				'capability'  => 'edit_others_posts',
				'slug'        => 'shortcodes-ultimate-examples',
				'options'     => array(
					array(
						'type' => 'examples',
						'callback' => array( 'Su_Admin_Views', 'examples' )
					)
				)
			) );
		// Cheatsheet submenu
		$admin->add_submenu( array(
				'parent_slug' => 'shortcodes-ultimate',
				'page_title'  => __( 'Cheatsheet', 'shortcodes-ultimate' ) . ' &lsaquo; ' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ),
				'menu_title'  => apply_filters( 'su/menu/examples', __( 'Cheatsheet', 'shortcodes-ultimate' ) ),
				'capability'  => 'edit_others_posts',
				'slug'        => 'shortcodes-ultimate-cheatsheet',
				'options'     => array(
					array(
						'type' => 'cheatsheet',
						'callback' => array( 'Su_Admin_Views', 'cheatsheet' )
					)
				)
			) );
		// Add-ons submenu
		$admin->add_submenu( array(
				'parent_slug' => 'shortcodes-ultimate',
				'page_title'  => __( 'Add-ons', 'shortcodes-ultimate' ) . ' &lsaquo; ' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ),
				'menu_title'  => apply_filters( 'su/menu/addons', __( 'Add-ons', 'shortcodes-ultimate' ) ),
				'capability'  => 'edit_others_posts',
				'slug'        => 'shortcodes-ultimate-addons',
				'options'     => array(
					array(
						'type' => 'addons',
						'callback' => array( 'Su_Admin_Views', 'addons' )
					)
				)
			) );
		// Translate plugin meta
		__( 'Shortcodes Ultimate', 'shortcodes-ultimate' );
		__( 'Vladimir Anokhin', 'shortcodes-ultimate' );
		__( 'Supercharge your WordPress theme with mega pack of shortcodes', 'shortcodes-ultimate' );
		// Add plugin actions links
		add_filter( 'plugin_action_links_' . plugin_basename( SU_PLUGIN_FILE ), array( __CLASS__, 'actions_links' ), -10 );
		// Add plugin meta links
		add_filter( 'plugin_row_meta', array( __CLASS__, 'meta_links' ), 10, 2 );
		// Shortcodes Ultimate is ready
		do_action( 'su/init' );
	}

	/**
	 * Plugin activation
	 */
	public static function activation() {
		self::timestamp();
		update_option( 'su_option_version', SU_PLUGIN_VERSION );
		do_action( 'su/activation' );
	}

	/**
	 * Plugin deactivation
	 */
	public static function deactivation() {
		do_action( 'su/deactivation' );
	}

	/**
	 * Plugin update hook
	 */
	public static function update() {
		$option = get_option( 'su_option_version' );
		if ( $option !== SU_PLUGIN_VERSION ) {
			update_option( 'su_option_version', SU_PLUGIN_VERSION );
			do_action( 'su/update' );
		}
	}

	/**
	 * Register shortcodes
	 */
	public static function register() {
		// Prepare compatibility mode prefix
		$prefix = su_cmpt();
		// Loop through shortcodes
		foreach ( ( array ) Su_Data::shortcodes() as $id => $data ) {
			if ( isset( $data['function'] ) && is_callable( $data['function'] ) ) $func = $data['function'];
			elseif ( is_callable( array( 'Su_Shortcodes', $id ) ) ) $func = array( 'Su_Shortcodes', $id );
			elseif ( is_callable( array( 'Su_Shortcodes', 'su_' . $id ) ) ) $func = array( 'Su_Shortcodes', 'su_' . $id );
			else continue;
			// Register shortcode
			add_shortcode( $prefix . $id, $func );
		}
		// Register [media] manually // 3.x
		add_shortcode( $prefix . 'media', array( 'Su_Shortcodes', 'media' ) );
	}

	/**
	 * Add timestamp
	 */
	public static function timestamp() {
		if ( !get_option( 'su_installed' ) ) update_option( 'su_installed', time() );
	}

	/**
	 * Add plugin actions links
	 */
	public static function actions_links( $links ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=shortcodes-ultimate-examples' ) . '">' . __( 'Examples', 'shortcodes-ultimate' ) . '</a>';
		$links[] = '<a href="' . admin_url( 'admin.php?page=shortcodes-ultimate' ) . '#tab-0">' . __( 'Where to start?', 'shortcodes-ultimate' ) . '</a>';
		return $links;
	}

	/**
	 * Add plugin meta links
	 */
	public static function meta_links( $links, $file ) {
		// Check plugin
		if ( $file === plugin_basename( SU_PLUGIN_FILE ) ) {
			unset( $links[2] );
			$links[] = '<a href="http://gndev.info/shortcodes-ultimate/" target="_blank">' . __( 'Project homepage', 'shortcodes-ultimate' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/support/plugin/shortcodes-ultimate/" target="_blank">' . __( 'Support forum', 'shortcodes-ultimate' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/extend/plugins/shortcodes-ultimate/changelog/" target="_blank">' . __( 'Changelog', 'shortcodes-ultimate' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Register plugin function to perform checks that plugin is installed
 */
function shortcodes_ultimate() {
	return true;
}

new Shortcodes_Ultimate;
