<?php
// var_dump(FASTWP_STYLE_SWITCH_PATH);


// Set up the content width value based on the theme's design and stylesheet.
if ( ! isset( $content_width ) )
	$content_width = 980;
	

function fastwp_setup(){
    load_theme_textdomain	('fastwp', get_template_directory() . '/locale');
	add_theme_support		('post-formats', array('quote', 'video', 'image', 'audio','gallery'));
	add_image_size			('portfolio-thumb', 370, 241, true ); // Portfolio 370x241 image (cropped if larger)
	/* Not used */
	$args = array();
	add_theme_support( 'custom-header', $args );
	add_theme_support( 'custom-background', $args );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'primary', __( 'Primary Menu', 'wisten' ) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop
	
	
	
	global $smof_data;
	$smof_data = apply_filters('fastwp_alter_settings', $smof_data);
}
add_action('after_setup_theme', 'fastwp_setup', 11);	
		
/**
 * Enqueue scripts and styles for front-end.
 *
 * @since Wisten 1.0
 *
 * @return void
 */
function fastwp_scripts_styles() {
	global $wp_styles, $smof_data;

	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
	
	$scripts = array(
	'bootstrap',
	'jquery.appear',
	'waypoints.min',
	'jquery.prettyPhoto',
	'modernizr-latest',
	'jquery.parallax-1.1.3',
	'jquery.easing.1.3',
	'jquery.superslides',
	'jquery.flexslider',
	'jquery.sticky',
	'owl.carousel',
	'jquery.isotope',
	'jquery.mb.YTPlayer',
	'magnific.popup',
	'rainyday.min',
	'plugins',
	);

	$user_agent_str = $_SERVER['HTTP_USER_AGENT'];

	if(preg_match("/like\sGecko\)\sChrome\//", $user_agent_str) && !strstr($user_agent_str, 'Iron')){
	   $scripts[] = 'SmoothScroll';
	}

	if(isset($smof_data['combine_js']) && $smof_data['combine_js'] == '1'){
		wp_enqueue_script( 'fastwp-combined-js', get_template_directory_uri() . '/fastwp/do.combine.php?scripts='.implode(',', $scripts), array('jquery'), '1.0', true );
	}else {
		foreach($scripts as $script){
			wp_enqueue_script( $script, get_template_directory_uri() . '/js/'.$script.'.js', array(), '1.0', true );
		}
	}
	
	// Loads our stylesheets.
	$styles = array(
	'reset',
	'animate.min',
	'bootstrap',
	'layout',
	'flexslider',
	'font-awesome',
	'owl.carousel',
	'settings',
	'prettyPhoto',
	'magnific-popup',
	'YTPlayer',
	'responsive',
	'palette',
	);
	
	if(isset($smof_data['combine_css']) && $smof_data['combine_css'] == '1'){
		wp_enqueue_style( 'fastwp-combined-css', get_template_directory_uri() . '/fastwp/do.combine.php?styles='.implode(',', $styles));
	}else {
		foreach($styles as $style){
			wp_enqueue_style( $style, get_template_directory_uri().'/css/'.$style.'.css');
		}
	}
	
	$custom_fonts_file = dirname(__FILE__).'/cache/custom-fonts.css';
	if(file_exists($custom_fonts_file)){
		wp_enqueue_style( 'fastwp-custom-css', get_template_directory_uri().'/cache/custom-fonts.css');
	}
	
	
$color 			= (isset($smof_data['color_secondary']) && !empty($smof_data['color_secondary']))? $smof_data['color_secondary'] : '#E70000';
$grad_start 	= (isset($smof_data['color_secondary_top']) && !empty($smof_data['color_secondary_top']))? $smof_data['color_secondary_top'] :'#e02424';
$grad_stop 		= (isset($smof_data['color_secondary_bottom']) && !empty($smof_data['color_secondary_bottom']))? $smof_data['color_secondary_bottom'] :'#be1919';
$custom_color 	= "
span.red, .nav-menu a:hover, .nav-menu a.active, .main-nav > li.active, .revslide span, .mail-message p.mail-head, .post-texts .tag:hover, .subs .text h1 span, .main-nav > li.active > a, .color, .comment .tools > a:hover { color:$color; }	
.progress-bar, .post-img .zoom-button:hover, .widget_calendar td a:hover, .work-img .button:hover, .subscribe-btn:hover, .form-btn:hover, .package.active .circle, .timeline .note:hover:after, a.p-btn:hover, .wpcf7-submit:hover, .flex-control-paging li a:hover, .flex-control-paging li a.flex-active, ins, mark { background:$color; }  
.form:focus, .package.active .circle:after, .package.active .circle, .timeline .note:hover:after, a.p-btn:hover , .wpcf7 input[type=\"text\"]:focus,.wpcf7 input[type=\"email\"]:focus,.wpcf7 textarea:focus{ 	border-color:$color; } 
.nav-tabs li.active a, .nav-tabs li.active a:hover{ border-top-color:$color; }	  
.owl-item .item:hover { border-bottom-color:$color; }
#submit:hover,
.btn-post:hover{
	background:$grad_start; /* Old browsers */
	background: -moz-linear-gradient(top, $grad_start 0%, $grad_stop 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,$grad_start), color-stop(100%,$grad_stop));
	background: -webkit-linear-gradient(top, $grad_start 0%,$grad_stop 100%); 
	background: -o-linear-gradient(top, $grad_start 0%,$grad_stop 100%);
	background: -ms-linear-gradient(top, $grad_start 0%,$grad_stop 100%);
	background: linear-gradient(to bottom, $grad_start 0%,$grad_stop 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$grad_start', endColorstr='$grad_stop',GradientType=0 );
}
.twitter-image a:hover,
.twitter-white a:hover { color:$color ;}
";

wp_enqueue_style(
        'custom_css',
        get_template_directory_uri() . '/css/custom.css'
    );
wp_enqueue_style(
        'media_query_and_custom_css',
        get_template_directory_uri() . '/css/custom.css'
    );
wp_add_inline_style( 'custom_css', $custom_color );

if(isset($smof_data['menu_breakpoint']) && !empty($smof_data['menu_breakpoint'])){

	wp_add_inline_style( 'media_query_and_custom_css', '@media only screen and (max-width: '.$smof_data['menu_breakpoint'].'px){ .nav-menu{display:none;}.mobile-drop{display:block;}}');
	
}
if(isset($smof_data['custom_css']) && !empty($smof_data['custom_css'])){
	wp_add_inline_style( 'media_query_and_custom_css', $smof_data['custom_css']);
}





	
	
}
add_action( 'wp_enqueue_scripts', 'fastwp_scripts_styles' );

function fastwp_admin_scripts_styles(){
	// wp_enqueue_style( 'dashicons' );	
	wp_enqueue_script( 'jquery');
	wp_enqueue_script( 'jquery-ui-core');
	wp_enqueue_script( 'jquery-ui-slider');
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'fastwp-admin-ui', get_template_directory_uri().'/css/jquery-ui-1.10.4.custom.css');
	wp_enqueue_style( 'fastwp-admin', get_template_directory_uri().'/css/fastwp-admin.css');
}
add_action( 'admin_enqueue_scripts', 'fastwp_admin_scripts_styles' );


function fastwp_content_nav($temp){
	
}
/**
 * Filter the page title.
 *
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since Wisten 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function fastwp_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'wisten' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'fastwp_wp_title', 10, 2 );

/**
 * Filter the page menu arguments.
 *
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since Wisten 1.0
 */
function fastwp_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'fastwp_page_menu_args' );

/**
 * Register sidebars.
 *
 * Registers our main widget area and the front page widget areas.
 *
 * @since Wisten 1.0
 */
function fastwp_widgets_init() {
	register_sidebar( array(
		'name' 			=> __( 'Default Sidebar', 'wisten' ),
		'id' 			=> 'sidebar-1',
		'description' 	=> __( 'Appears on blog pages', 'wisten' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' 	=> '</aside>',
		'before_title' 	=> '<h3 class="widget-title">',
		'after_title' 	=> '</h3>',
	));
	for($i=2;$i<=5;$i++){
		register_sidebar( array(
			'name' 			=> __( 'Sidebar '.$i, 'wisten' ),
			'id' 			=> 'sidebar-'.$i,
			'description' 	=> __( 'Appears on blog pages. Those are selectable on admin panel', 'wisten' ),
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' 	=> '</aside>',
			'before_title' 	=> '<h3 class="widget-title">',
			'after_title' 	=> '</h3>',
		));
	}
	
}
add_action( 'widgets_init', 'fastwp_widgets_init' );

function fastwp_excerpt_length( $length ) {
	return 100;
}
add_filter( 'excerpt_length', 'fastwp_excerpt_length', 999 );

function fastwp_excerpt_more( $more ) {
	return ' ...';
}
add_filter( 'excerpt_more', 'fastwp_excerpt_more' );

if ( ! function_exists( 'fastwp_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own fastwp_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Wisten 1.0
 *
 * @return void
 */
function fastwp_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'wisten' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'wisten' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<section class="avatar"><?php echo get_avatar( $comment, 85); ?></section>
			<header class="comment-meta comment-author vcard bsbb">
				<?php
					
					printf( '<cite><b class="fn">%1$s</b> %2$s</cite>',
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ?  __( 'Author', 'wisten' ) : '',
						get_comment_author_link()
						
					);
					printf( ' <a href="%1$s" class="posted-date"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'wisten' ), get_comment_date(), get_comment_time() )
					);
				?>
	
			</header><!-- .comment-meta -->
			<section class="comment-content comment bsbb">
				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'wisten' ); ?></p>
				<?php endif; ?>
				<?php comment_text(); ?>
				
			</section><!-- .comment-content -->
			<div class="tools">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => '<i class="fa fa-reply"></i>' . __( 'Reply', 'wisten' ), 'before'=>'', 'after' => '', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				<?php edit_comment_link(  '<i class="fa fa-edit"></i>' . __( 'Edit', 'wisten' ), '', '' ); ?>
			</div>
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if ( ! function_exists( 'fastwp_entry_meta' ) ) :
/**
 * Set up post entry meta.
 *
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own fastwp_entry_meta() to override in a child theme.
 *
 * @since Wisten 1.0
 *
 * @return void
 */
function fastwp_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'wisten' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'wisten' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'wisten' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'wisten' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'wisten' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'wisten' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Extends the default WordPress body clas.
 *
 * @since Wisten 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function fastwp_body_class( $classes ) {
	/* For future development*/
	return $classes;
}
add_filter( 'body_class', 'fastwp_body_class' );


/**
 * Register postMessage support.
 *
 * Add postMessage support for site title and description for the Customizer.
 *
 * @since Wisten 1.0
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 * @return void
 */
function fastwp_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
}
add_action( 'customize_register', 'fastwp_customize_register' );

/**
 * Enqueue Javascript postMessage handlers for the Customizer.
 *
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Wisten 1.0
 *
 * @return void
 */
function fastwp_customize_preview_js() {
	wp_enqueue_script( 'wisten-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20140418', true );
}
add_action( 'customize_preview_init', 'fastwp_customize_preview_js' );

require_once('admin/index.php');
require_once('fastwp/metabox.functions.php');
require_once('fastwp/custom.posts.php');
require_once('fastwp/shortcodes.functions.php');
require_once('fastwp/user.interface.php');
require_once('fastwp/widgets.functions.php');
require_once('fastwp/admin.functions.php');


/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname( __FILE__ ) . '/fastwp/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'my_theme_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function my_theme_register_required_plugins() {

    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(

        // This is an example of how to include a plugin pre-packaged with a theme.
        array(
            'name'               => 'FastWP Shortcodes for Wisten', // The plugin name.
            'slug'               => 'fastwp-shortcodes-v1.1', // The plugin slug (typically the folder name).
            'source'             => get_stylesheet_directory() . '/fastwp/plugins/fastwp-shortcodes-v1.1.zip', // The plugin source.
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
            'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url'       => '', // If set, overrides default API URL and points to an external URL.
        ),
        array(
            'name'               => 'FastWP Wordpress importer', // The plugin name.
            'slug'               => 'fastwp-wordpress-importer', // The plugin slug (typically the folder name).
            'source'             => get_stylesheet_directory() . '/fastwp/plugins/fastwp-wordpress-importer.zip', // The plugin source.
            'required'           => false, // If false, the plugin is only 'recommended' instead of required.
            'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher.
            'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url'       => '', // If set, overrides default API URL and points to an external URL.
        ),

        array(
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => true,
            'force_activation'   => true,
        ),

    );

    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'Wisten requires the following plugin: %1$s.', 'Wisten requires the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'Wisten recommends the following plugin: %1$s.', 'Wisten recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );

}

/*
// Disabled because of tgn plugin activation 
function _no_shortcodes_warning(){ 
	$defined = get_defined_constants();
	if(@$defined['FASTWP_SHORTCODE'] != true):
		echo sprintf('<div class="message error"><p>%s</p></div>','Wisten is enabled, but not fully functional. It requires plugin "FastWP Shortcodes" enabled.');
	endif;
}
add_action('admin_notices', '_no_shortcodes_warning');
*/

function m_explode(array $array,$key = ''){     
        if( !is_array($array) or $key == '')
            return;        
        $output = array();
        foreach( $array as $v ){        
            if( !is_object($v) ){
                return;
            }
            $output[] = $v->$key;
        }
        return $output;
}



if(!isset($fastwp_social_networks)){
	$fastwp_social_networks = array(
		'contact' 	=> 'envelope',
		// 'bitbucket' 	=> 'bitbucket',
		'pinterest' 	=> 'pinterest',
		'dribbble' 	=> 'dribbble',
		'github' 	=> 'github',
		'google_plus' 	=> 'google-plus',
		'instagram' 	=> 'instagram',
		'linkedin' 	=> 'linkedin',
		'skype' 	=> 'skype',
		// 'stack_exchange' 	=> 'stack-exchange',
		'tumblr' 	=> 'tumblr',
		'vimeo' 	=> 'vimeo-square',
		// 'weibo' 	=> 'weibo',
		'facebook' 	=> 'facebook',
		'twitter'	=> 'twitter',
		'youtube'	=> 'youtube',
		'rss'		=> 'rss'
	);
}


if(!isset($fastwp_share_networks)){
	$fastwp_share_networks = array(
		'twitter'	=> 'twitter',
		'facebook' 	=> 'facebook',
		'pinterest' => 'pinterest',
		'google' 	=> 'google-plus',
		// 'delicious' => 'delicious',
		'linkedin' 	=> 'linkedin',
	);
}



if ( ! function_exists( 'fastwp_is_multipage' ) ) :
	function fastwp_is_multipage(){
		echo get_template_name();
	}	

endif;

if ( ! function_exists( 'fastwp_page_list' ) ) :
	function fastwp_page_list(){
		$all_pages = get_pages();
		$return = array();
		foreach($all_pages as $page){
			$return[$page->ID] = $page->post_title;
		}
		return $return;
	}	

endif;






add_filter('fastwp_separator', 'fastwp_separator_process',10,2);
function fastwp_separator_process($content, $id){
	$value = get_post_meta( $id, '_attach_section', true );
	if(!isset($value['p_image']) || $value['p_image'] == '') return $content;
	$overlay_style = $parallax_style = '';
	$parallax_style = 'background:url('.@$value['p_image'].');';
	$overlay_style .= (isset($value['color']) && !empty($value['color']))? 'background-color:'.$value['color'].';':'';
	$overlay_style .= (isset($value['opacity']) && !empty($value['opacity']))? 'opacity:'.($value['opacity']/100).';':'';
	if(isset($value['opacity']) && $value['opacity'] == '0') { $overlay_style = '';}
	return sprintf('<div class="fastwp-parallax-bg" data-bg="%s" data-speed="%s" style="%s"><div class="relative"><div class="overlay" style="%s"></div></div><div class="inner-content">%s</div></div>', @$value['p_image'], @$value['speed'], $parallax_style, $overlay_style, $content);
}
$add_id_to = array(
	'fwp_portfolio',
	'fwp_team',
	'fwp_service',
	'fwp_testimonial',
	'page',
);
foreach($add_id_to as $post_type){
	add_filter( 'manage_edit-'.$post_type.'_columns', 'set_custom_edit_ITEM_columns' );
	add_action( 'manage_'.$post_type.'_posts_custom_column' , 'custom_ITEM_column', 10, 2 );
}

function set_custom_edit_ITEM_columns($columns) {
    $columns['post_id'] = __( 'Item ID', 'your_text_domain' );
    return $columns;
}

function custom_ITEM_column( $column, $post_id ) {
    switch ( $column ) {
        case 'post_id' :
            echo $post_id; 
        break;
    }
}

