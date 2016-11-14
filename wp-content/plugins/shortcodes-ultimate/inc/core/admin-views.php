<?php
class Su_Admin_Views {
	function __construct() {}

	public static function about( $field, $config ) {
		ob_start();
?>
<div id="su-about-screen">
	<h1><?php _e( 'Welcome to Shortcodes Ultimate', 'shortcodes-ultimate' ); ?> <small><?php _e( 'A real swiss army knife for WordPress', 'shortcodes-ultimate' ); ?></small></h1>
	<div class="sunrise-inline-menu">
		<a href="http://gndev.info/shortcodes-ultimate/" target="_blank"><strong><?php _e( 'Project homepage', 'shortcodes-ultimate' ); ?></strong></a>
		<a href="http://gndev.info/kb/" target="_blank"><?php _e( 'Documentation', 'shortcodes-ultimate' ); ?></a>
		<a href="http://wordpress.org/support/plugin/shortcodes-ultimate/" target="_blank"><?php _e( 'Support forum', 'shortcodes-ultimate' ); ?></a>
		<a href="http://wordpress.org/extend/plugins/shortcodes-ultimate/changelog/" target="_blank"><?php _e( 'Changelog', 'shortcodes-ultimate' ); ?></a>
		<a href="https://github.com/gndev/shortcodes-ultimate" target="_blank"><?php _e( 'Fork on GitHub', 'shortcodes-ultimate' ); ?></a>
	</div>
	<div class="su-clearfix">
		<div class="su-about-column">
			<h3><?php _e( 'Plugin features', 'shortcodes-ultimate' ); ?></h3>
			<ul>
				<li><?php _e( '40+ amazing shortcodes', 'shortcodes-ultimate' ); ?></li>
				<li><?php _e( 'Power of CSS3 transitions', 'shortcodes-ultimate' ); ?></li>
				<li><?php _e( 'Handy shortcodes generator', 'shortcodes-ultimate' ) ?></li>
				<li><?php _e( 'International', 'shortcodes-ultimate' ); ?></li>
				<li><?php _e( 'Documented API', 'shortcodes-ultimate' ); ?></li>
			</ul>
		</div>
		<div class="su-about-column">
			<h3><?php _e( 'What is a shortcode?', 'shortcodes-ultimate' ); ?></h3>
			<p><?php _e( '<strong>Shortcode</strong> is a WordPress-specific code that lets you do nifty things with very little effort.', 'shortcodes-ultimate' ); ?></p>
			<p><?php _e( 'Shortcodes can embed files or create objects that would normally require lots of complicated, ugly code in just one line. Shortcode = shortcut.', 'shortcodes-ultimate' ); ?></p>
		</div>
	</div>
	<div class="su-clearfix">
		<div class="su-about-column">
			<h3><?php _e( 'How does it works', 'shortcodes-ultimate' ); ?></h3>
			<a href="http://www.youtube.com/watch?v=lni-w2dtcQY?autoplay=1&amp;showinfo=0&amp;rel=0&amp;theme=light#" target="_blank" class="su-demo-video"><img src="<?php echo plugins_url( 'assets/images/banners/how-it-works.jpg', SU_PLUGIN_FILE ); ?>" alt=""></a>
		</div>
		<div class="su-about-column">
			<h3><?php _e( 'More videos', 'shortcodes-ultimate' ); ?></h3>
			<ul>
				<li><a href="http://www.youtube.com/watch?v=IjmaXz-b55I" target="_blank"><?php _e( 'Shortcodes Ultimate Tutorial', 'shortcodes-ultimate' ); ?></a></li>
				<li><a href="http://www.youtube.com/watch?v=YU3Zu6C5ZfA" target="_blank"><?php _e( 'How to use special widget', 'shortcodes-ultimate' ); ?></a></li>
				<li><a href="http://www.screenr.com/BK0H" target="_blank"><?php _e( 'How to create Carousel', 'shortcodes-ultimate' ); ?></a></li>
				<li><a href="http://www.youtube.com/watch?v=kCWyO2F7jTw" target="_blank"><?php _e( 'How to create image gallery', 'shortcodes-ultimate' ); ?></a></li>
			</ul>
		</div>
	</div>
</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();
		su_query_asset( 'css', array( 'magnific-popup', 'su-options-page' ) );
		su_query_asset( 'js', array( 'jquery', 'magnific-popup', 'su-options-page' ) );
		return $output;
	}

	public static function custom_css( $field, $config ) {
		ob_start();
?>
<div id="su-custom-css-screen">
	<div class="su-custom-css-originals">
		<p><strong><?php _e( 'You can overview the original styles to overwrite it', $config['textdomain'] ); ?></strong></p>
		<div class="sunrise-inline-menu">
			<a href="<?php echo su_skin_url( 'content-shortcodes.css' ); ?>">content-shortcodes.css</a>
			<a href="<?php echo su_skin_url( 'box-shortcodes.css' ); ?>">box-shortcodes.css</a>
			<a href="<?php echo su_skin_url( 'media-shortcodes.css' ); ?>">media-shortcodes.css</a>
			<a href="<?php echo su_skin_url( 'galleries-shortcodes.css' ); ?>">galleries-shortcodes.css</a>
			<a href="<?php echo su_skin_url( 'players-shortcodes.css' ); ?>">players-shortcodes.css</a>
			<a href="<?php echo su_skin_url( 'other-shortcodes.css' ); ?>">other-shortcodes.css</a>
		</div>
		<?php do_action( 'su/admin/css/originals/after' ); ?>
	</div>
	<div class="su-custom-css-vars">
		<p><strong><?php _e( 'You can use next variables in your custom CSS', $config['textdomain'] ); ?></strong></p>
		<code>%home_url%</code> - <?php _e( 'home url', $config['textdomain'] ); ?><br/>
		<code>%theme_url%</code> - <?php _e( 'theme url', $config['textdomain'] ); ?><br/>
		<code>%plugin_url%</code> - <?php _e( 'plugin url', $config['textdomain'] ); ?>
	</div>
	<div id="su-custom-css-editor">
		<div id="sunrise-field-<?php echo $field['id']; ?>-editor"></div>
		<textarea name="sunrise[<?php echo $field['id']; ?>]" id="sunrise-field-<?php echo $field['id']; ?>" class="regular-text" rows="10"><?php echo stripslashes( get_option( $config['prefix'] . $field['id'] ) ); ?></textarea>
	</div>
</div>
			<?php
		$output = ob_get_contents();
		ob_end_clean();
		su_query_asset( 'css', array( 'magnific-popup', 'su-options-page' ) );
		su_query_asset( 'js', array( 'jquery', 'magnific-popup', 'ace', 'su-options-page' ) );
		return $output;
	}

	public static function examples( $field, $config ) {
		$output = array();
		$examples = Su_Data::examples();
		$preview = '<div style="display:none"><div id="su-examples-window"><div id="su-examples-preview"></div></div></div>';
		$open = ( isset( $_GET['example'] ) ) ? sanitize_key( $_GET['example'] ) : '';
		$open = '<input id="su_open_example" type="hidden" name="su_open_example" value="' . $open . '" />';
		$nonce = '<input id="su_examples_nonce" type="hidden" name="su_examples_nonce" value="' . wp_create_nonce( 'su_examples_nonce' ) . '" />';
		foreach ( $examples as $group ) {
			$items = array();
			if ( isset( $group['items'] ) ) foreach ( $group['items'] as $item ) {
					$code = ( isset( $item['code'] ) ) ? $item['code'] : plugins_url( 'inc/examples/' . $item['id'] . '.example', SU_PLUGIN_FILE );
					$id = ( isset( $item['id'] ) ) ? $item['id'] : '';
					$items[] = '<div class="su-examples-item" data-code="' . $code . '" data-id="' . $id . '" data-mfp-src="#su-examples-window"><i class="fa fa-' . $item['icon'] . '"></i> ' . $item['name'] . '</div>';
				}
			$output[] = '<div class="su-examples-group su-clearfix"><h2 class="su-examples-group-title">' . $group['title'] . '</h2>' . implode( '', $items ) . '</div>';
		}
		su_query_asset( 'css', array( 'magnific-popup', 'font-awesome', 'su-options-page' ) );
		su_query_asset( 'js', array( 'jquery', 'magnific-popup', 'su-options-page' ) );
		return '<div id="su-examples-screen">' . implode( '', $output ) . '</div>' . $preview . $open . $nonce;
	}

	public static function cheatsheet( $field, $config ) {
		// Prepare print button
		$print = '<div><a href="javascript:;" id="su-cheatsheet-print" class="su-cheatsheet-switch button button-primary button-large">' . __( 'Printable version', 'shortcodes-ultimate' ) . '</a><div id="su-cheatsheet-print-head"><h1>' . __( 'Shortcodes Ultimate', 'shortcodes-ultimate' ) . ': ' . __( 'Cheatsheet', 'shortcodes-ultimate' ) . '</h1><a href="javascript:;" class="su-cheatsheet-switch">&larr; ' . __( 'Back to Dashboard', 'shortcodes-ultimate' ) . '</a></div></div>';
		// Prepare table array
		$table = array();
		// Table start
		$table[] = '<table><tr><th style="width:20%;">' . __( 'Shortcode', 'shortcodes-ultimate' ) . '</th><th style="width:50%">' . __( 'Attributes', 'shortcodes-ultimate' ) . '</th><th style="width:30%">' . __( 'Example code', 'shortcodes-ultimate' ) . '</th></tr>';
		// Loop through shortcodes
		foreach ( (array) Su_Data::shortcodes() as $name => $shortcode ) {
			// Prepare vars
			$icon = ( isset( $shortcode['icon'] ) ) ? $shortcode['icon'] : 'puzzle-piece';
			$shortcode['name'] = ( isset( $shortcode['name'] ) ) ? $shortcode['name'] : $name;
			$attributes = array();
			$example = array();
			$icons = 'icon: music, icon: envelope &hellip; <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">' . __( 'full list', 'shortcodes-ultimate' ) . '</a>';
			// Loop through attributes
			if ( is_array( $shortcode['atts'] ) )
				foreach ( $shortcode['atts'] as $id => $data ) {
					// Prepare default value
					$default = ( isset( $data['default'] ) && $data['default'] !== '' ) ? '<p><em>' . __( 'Default value', 'shortcodes-ultimate' ) . ':</em> ' . $data['default'] . '</p>' : '';
					// Check type is set
					if ( empty( $data['type'] ) ) $data['type'] = 'text';
					// Switch attribute types
					switch ( $data['type'] ) {
						// Select
					case 'select':
						$value = implode( ', ', array_keys( $data['values'] ) );
						break;
						// Slider and number
					case 'slider':
					case 'number':
						$value = $data['min'] . '&hellip;' . $data['max'];
						break;
						// Bool
					case 'bool':
						$value = 'yes | no';
						break;
						// Icon
					case 'icon':
						$value = $icons;
						break;
						// Color
					case 'color':
						$value = __( '#RGB and rgba() colors' );
						break;
						// Default value
					default:
						$value = $data['default'];
						break;
					}
					// Check empty value
					if ( $value === '' ) $value = __( 'Any text value', 'shortcodes-ultimate' );
					// Extra CSS class
					if ( $id === 'class' ) $value = __( 'Any custom CSS classes', 'shortcodes-ultimate' );
					// Add attribute
					$attributes[] = '<div class="su-shortcode-attribute"><strong>' . $data['name'] . ' <em>&ndash; ' . $id . '</em></strong><p><em>' . __( 'Possible values', 'shortcodes-ultimate' ) . ':</em> ' . $value . '</p>' . $default . '</div>';
					// Add attribute to the example code
					$example[] = $id . '="' . $data['default'] . '"';
				}
			// Prepare example code
			$example = '[%prefix_' . $name . ' ' . implode( ' ', $example ) . ']';
			// Prepare content value
			if ( empty( $shortcode['content'] ) ) $shortcode['content'] = '';
			// Add wrapping code
			if ( $shortcode['type'] === 'wrap' ) $example .= esc_textarea( $shortcode['content'] ) . '[/%prefix_' . $name . ']';
			// Change compatibility prefix
			$example = str_replace( array( '%prefix_', '__' ), su_cmpt(), $example );
			// Shortcode
			$table[] = '<td>' . '<span class="su-shortcode-icon">' . Su_Tools::icon( $icon ) . '</span>' . $shortcode['name'] . '<br/><em class="su-shortcode-desc">' . $shortcode['desc'] . '</em></td>';
			// Attributes
			$table[] = '<td>' . implode( '', $attributes ) . '</td>';
			// Example code
			$table[] = '<td><code contenteditable="true">' . $example . '</code></td></tr>';
		}
		// Table end
		$table[] = '</table>';
		// Query assets
		su_query_asset( 'css', array( 'font-awesome', 'su-cheatsheet' ) );
		su_query_asset( 'js', array( 'jquery', 'su-options-page' ) );
		// Return output
		return '<div id="su-cheatsheet-screen">' . $print . implode( '', $table ) . '</div>';
	}

	public static function addons( $field, $config ) {
		$output = array();
		$addons = array(
			array(
				'name' => __( 'New Shortcodes', 'shortcodes-ultimate' ),
				'desc' => __( 'Parallax sections, responsive content slider, pricing tables, vector icons, testimonials, progress bars and even more', 'shortcodes-ultimate' ),
				'url' => 'http://gndev.info/shortcodes-ultimate/extra/',
				'image' => plugins_url( 'assets/images/banners/extra.png', SU_PLUGIN_FILE )
			),
			array(
				'name' => __( 'Maker', 'shortcodes-ultimate' ),
				'desc' => __( 'This add-on allows you to create custom shortcodes. You can easily create any shortcode with different parameters or even override default shortcodes', 'shortcodes-ultimate' ),
				'url' => 'http://gndev.info/shortcodes-ultimate/maker/',
				'image' => plugins_url( 'assets/images/banners/maker.png', SU_PLUGIN_FILE )
			),
			array(
				'name' => __( 'Skins', 'shortcodes-ultimate' ),
				'desc' => __( 'Set of additional skins for Shortcodes Ultimate. It includes skins for accordeons/spoilers, tabs and some other shortcodes', 'shortcodes-ultimate' ),
				'url' => 'http://gndev.info/shortcodes-ultimate/skins/',
				'image' => plugins_url( 'assets/images/banners/skins.png', SU_PLUGIN_FILE )
			),
			array(
				'name' => __( 'Add-ons bundle', 'shortcodes-ultimate' ),
				'desc' => __( 'Get all three add-ons with huge discount!', 'shortcodes-ultimate' ),
				'url' => 'http://gndev.info/shortcodes-ultimate/add-ons-bundle/',
				'image' => plugins_url( 'assets/images/banners/bundle.png', SU_PLUGIN_FILE )
			),
		);
		$plugins = array();
		$output[] = '<h2>' . __( 'Shortcodes Ultimate Add-ons', 'shortcodes-ultimate' ) . '</h2>';
		$output[] = '<div class="su-addons-loop su-clearfix">';
		foreach ( $addons as $addon ) {
			$output[] = '<div class="su-addons-item" style="visibility:hidden" data-url="' . $addon['url'] . '"><img src="' . $addon['image'] . '" alt="' . $addon['image'] . '" /><div class="su-addons-item-content"><h4>' . $addon['name'] . '</h4><p>' . $addon['desc'] . '</p><div class="su-addons-item-button"><a href="' . $addon['url'] . '" class="button button-primary" target="_blank">' . __( 'Learn more', 'shortcodes-ultimate' ) . '</a></div></div></div>';
		}
		$output[] = '</div>';
		if ( count( $plugins ) ) {
			$output[] = '<h2>' . __( 'Other WordPress Plugins', 'shortcodes-ultimate' ) . '</h2>';
			$output[] = '<div class="su-addons-loop su-clearfix">';
			foreach ( $plugins as $plugin ) {
				$output[] = '<div class="su-addons-item" style="visibility:hidden" data-url="' . $plugin['url'] . '"><img src="' . $plugin['image'] . '" alt="' . $plugin['image'] . '" /><div class="su-addons-item-content"><h4>' . $plugin['name'] . '</h4><p>' . $plugin['desc'] . '</p>' . Su_Shortcodes::button( array( 'url' => $plugin['url'], 'target' => 'blank', 'style' => 'flat', 'background' => '#FF7654', 'wide' => 'yes', 'radius' => '0' ), __( 'Learn more', 'shortcodes-ultimate' ) ) . '</div></div>';
			}
			$output[] = '</div>';
		}
		su_query_asset( 'css', array( 'animate', 'su-options-page' ) );
		su_query_asset( 'js', array( 'jquery', 'su-options-page' ) );
		return '<div id="su-addons-screen">' . implode( '', $output ) . '</div>';
	}
}
