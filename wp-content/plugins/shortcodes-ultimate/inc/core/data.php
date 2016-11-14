<?php
/**
 * Class for managing plugin data
 */
class Su_Data {

	/**
	 * Constructor
	 */
	function __construct() {}

	/**
	 * Shortcode groups
	 */
	public static function groups() {
		return apply_filters( 'su/data/groups', array(
				'all'     => __( 'All', 'shortcodes-ultimate' ),
				'content' => __( 'Content', 'shortcodes-ultimate' ),
				'box'     => __( 'Box', 'shortcodes-ultimate' ),
				'media'   => __( 'Media', 'shortcodes-ultimate' ),
				'gallery' => __( 'Gallery', 'shortcodes-ultimate' ),
				'data'    => __( 'Data', 'shortcodes-ultimate' ),
				'other'   => __( 'Other', 'shortcodes-ultimate' )
			) );
	}

	/**
	 * Border styles
	 */
	public static function borders() {
		return apply_filters( 'su/data/borders', array(
				'none'   => __( 'None', 'shortcodes-ultimate' ),
				'solid'  => __( 'Solid', 'shortcodes-ultimate' ),
				'dotted' => __( 'Dotted', 'shortcodes-ultimate' ),
				'dashed' => __( 'Dashed', 'shortcodes-ultimate' ),
				'double' => __( 'Double', 'shortcodes-ultimate' ),
				'groove' => __( 'Groove', 'shortcodes-ultimate' ),
				'ridge'  => __( 'Ridge', 'shortcodes-ultimate' )
			) );
	}

	/**
	 * Font-Awesome icons
	 */
	public static function icons() {
		return apply_filters( 'su/data/icons', array( 'adjust', 'adn', 'align-center', 'align-justify', 'align-left', 'align-right', 'ambulance', 'anchor', 'android', 'angle-double-down', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'apple', 'archive', 'arrow-circle-down', 'arrow-circle-left', 'arrow-circle-o-down', 'arrow-circle-o-left', 'arrow-circle-o-right', 'arrow-circle-o-up', 'arrow-circle-right', 'arrow-circle-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows', 'arrows-alt', 'arrows-h', 'arrows-v', 'asterisk', 'automobile', 'backward', 'ban', 'bank', 'bar-chart-o', 'barcode', 'bars', 'beer', 'behance', 'behance-square', 'bell', 'bell-o', 'bitbucket', 'bitbucket-square', 'bitcoin', 'bold', 'bolt', 'bomb', 'book', 'bookmark', 'bookmark-o', 'briefcase', 'btc', 'bug', 'building', 'building-o', 'bullhorn', 'bullseye', 'cab', 'calendar', 'calendar-o', 'camera', 'camera-retro', 'car', 'caret-down', 'caret-left', 'caret-right', 'caret-square-o-down', 'caret-square-o-left', 'caret-square-o-right', 'caret-square-o-up', 'caret-up', 'certificate', 'chain', 'chain-broken', 'check', 'check-circle', 'check-circle-o', 'check-square', 'check-square-o', 'chevron-circle-down', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'child', 'circle', 'circle-o', 'circle-o-notch', 'circle-thin', 'clipboard', 'clock-o', 'cloud', 'cloud-download', 'cloud-upload', 'cny', 'code', 'code-fork', 'codepen', 'coffee', 'cog', 'cogs', 'columns', 'comment', 'comment-o', 'comments', 'comments-o', 'compass', 'compress', 'copy', 'credit-card', 'crop', 'crosshairs', 'css3', 'cube', 'cubes', 'cut', 'cutlery', 'dashboard', 'database', 'dedent', 'delicious', 'desktop', 'deviantart', 'digg', 'dollar', 'dot-circle-o', 'download', 'dribbble', 'dropbox', 'drupal', 'edit', 'eject', 'ellipsis-h', 'ellipsis-v', 'empire', 'envelope', 'envelope-o', 'envelope-square', 'eraser', 'eur', 'euro', 'exchange', 'exclamation', 'exclamation-circle', 'exclamation-triangle', 'expand', 'external-link', 'external-link-square', 'eye', 'eye-slash', 'facebook', 'facebook-square', 'fast-backward', 'fast-forward', 'fax', 'female', 'fighter-jet', 'file', 'file-archive-o', 'file-audio-o', 'file-code-o', 'file-excel-o', 'file-image-o', 'file-movie-o', 'file-o', 'file-pdf-o', 'file-photo-o', 'file-picture-o', 'file-powerpoint-o', 'file-sound-o', 'file-text', 'file-text-o', 'file-video-o', 'file-word-o', 'file-zip-o', 'files-o', 'film', 'filter', 'fire', 'fire-extinguisher', 'flag', 'flag-checkered', 'flag-o', 'flash', 'flask', 'flickr', 'floppy-o', 'folder', 'folder-o', 'folder-open', 'folder-open-o', 'font', 'forward', 'foursquare', 'frown-o', 'gamepad', 'gavel', 'gbp', 'ge', 'gear', 'gears', 'gift', 'git', 'git-square', 'github', 'github-alt', 'github-square', 'gittip', 'glass', 'globe', 'google', 'google-plus', 'google-plus-square', 'graduation-cap', 'group', 'h-square', 'hacker-news', 'hand-o-down', 'hand-o-left', 'hand-o-right', 'hand-o-up', 'hdd-o', 'header', 'headphones', 'heart', 'heart-o', 'history', 'home', 'hospital-o', 'html5', 'image', 'inbox', 'indent', 'info', 'info-circle', 'inr', 'instagram', 'institution', 'italic', 'joomla', 'jpy', 'jsfiddle', 'key', 'keyboard-o', 'krw', 'language', 'laptop', 'leaf', 'legal', 'lemon-o', 'level-down', 'level-up', 'life-bouy', 'life-ring', 'life-saver', 'lightbulb-o', 'link', 'linkedin', 'linkedin-square', 'linux', 'list', 'list-alt', 'list-ol', 'list-ul', 'location-arrow', 'lock', 'long-arrow-down', 'long-arrow-left', 'long-arrow-right', 'long-arrow-up', 'magic', 'magnet', 'mail-forward', 'mail-reply', 'mail-reply-all', 'male', 'map-marker', 'maxcdn', 'medkit', 'meh-o', 'microphone', 'microphone-slash', 'minus', 'minus-circle', 'minus-square', 'minus-square-o', 'mobile', 'mobile-phone', 'money', 'moon-o', 'mortar-board', 'music', 'navicon', 'openid', 'outdent', 'pagelines', 'paper-plane', 'paper-plane-o', 'paperclip', 'paragraph', 'paste', 'pause', 'paw', 'pencil', 'pencil-square', 'pencil-square-o', 'phone', 'phone-square', 'photo', 'picture-o', 'pied-piper', 'pied-piper-alt', 'pied-piper-square', 'pinterest', 'pinterest-square', 'plane', 'play', 'play-circle', 'play-circle-o', 'plus', 'plus-circle', 'plus-square', 'plus-square-o', 'power-off', 'print', 'puzzle-piece', 'qq', 'qrcode', 'question', 'question-circle', 'quote-left', 'quote-right', 'ra', 'random', 'rebel', 'recycle', 'reddit', 'reddit-square', 'refresh', 'renren', 'reorder', 'repeat', 'reply', 'reply-all', 'retweet', 'rmb', 'road', 'rocket', 'rotate-left', 'rotate-right', 'rouble', 'rss', 'rss-square', 'rub', 'ruble', 'rupee', 'save', 'scissors', 'search', 'search-minus', 'search-plus', 'send', 'send-o', 'share', 'share-alt', 'share-alt-square', 'share-square', 'share-square-o', 'shield', 'shopping-cart', 'sign-in', 'sign-out', 'signal', 'sitemap', 'skype', 'slack', 'sliders', 'smile-o', 'sort', 'sort-alpha-asc', 'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-asc', 'sort-desc', 'sort-down', 'sort-numeric-asc', 'sort-numeric-desc', 'sort-up', 'soundcloud', 'space-shuttle', 'spinner', 'spoon', 'spotify', 'square', 'square-o', 'stack-exchange', 'stack-overflow', 'star', 'star-half', 'star-half-empty', 'star-half-full', 'star-half-o', 'star-o', 'steam', 'steam-square', 'step-backward', 'step-forward', 'stethoscope', 'stop', 'strikethrough', 'stumbleupon', 'stumbleupon-circle', 'subscript', 'suitcase', 'sun-o', 'superscript', 'support', 'table', 'tablet', 'tachometer', 'tag', 'tags', 'tasks', 'taxi', 'tencent-weibo', 'terminal', 'text-height', 'text-width', 'th', 'th-large', 'th-list', 'thumb-tack', 'thumbs-down', 'thumbs-o-down', 'thumbs-o-up', 'thumbs-up', 'ticket', 'times', 'times-circle', 'times-circle-o', 'tint', 'toggle-down', 'toggle-left', 'toggle-right', 'toggle-up', 'trash-o', 'tree', 'trello', 'trophy', 'truck', 'try', 'tumblr', 'tumblr-square', 'turkish-lira', 'twitter', 'twitter-square', 'umbrella', 'underline', 'undo', 'university', 'unlink', 'unlock', 'unlock-alt', 'unsorted', 'upload', 'usd', 'user', 'user-md', 'users', 'video-camera', 'vimeo-square', 'vine', 'vk', 'volume-down', 'volume-off', 'volume-up', 'warning', 'wechat', 'weibo', 'weixin', 'wheelchair', 'windows', 'won', 'wordpress', 'wrench', 'xing', 'xing-square', 'yahoo', 'yen', 'youtube', 'youtube-play', 'youtube-square' ) );
	}

	/**
	 * Animate.css animations
	 */
	public static function animations() {
		return apply_filters( 'su/data/animations', array( 'flash', 'bounce', 'shake', 'tada', 'swing', 'wobble', 'pulse', 'flip', 'flipInX', 'flipOutX', 'flipInY', 'flipOutY', 'fadeIn', 'fadeInUp', 'fadeInDown', 'fadeInLeft', 'fadeInRight', 'fadeInUpBig', 'fadeInDownBig', 'fadeInLeftBig', 'fadeInRightBig', 'fadeOut', 'fadeOutUp', 'fadeOutDown', 'fadeOutLeft', 'fadeOutRight', 'fadeOutUpBig', 'fadeOutDownBig', 'fadeOutLeftBig', 'fadeOutRightBig', 'slideInDown', 'slideInLeft', 'slideInRight', 'slideOutUp', 'slideOutLeft', 'slideOutRight', 'bounceIn', 'bounceInDown', 'bounceInUp', 'bounceInLeft', 'bounceInRight', 'bounceOut', 'bounceOutDown', 'bounceOutUp', 'bounceOutLeft', 'bounceOutRight', 'rotateIn', 'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight', 'rotateOut', 'rotateOutDownLeft', 'rotateOutDownRight', 'rotateOutUpLeft', 'rotateOutUpRight', 'lightSpeedIn', 'lightSpeedOut', 'hinge', 'rollIn', 'rollOut' ) );
	}

	/**
	 * Examples section
	 */
	public static function examples() {
		return apply_filters( 'su/data/examples', array(
				'basic' => array(
					'title' => __( 'Basic examples', 'shortcodes-ultimate' ),
					'items' => array(
						array(
							'name' => __( 'Accordions, spoilers, different styles, anchors', 'shortcodes-ultimate' ),
							'id'   => 'spoilers',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/spoilers.example',
							'icon' => 'tasks'
						),
						array(
							'name' => __( 'Tabs, vertical tabs, tab anchors', 'shortcodes-ultimate' ),
							'id'   => 'tabs',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/tabs.example',
							'icon' => 'folder'
						),
						array(
							'name' => __( 'Column layouts', 'shortcodes-ultimate' ),
							'id'   => 'columns',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/columns.example',
							'icon' => 'th-large'
						),
						array(
							'name' => __( 'Media elements, YouTube, Vimeo, Screenr and self-hosted videos, audio player', 'shortcodes-ultimate' ),
							'id'   => 'media',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/media.example',
							'icon' => 'play-circle'
						),
						array(
							'name' => __( 'Unlimited buttons', 'shortcodes-ultimate' ),
							'id'   => 'buttons',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/buttons.example',
							'icon' => 'heart'
						),
						array(
							'name' => __( 'Animations', 'shortcodes-ultimate' ),
							'id'   => 'animations',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/animations.example',
							'icon' => 'bolt'
						),
					)
				),
				'advanced' => array(
					'title' => __( 'Advanced examples', 'shortcodes-ultimate' ),
					'items' => array(
						array(
							'name' => __( 'Interacting with posts shortcode', 'shortcodes-ultimate' ),
							'id' => 'posts',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/posts.example',
							'icon' => 'list'
						),
						array(
							'name' => __( 'Nested shortcodes, shortcodes inside of attributes', 'shortcodes-ultimate' ),
							'id' => 'nested',
							'code' => plugin_dir_path( SU_PLUGIN_FILE ) . '/inc/examples/nested.example',
							'icon' => 'indent'
						),
					)
				),
			) );
	}

	/**
	 * Shortcodes
	 */
	public static function shortcodes( $shortcode = false ) {
		$shortcodes = apply_filters( 'su/data/shortcodes', array(
				// heading
				'heading' => array(
					'name' => __( 'Heading', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose style for this heading', 'shortcodes-ultimate' ) . '%su_skins_link%'
						),
						'size' => array(
							'type' => 'slider',
							'min' => 7,
							'max' => 48,
							'step' => 1,
							'default' => 13,
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Select heading size (pixels)', 'shortcodes-ultimate' )
						),
						'align' => array(
							'type' => 'select',
							'values' => array(
								'left' => __( 'Left', 'shortcodes-ultimate' ),
								'center' => __( 'Center', 'shortcodes-ultimate' ),
								'right' => __( 'Right', 'shortcodes-ultimate' )
							),
							'default' => 'center',
							'name' => __( 'Align', 'shortcodes-ultimate' ),
							'desc' => __( 'Heading text alignment', 'shortcodes-ultimate' )
						),
						'margin' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 200,
							'step' => 10,
							'default' => 20,
							'name' => __( 'Margin', 'shortcodes-ultimate' ),
							'desc' => __( 'Bottom margin (pixels)', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Heading text', 'shortcodes-ultimate' ),
					'desc' => __( 'Styled heading', 'shortcodes-ultimate' ),
					'icon' => 'h-square'
				),
				// tabs
				'tabs' => array(
					'name' => __( 'Tabs', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose style for this tabs', 'shortcodes-ultimate' ) . '%su_skins_link%'
						),
						'active' => array(
							'type' => 'number',
							'min' => 1,
							'max' => 100,
							'step' => 1,
							'default' => 1,
							'name' => __( 'Active tab', 'shortcodes-ultimate' ),
							'desc' => __( 'Select which tab is open by default', 'shortcodes-ultimate' )
						),
						'vertical' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Vertical', 'shortcodes-ultimate' ),
							'desc' => __( 'Show tabs vertically', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( "[%prefix_tab title=\"Title 1\"]Content 1[/%prefix_tab]\n[%prefix_tab title=\"Title 2\"]Content 2[/%prefix_tab]\n[%prefix_tab title=\"Title 3\"]Content 3[/%prefix_tab]", 'shortcodes-ultimate' ),
					'desc' => __( 'Tabs container', 'shortcodes-ultimate' ),
					'example' => 'tabs',
					'icon' => 'list-alt'
				),
				// tab
				'tab' => array(
					'name' => __( 'Tab', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'title' => array(
							'default' => __( 'Tab name', 'shortcodes-ultimate' ),
							'name' => __( 'Title', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter tab name', 'shortcodes-ultimate' )
						),
						'disabled' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Disabled', 'shortcodes-ultimate' ),
							'desc' => __( 'Is this tab disabled', 'shortcodes-ultimate' )
						),
						'anchor' => array(
							'default' => '',
							'name' => __( 'Anchor', 'shortcodes-ultimate' ),
							'desc' => __( 'You can use unique anchor for this tab to access it with hash in page url. For example: type here <b%value>Hello</b> and then use url like http://example.com/page-url#Hello. This tab will be activated and scrolled in', 'shortcodes-ultimate' )
						),
						'url' => array(
							'default' => '',
							'name' => __( 'URL', 'shortcodes-ultimate' ),
							'desc' => __( 'You can link this tab to any webpage. Enter here full URL to switch this tab into link', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self'  => __( 'Open link in same window/tab', 'shortcodes-ultimate' ),
								'blank' => __( 'Open link in new window/tab', 'shortcodes-ultimate' )
							),
							'default' => 'blank',
							'name' => __( 'Link target', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose how to open the custom tab link', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Tab content', 'shortcodes-ultimate' ),
					'desc' => __( 'Single tab', 'shortcodes-ultimate' ),
					'note' => __( 'Did you know that you need to wrap single tabs with [tabs] shortcode?', 'shortcodes-ultimate' ),
					'example' => 'tabs',
					'icon' => 'list-alt'
				),
				// spoiler
				'spoiler' => array(
					'name' => __( 'Spoiler', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'title' => array(
							'default' => __( 'Spoiler title', 'shortcodes-ultimate' ),
							'name' => __( 'Title', 'shortcodes-ultimate' ), 'desc' => __( 'Text in spoiler title', 'shortcodes-ultimate' )
						),
						'open' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Open', 'shortcodes-ultimate' ),
							'desc' => __( 'Is spoiler content visible by default', 'shortcodes-ultimate' )
						),
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'fancy' => __( 'Fancy', 'shortcodes-ultimate' ),
								'simple' => __( 'Simple', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose style for this spoiler', 'shortcodes-ultimate' ) . '%su_skins_link%'
						),
						'icon' => array(
							'type' => 'select',
							'values' => array(
								'plus'           => __( 'Plus', 'shortcodes-ultimate' ),
								'plus-circle'    => __( 'Plus circle', 'shortcodes-ultimate' ),
								'plus-square-1'  => __( 'Plus square 1', 'shortcodes-ultimate' ),
								'plus-square-2'  => __( 'Plus square 2', 'shortcodes-ultimate' ),
								'arrow'          => __( 'Arrow', 'shortcodes-ultimate' ),
								'arrow-circle-1' => __( 'Arrow circle 1', 'shortcodes-ultimate' ),
								'arrow-circle-2' => __( 'Arrow circle 2', 'shortcodes-ultimate' ),
								'chevron'        => __( 'Chevron', 'shortcodes-ultimate' ),
								'chevron-circle' => __( 'Chevron circle', 'shortcodes-ultimate' ),
								'caret'          => __( 'Caret', 'shortcodes-ultimate' ),
								'caret-square'   => __( 'Caret square', 'shortcodes-ultimate' ),
								'folder-1'       => __( 'Folder 1', 'shortcodes-ultimate' ),
								'folder-2'       => __( 'Folder 2', 'shortcodes-ultimate' )
							),
							'default' => 'plus',
							'name' => __( 'Icon', 'shortcodes-ultimate' ),
							'desc' => __( 'Icons for spoiler', 'shortcodes-ultimate' )
						),
						'anchor' => array(
							'default' => '',
							'name' => __( 'Anchor', 'shortcodes-ultimate' ),
							'desc' => __( 'You can use unique anchor for this spoiler to access it with hash in page url. For example: type here <b%value>Hello</b> and then use url like http://example.com/page-url#Hello. This spoiler will be open and scrolled in', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Hidden content', 'shortcodes-ultimate' ),
					'desc' => __( 'Spoiler with hidden content', 'shortcodes-ultimate' ),
					'note' => __( 'Did you know that you can wrap multiple spoilers with [accordion] shortcode to create accordion effect?', 'shortcodes-ultimate' ),
					'example' => 'spoilers',
					'icon' => 'list-ul'
				),
				// accordion
				'accordion' => array(
					'name' => __( 'Accordion', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( "[%prefix_spoiler]Content[/%prefix_spoiler]\n[%prefix_spoiler]Content[/%prefix_spoiler]\n[%prefix_spoiler]Content[/%prefix_spoiler]", 'shortcodes-ultimate' ),
					'desc' => __( 'Accordion with spoilers', 'shortcodes-ultimate' ),
					'note' => __( 'Did you know that you can wrap multiple spoilers with [accordion] shortcode to create accordion effect?', 'shortcodes-ultimate' ),
					'example' => 'spoilers',
					'icon' => 'list'
				),
				// divider
				'divider' => array(
					'name' => __( 'Divider', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content',
					'atts' => array(
						'top' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show TOP link', 'shortcodes-ultimate' ),
							'desc' => __( 'Show link to top of the page or not', 'shortcodes-ultimate' )
						),
						'text' => array(
							'values' => array( ),
							'default' => __( 'Go to top', 'shortcodes-ultimate' ),
							'name' => __( 'Link text', 'shortcodes-ultimate' ), 'desc' => __( 'Text for the GO TOP link', 'shortcodes-ultimate' )
						),
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'dotted'  => __( 'Dotted', 'shortcodes-ultimate' ),
								'dashed'  => __( 'Dashed', 'shortcodes-ultimate' ),
								'double'  => __( 'Double', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose style for this divider', 'shortcodes-ultimate' )
						),
						'divider_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#999999',
							'name' => __( 'Divider color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick the color for divider', 'shortcodes-ultimate' )
						),
						'link_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#999999',
							'name' => __( 'Link color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick the color for TOP link', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 40,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Height of the divider (in pixels)', 'shortcodes-ultimate' )
						),
						'margin' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 200,
							'step' => 5,
							'default' => 15,
							'name' => __( 'Margin', 'shortcodes-ultimate' ),
							'desc' => __( 'Adjust the top and bottom margins of this divider (in pixels)', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Content divider with optional TOP link', 'shortcodes-ultimate' ),
					'icon' => 'ellipsis-h'
				),
				// spacer
				'spacer' => array(
					'name' => __( 'Spacer', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content other',
					'atts' => array(
						'size' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 800,
							'step' => 10,
							'default' => 20,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Height of the spacer in pixels', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Empty space with adjustable height', 'shortcodes-ultimate' ),
					'icon' => 'arrows-v'
				),
				// highlight
				'highlight' => array(
					'name' => __( 'Highlight', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'background' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#DDFF99',
							'name' => __( 'Background', 'shortcodes-ultimate' ),
							'desc' => __( 'Highlighted text background color', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#000000',
							'name' => __( 'Text color', 'shortcodes-ultimate' ), 'desc' => __( 'Highlighted text color', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Highlighted text', 'shortcodes-ultimate' ),
					'desc' => __( 'Highlighted text', 'shortcodes-ultimate' ),
					'icon' => 'pencil'
				),
				// label
				'label' => array(
					'name' => __( 'Label', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'type' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'success' => __( 'Success', 'shortcodes-ultimate' ),
								'warning' => __( 'Warning', 'shortcodes-ultimate' ),
								'important' => __( 'Important', 'shortcodes-ultimate' ),
								'black' => __( 'Black', 'shortcodes-ultimate' ),
								'info' => __( 'Info', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Type', 'shortcodes-ultimate' ),
							'desc' => __( 'Style of the label', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Label', 'shortcodes-ultimate' ),
					'desc' => __( 'Styled label', 'shortcodes-ultimate' ),
					'icon' => 'tag'
				),
				// quote
				'quote' => array(
					'name' => __( 'Quote', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose style for this quote', 'shortcodes-ultimate' ) . '%su_skins_link%'
						),
						'cite' => array(
							'default' => '',
							'name' => __( 'Cite', 'shortcodes-ultimate' ),
							'desc' => __( 'Quote author name', 'shortcodes-ultimate' )
						),
						'url' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Cite url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url of the quote author. Leave empty to disable link', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Quote', 'shortcodes-ultimate' ),
					'desc' => __( 'Blockquote alternative', 'shortcodes-ultimate' ),
					'icon' => 'quote-right'
				),
				// pullquote
				'pullquote' => array(
					'name' => __( 'Pullquote', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'align' => array(
							'type' => 'select',
							'values' => array(
								'left' => __( 'Left', 'shortcodes-ultimate' ),
								'right' => __( 'Right', 'shortcodes-ultimate' )
							),
							'default' => 'left',
							'name' => __( 'Align', 'shortcodes-ultimate' ), 'desc' => __( 'Pullquote alignment (float)', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Pullquote', 'shortcodes-ultimate' ),
					'desc' => __( 'Pullquote', 'shortcodes-ultimate' ),
					'icon' => 'quote-left'
				),
				// dropcap
				'dropcap' => array(
					'name' => __( 'Dropcap', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'flat' => __( 'Flat', 'shortcodes-ultimate' ),
								'light' => __( 'Light', 'shortcodes-ultimate' ),
								'simple' => __( 'Simple', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ), 'desc' => __( 'Dropcap style preset', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'slider',
							'min' => 1,
							'max' => 5,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose dropcap size', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'D', 'shortcodes-ultimate' ),
					'desc' => __( 'Dropcap', 'shortcodes-ultimate' ),
					'icon' => 'bold'
				),
				// frame
				'frame' => array(
					'name' => __( 'Frame', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'align' => array(
							'type' => 'select',
							'values' => array(
								'left' => __( 'Left', 'shortcodes-ultimate' ),
								'center' => __( 'Center', 'shortcodes-ultimate' ),
								'right' => __( 'Right', 'shortcodes-ultimate' )
							),
							'default' => 'left',
							'name' => __( 'Align', 'shortcodes-ultimate' ),
							'desc' => __( 'Frame alignment', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => '<img src="http://lorempixel.com/g/400/200/" />',
					'desc' => __( 'Styled image frame', 'shortcodes-ultimate' ),
					'icon' => 'picture-o'
				),
				// row
				'row' => array(
					'name' => __( 'Row', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( "[%prefix_column size=\"1/3\"]Content[/%prefix_column]\n[%prefix_column size=\"1/3\"]Content[/%prefix_column]\n[%prefix_column size=\"1/3\"]Content[/%prefix_column]", 'shortcodes-ultimate' ),
					'desc' => __( 'Row for flexible columns', 'shortcodes-ultimate' ),
					'icon' => 'columns'
				),
				// column
				'column' => array(
					'name' => __( 'Column', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'size' => array(
							'type' => 'select',
							'values' => array(
								'1/1' => __( 'Full width', 'shortcodes-ultimate' ),
								'1/2' => __( 'One half', 'shortcodes-ultimate' ),
								'1/3' => __( 'One third', 'shortcodes-ultimate' ),
								'2/3' => __( 'Two third', 'shortcodes-ultimate' ),
								'1/4' => __( 'One fourth', 'shortcodes-ultimate' ),
								'3/4' => __( 'Three fourth', 'shortcodes-ultimate' ),
								'1/5' => __( 'One fifth', 'shortcodes-ultimate' ),
								'2/5' => __( 'Two fifth', 'shortcodes-ultimate' ),
								'3/5' => __( 'Three fifth', 'shortcodes-ultimate' ),
								'4/5' => __( 'Four fifth', 'shortcodes-ultimate' ),
								'1/6' => __( 'One sixth', 'shortcodes-ultimate' ),
								'5/6' => __( 'Five sixth', 'shortcodes-ultimate' )
							),
							'default' => '1/2',
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Select column width. This width will be calculated depend page width', 'shortcodes-ultimate' )
						),
						'center' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Centered', 'shortcodes-ultimate' ),
							'desc' => __( 'Is this column centered on the page', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Column content', 'shortcodes-ultimate' ),
					'desc' => __( 'Flexible and responsive columns', 'shortcodes-ultimate' ),
					'note' => __( 'Did you know that you need to wrap columns with [row] shortcode?', 'shortcodes-ultimate' ),
					'example' => 'columns',
					'icon' => 'columns'
				),
				// list
				'list' => array(
					'name' => __( 'List', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'icon' => array(
							'type' => 'icon',
							'default' => '',
							'name' => __( 'Icon', 'shortcodes-ultimate' ),
							'desc' => __( 'You can upload custom icon for this list or pick a built-in icon', 'shortcodes-ultimate' )
						),
						'icon_color' => array(
							'type' => 'color',
							'default' => '#333333',
							'name' => __( 'Icon color', 'shortcodes-ultimate' ),
							'desc' => __( 'This color will be applied to the selected icon. Does not works with uploaded icons', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( "<ul>\n<li>List item</li>\n<li>List item</li>\n<li>List item</li>\n</ul>", 'shortcodes-ultimate' ),
					'desc' => __( 'Styled unordered list', 'shortcodes-ultimate' ),
					'icon' => 'list-ol'
				),
				// button
				'button' => array(
					'name' => __( 'Button', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'content',
					'atts' => array(
						'url' => array(
							'values' => array( ),
							'default' => get_option( 'home' ),
							'name' => __( 'Link', 'shortcodes-ultimate' ),
							'desc' => __( 'Button link', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Same tab', 'shortcodes-ultimate' ),
								'blank' => __( 'New tab', 'shortcodes-ultimate' )
							),
							'default' => 'self',
							'name' => __( 'Target', 'shortcodes-ultimate' ),
							'desc' => __( 'Button link target', 'shortcodes-ultimate' )
						),
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'flat' => __( 'Flat', 'shortcodes-ultimate' ),
								'ghost' => __( 'Ghost', 'shortcodes-ultimate' ),
								'soft' => __( 'Soft', 'shortcodes-ultimate' ),
								'glass' => __( 'Glass', 'shortcodes-ultimate' ),
								'bubbles' => __( 'Bubbles', 'shortcodes-ultimate' ),
								'noise' => __( 'Noise', 'shortcodes-ultimate' ),
								'stroked' => __( 'Stroked', 'shortcodes-ultimate' ),
								'3d' => __( '3D', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ), 'desc' => __( 'Button background style preset', 'shortcodes-ultimate' )
						),
						'background' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#2D89EF',
							'name' => __( 'Background', 'shortcodes-ultimate' ), 'desc' => __( 'Button background color', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#FFFFFF',
							'name' => __( 'Text color', 'shortcodes-ultimate' ),
							'desc' => __( 'Button text color', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'slider',
							'min' => 1,
							'max' => 20,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Button size', 'shortcodes-ultimate' )
						),
						'wide' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Fluid', 'shortcodes-ultimate' ), 'desc' => __( 'Fluid buttons has 100% width', 'shortcodes-ultimate' )
						),
						'center' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Centered', 'shortcodes-ultimate' ), 'desc' => __( 'Is button centered on the page', 'shortcodes-ultimate' )
						),
						'radius' => array(
							'type' => 'select',
							'values' => array(
								'auto' => __( 'Auto', 'shortcodes-ultimate' ),
								'round' => __( 'Round', 'shortcodes-ultimate' ),
								'0' => __( 'Square', 'shortcodes-ultimate' ),
								'5' => '5px',
								'10' => '10px',
								'20' => '20px'
							),
							'default' => 'auto',
							'name' => __( 'Radius', 'shortcodes-ultimate' ),
							'desc' => __( 'Radius of button corners. Auto-radius calculation based on button size', 'shortcodes-ultimate' )
						),
						'icon' => array(
							'type' => 'icon',
							'default' => '',
							'name' => __( 'Icon', 'shortcodes-ultimate' ),
							'desc' => __( 'You can upload custom icon for this button or pick a built-in icon', 'shortcodes-ultimate' )
						),
						'icon_color' => array(
							'type' => 'color',
							'default' => '#FFFFFF',
							'name' => __( 'Icon color', 'shortcodes-ultimate' ),
							'desc' => __( 'This color will be applied to the selected icon. Does not works with uploaded icons', 'shortcodes-ultimate' )
						),
						'text_shadow' => array(
							'type' => 'shadow',
							'default' => 'none',
							'name' => __( 'Text shadow', 'shortcodes-ultimate' ),
							'desc' => __( 'Button text shadow', 'shortcodes-ultimate' )
						),
						'desc' => array(
							'default' => '',
							'name' => __( 'Description', 'shortcodes-ultimate' ),
							'desc' => __( 'Small description under button text. This option is incompatible with icon.', 'shortcodes-ultimate' )
						),
						'onclick' => array(
							'default' => '',
							'name' => __( 'onClick', 'shortcodes-ultimate' ),
							'desc' => __( 'Advanced JavaScript code for onClick action', 'shortcodes-ultimate' )
						),
						'rel' => array(
							'default' => '',
							'name' => __( 'Rel attribute', 'shortcodes-ultimate' ),
							'desc' => __( 'Here you can add value for the rel attribute.<br>Example values: <b%value>nofollow</b>, <b%value>lightbox</b>', 'shortcodes-ultimate' )
						),
						'title' => array(
							'default' => '',
							'name' => __( 'Title attribute', 'shortcodes-ultimate' ),
							'desc' => __( 'Here you can add value for the title attribute', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Button text', 'shortcodes-ultimate' ),
					'desc' => __( 'Styled button', 'shortcodes-ultimate' ),
					'example' => 'buttons',
					'icon' => 'heart'
				),
				// service
				'service' => array(
					'name' => __( 'Service', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'title' => array(
							'values' => array( ),
							'default' => __( 'Service title', 'shortcodes-ultimate' ),
							'name' => __( 'Title', 'shortcodes-ultimate' ),
							'desc' => __( 'Service name', 'shortcodes-ultimate' )
						),
						'icon' => array(
							'type' => 'icon',
							'default' => '',
							'name' => __( 'Icon', 'shortcodes-ultimate' ),
							'desc' => __( 'You can upload custom icon for this box', 'shortcodes-ultimate' )
						),
						'icon_color' => array(
							'type' => 'color',
							'default' => '#333333',
							'name' => __( 'Icon color', 'shortcodes-ultimate' ),
							'desc' => __( 'This color will be applied to the selected icon. Does not works with uploaded icons', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 128,
							'step' => 2,
							'default' => 32,
							'name' => __( 'Icon size', 'shortcodes-ultimate' ),
							'desc' => __( 'Size of the uploaded icon in pixels', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Service description', 'shortcodes-ultimate' ),
					'desc' => __( 'Service box with title', 'shortcodes-ultimate' ),
					'icon' => 'check-square-o'
				),
				// box
				'box' => array(
					'name' => __( 'Box', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'title' => array(
							'values' => array( ),
							'default' => __( 'Box title', 'shortcodes-ultimate' ),
							'name' => __( 'Title', 'shortcodes-ultimate' ), 'desc' => __( 'Text for the box title', 'shortcodes-ultimate' )
						),
						'style' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'soft' => __( 'Soft', 'shortcodes-ultimate' ),
								'glass' => __( 'Glass', 'shortcodes-ultimate' ),
								'bubbles' => __( 'Bubbles', 'shortcodes-ultimate' ),
								'noise' => __( 'Noise', 'shortcodes-ultimate' )
							),
							'default' => 'default',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Box style preset', 'shortcodes-ultimate' )
						),
						'box_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#333333',
							'name' => __( 'Color', 'shortcodes-ultimate' ),
							'desc' => __( 'Color for the box title and borders', 'shortcodes-ultimate' )
						),
						'title_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#FFFFFF',
							'name' => __( 'Title text color', 'shortcodes-ultimate' ), 'desc' => __( 'Color for the box title text', 'shortcodes-ultimate' )
						),
						'radius' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 20,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Radius', 'shortcodes-ultimate' ),
							'desc' => __( 'Box corners radius', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Box content', 'shortcodes-ultimate' ),
					'desc' => __( 'Colored box with caption', 'shortcodes-ultimate' ),
					'icon' => 'list-alt'
				),
				// note
				'note' => array(
					'name' => __( 'Note', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'note_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#FFFF66',
							'name' => __( 'Background', 'shortcodes-ultimate' ), 'desc' => __( 'Note background color', 'shortcodes-ultimate' )
						),
						'text_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#333333',
							'name' => __( 'Text color', 'shortcodes-ultimate' ),
							'desc' => __( 'Note text color', 'shortcodes-ultimate' )
						),
						'radius' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 20,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Radius', 'shortcodes-ultimate' ), 'desc' => __( 'Note corners radius', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Note text', 'shortcodes-ultimate' ),
					'desc' => __( 'Colored box', 'shortcodes-ultimate' ),
					'icon' => 'list-alt'
				),
				// expand
				'expand' => array(
					'name' => __( 'Expand', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'box',
					'atts' => array(
						'more_text' => array(
							'default' => __( 'Show more', 'shortcodes-ultimate' ),
							'name' => __( 'More text', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter the text for more link', 'shortcodes-ultimate' )
						),
						'less_text' => array(
							'default' => __( 'Show less', 'shortcodes-ultimate' ),
							'name' => __( 'Less text', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter the text for less link', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 1000,
							'step' => 10,
							'default' => 100,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Height for collapsed state (in pixels)', 'shortcodes-ultimate' )
						),
						'hide_less' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Hide less link', 'shortcodes-ultimate' ),
							'desc' => __( 'This option allows you to hide less link, when the text block has been expanded', 'shortcodes-ultimate' )
						),
						'text_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#333333',
							'name' => __( 'Text color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick the text color', 'shortcodes-ultimate' )
						),
						'link_color' => array(
							'type' => 'color',
							'values' => array( ),
							'default' => '#0088FF',
							'name' => __( 'Link color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick the link color', 'shortcodes-ultimate' )
						),
						'link_style' => array(
							'type' => 'select',
							'values' => array(
								'default'    => __( 'Default', 'shortcodes-ultimate' ),
								'underlined' => __( 'Underlined', 'shortcodes-ultimate' ),
								'dotted'     => __( 'Dotted', 'shortcodes-ultimate' ),
								'dashed'     => __( 'Dashed', 'shortcodes-ultimate' ),
								'button'     => __( 'Button', 'shortcodes-ultimate' ),
							),
							'default' => 'default',
							'name' => __( 'Link style', 'shortcodes-ultimate' ),
							'desc' => __( 'Select the style for more/less link', 'shortcodes-ultimate' )
						),
						'link_align' => array(
							'type' => 'select',
							'values' => array(
								'left' => __( 'Left', 'shortcodes-ultimate' ),
								'center' => __( 'Center', 'shortcodes-ultimate' ),
								'right' => __( 'Right', 'shortcodes-ultimate' ),
							),
							'default' => 'left',
							'name' => __( 'Link align', 'shortcodes-ultimate' ),
							'desc' => __( 'Select link alignment', 'shortcodes-ultimate' )
						),
						'more_icon' => array(
							'type' => 'icon',
							'default' => '',
							'name' => __( 'More icon', 'shortcodes-ultimate' ),
							'desc' => __( 'Add an icon to the more link', 'shortcodes-ultimate' )
						),
						'less_icon' => array(
							'type' => 'icon',
							'default' => '',
							'name' => __( 'Less icon', 'shortcodes-ultimate' ),
							'desc' => __( 'Add an icon to the less link', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'This text block can be expanded', 'shortcodes-ultimate' ),
					'desc' => __( 'Expandable text block', 'shortcodes-ultimate' ),
					'icon' => 'sort-amount-asc'
				),
				// lightbox
				'lightbox' => array(
					'name' => __( 'Lightbox', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'gallery',
					'atts' => array(
						'type' => array(
							'type' => 'select',
							'values' => array(
								'iframe' => __( 'Iframe', 'shortcodes-ultimate' ),
								'image' => __( 'Image', 'shortcodes-ultimate' ),
								'inline' => __( 'Inline (html content)', 'shortcodes-ultimate' )
							),
							'default' => 'iframe',
							'name' => __( 'Content type', 'shortcodes-ultimate' ),
							'desc' => __( 'Select type of the lightbox window content', 'shortcodes-ultimate' )
						),
						'src' => array(
							'default' => '',
							'name' => __( 'Content source', 'shortcodes-ultimate' ),
							'desc' => __( 'Insert here URL or CSS selector. Use URL for Iframe and Image content types. Use CSS selector for Inline content type.<br />Example values:<br /><b%value>http://www.youtube.com/watch?v=XXXXXXXXX</b> - YouTube video (iframe)<br /><b%value>http://example.com/wp-content/uploads/image.jpg</b> - uploaded image (image)<br /><b%value>http://example.com/</b> - any web page (iframe)<br /><b%value>#my-custom-popup</b> - any HTML content (inline)', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( '[%prefix_button] Click Here to Watch the Video [/%prefix_button]', 'shortcodes-ultimate' ),
					'desc' => __( 'Lightbox window with custom content', 'shortcodes-ultimate' ),
					'icon' => 'external-link'
				),
				// lightbox content
				'lightbox_content' => array(
					'name' => __( 'Lightbox content', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'gallery',
					'atts' => array(
						'id' => array(
							'default' => '',
							'name' => __( 'ID', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'Enter here the ID from Content source field. %s Example value: %s', 'shortcodes-ultimate' ), '<br>', '<b%value>my-custom-popup</b>' )
						),
						'width' => array(
							'default' => '50%',
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'Adjust the width for inline content (in pixels or percents). %s Example values: %s, %s, %s', 'shortcodes-ultimate' ), '<br>', '<b%value>300px</b>', '<b%value>600px</b>', '<b%value>90%</b>' )
						),
						'margin' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 600,
							'step' => 5,
							'default' => 40,
							'name' => __( 'Margin', 'shortcodes-ultimate' ),
							'desc' => __( 'Adjust the margin for inline content (in pixels)', 'shortcodes-ultimate' )
						),
						'padding' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 600,
							'step' => 5,
							'default' => 40,
							'name' => __( 'Padding', 'shortcodes-ultimate' ),
							'desc' => __( 'Adjust the padding for inline content (in pixels)', 'shortcodes-ultimate' )
						),
						'text_align' => array(
							'type' => 'select',
							'values' => array(
								'left'   => __( 'Left', 'shortcodes-ultimate' ),
								'center' => __( 'Center', 'shortcodes-ultimate' ),
								'right'  => __( 'Right', 'shortcodes-ultimate' )
							),
							'default' => 'center',
							'name' => __( 'Text alignment', 'shortcodes-ultimate' ),
							'desc' => __( 'Select the text alignment', 'shortcodes-ultimate' )
						),
						'background' => array(
							'type' => 'color',
							'default' => '#FFFFFF',
							'name' => __( 'Background color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick a background color', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'default' => '#333333',
							'name' => __( 'Text color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick a text color', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'default' => '#333333',
							'name' => __( 'Text color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick a text color', 'shortcodes-ultimate' )
						),
						'shadow' => array(
							'type' => 'shadow',
							'default' => '0px 0px 15px #333333',
							'name' => __( 'Shadow', 'shortcodes-ultimate' ),
							'desc' => __( 'Adjust the shadow for content box', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Inline content', 'shortcodes-ultimate' ),
					'desc' => __( 'Inline content for lightbox', 'shortcodes-ultimate' ),
					'icon' => 'external-link'
				),
				// tooltip
				'tooltip' => array(
					'name' => __( 'Tooltip', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'style' => array(
							'type' => 'select',
							'values' => array(
								'light' => __( 'Basic: Light', 'shortcodes-ultimate' ),
								'dark' => __( 'Basic: Dark', 'shortcodes-ultimate' ),
								'yellow' => __( 'Basic: Yellow', 'shortcodes-ultimate' ),
								'green' => __( 'Basic: Green', 'shortcodes-ultimate' ),
								'red' => __( 'Basic: Red', 'shortcodes-ultimate' ),
								'blue' => __( 'Basic: Blue', 'shortcodes-ultimate' ),
								'youtube' => __( 'Youtube', 'shortcodes-ultimate' ),
								'tipsy' => __( 'Tipsy', 'shortcodes-ultimate' ),
								'bootstrap' => __( 'Bootstrap', 'shortcodes-ultimate' ),
								'jtools' => __( 'jTools', 'shortcodes-ultimate' ),
								'tipped' => __( 'Tipped', 'shortcodes-ultimate' ),
								'cluetip' => __( 'Cluetip', 'shortcodes-ultimate' ),
							),
							'default' => 'yellow',
							'name' => __( 'Style', 'shortcodes-ultimate' ),
							'desc' => __( 'Tooltip window style', 'shortcodes-ultimate' )
						),
						'position' => array(
							'type' => 'select',
							'values' => array(
								'north' => __( 'Top', 'shortcodes-ultimate' ),
								'south' => __( 'Bottom', 'shortcodes-ultimate' ),
								'west' => __( 'Left', 'shortcodes-ultimate' ),
								'east' => __( 'Right', 'shortcodes-ultimate' )
							),
							'default' => 'top',
							'name' => __( 'Position', 'shortcodes-ultimate' ),
							'desc' => __( 'Tooltip position', 'shortcodes-ultimate' )
						),
						'shadow' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Shadow', 'shortcodes-ultimate' ),
							'desc' => __( 'Add shadow to tooltip. This option is only works with basic styes, e.g. blue, green etc.', 'shortcodes-ultimate' )
						),
						'rounded' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Rounded corners', 'shortcodes-ultimate' ),
							'desc' => __( 'Use rounded for tooltip. This option is only works with basic styes, e.g. blue, green etc.', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'select',
							'values' => array(
								'default' => __( 'Default', 'shortcodes-ultimate' ),
								'1' => 1,
								'2' => 2,
								'3' => 3,
								'4' => 4,
								'5' => 5,
								'6' => 6,
							),
							'default' => 'default',
							'name' => __( 'Font size', 'shortcodes-ultimate' ),
							'desc' => __( 'Tooltip font size', 'shortcodes-ultimate' )
						),
						'title' => array(
							'default' => '',
							'name' => __( 'Tooltip title', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter title for tooltip window. Leave this field empty to hide the title', 'shortcodes-ultimate' )
						),
						'content' => array(
							'default' => __( 'Tooltip text', 'shortcodes-ultimate' ),
							'name' => __( 'Tooltip content', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter tooltip content here', 'shortcodes-ultimate' )
						),
						'behavior' => array(
							'type' => 'select',
							'values' => array(
								'hover' => __( 'Show and hide on mouse hover', 'shortcodes-ultimate' ),
								'click' => __( 'Show and hide by mouse click', 'shortcodes-ultimate' ),
								'always' => __( 'Always visible', 'shortcodes-ultimate' )
							),
							'default' => 'hover',
							'name' => __( 'Behavior', 'shortcodes-ultimate' ),
							'desc' => __( 'Select tooltip behavior', 'shortcodes-ultimate' )
						),
						'close' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Close button', 'shortcodes-ultimate' ),
							'desc' => __( 'Show close button', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( '[%prefix_button] Hover me to open tooltip [/%prefix_button]', 'shortcodes-ultimate' ),
					'desc' => __( 'Tooltip window with custom content', 'shortcodes-ultimate' ),
					'icon' => 'comment-o'
				),
				// private
				'private' => array(
					'name' => __( 'Private', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Private note text', 'shortcodes-ultimate' ),
					'desc' => __( 'Private note for post authors', 'shortcodes-ultimate' ),
					'icon' => 'lock'
				),
				// youtube
				'youtube' => array(
					'name' => __( 'YouTube', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url of YouTube page with video. Ex: http://youtube.com/watch?v=XXXXXX', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make player responsive', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Play video automatically when page is loaded', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'YouTube video', 'shortcodes-ultimate' ),
					'example' => 'media',
					'icon' => 'youtube-play'
				),
				// youtube_advanced
				'youtube_advanced' => array(
					'name' => __( 'YouTube Advanced', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url of YouTube page with video. Ex: http://youtube.com/watch?v=XXXXXX', 'shortcodes-ultimate' )
						),
						'playlist' => array(
							'default' => '',
							'name' => __( 'Playlist', 'shortcodes-ultimate' ),
							'desc' => __( 'Value is a comma-separated list of video IDs to play. If you specify a value, the first video that plays will be the VIDEO_ID specified in the URL path, and the videos specified in the playlist parameter will play thereafter', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make player responsive', 'shortcodes-ultimate' )
						),
						'controls' => array(
							'type' => 'select',
							'values' => array(
								'no' => __( '0 - Hide controls', 'shortcodes-ultimate' ),
								'yes' => __( '1 - Show controls', 'shortcodes-ultimate' ),
								'alt' => __( '2 - Show controls when playback is started', 'shortcodes-ultimate' )
							),
							'default' => 'yes',
							'name' => __( 'Controls', 'shortcodes-ultimate' ),
							'desc' => __( 'This parameter indicates whether the video player controls will display', 'shortcodes-ultimate' )
						),
						'autohide' => array(
							'type' => 'select',
							'values' => array(
								'no' => __( '0 - Do not hide controls', 'shortcodes-ultimate' ),
								'yes' => __( '1 - Hide all controls on mouse out', 'shortcodes-ultimate' ),
								'alt' => __( '2 - Hide progress bar on mouse out', 'shortcodes-ultimate' )
							),
							'default' => 'alt',
							'name' => __( 'Autohide', 'shortcodes-ultimate' ),
							'desc' => __( 'This parameter indicates whether the video controls will automatically hide after a video begins playing', 'shortcodes-ultimate' )
						),
						'showinfo' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show title bar', 'shortcodes-ultimate' ),
							'desc' => __( 'If you set the parameter value to NO, then the player will not display information like the video title and uploader before the video starts playing.', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Play video automatically when page is loaded', 'shortcodes-ultimate' )
						),
						'loop' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Loop', 'shortcodes-ultimate' ),
							'desc' => __( 'Setting of YES will cause the player to play the initial video again and again', 'shortcodes-ultimate' )
						),
						'rel' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Related videos', 'shortcodes-ultimate' ),
							'desc' => __( 'This parameter indicates whether the player should show related videos when playback of the initial video ends', 'shortcodes-ultimate' )
						),
						'fs' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show full-screen button', 'shortcodes-ultimate' ),
							'desc' => __( 'Setting this parameter to NO prevents the fullscreen button from displaying', 'shortcodes-ultimate' )
						),
						'modestbranding' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => 'modestbranding',
							'desc' => __( 'This parameter lets you use a YouTube player that does not show a YouTube logo. Set the parameter value to YES to prevent the YouTube logo from displaying in the control bar. Note that a small YouTube text label will still display in the upper-right corner of a paused video when the user\'s mouse pointer hovers over the player', 'shortcodes-ultimate' )
						),
						'theme' => array(
							'type' => 'select',
							'values' => array(
								'dark' => __( 'Dark theme', 'shortcodes-ultimate' ),
								'light' => __( 'Light theme', 'shortcodes-ultimate' )
							),
							'default' => 'dark',
							'name' => __( 'Theme', 'shortcodes-ultimate' ),
							'desc' => __( 'This parameter indicates whether the embedded player will display player controls (like a play button or volume control) within a dark or light control bar', 'shortcodes-ultimate' )
						),
						'https' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Force HTTPS', 'shortcodes-ultimate' ),
							'desc' => __( 'Use HTTPS in player iframe', 'shortcodes-ultimate' )
						),
						'wmode' => array(
							'default' => '',
							'name'    => __( 'WMode', 'shortcodes-ultimate' ),
							'desc'    => sprintf( __( 'Here you can specify wmode value for the embed URL. %s Example values: %s, %s', 'shortcodes-ultimate' ), '<br>', '<b%value>transparent</b>', '<b%value>opaque</b>' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'YouTube video player with advanced settings', 'shortcodes-ultimate' ),
					'example' => 'media',
					'icon' => 'youtube-play'
				),
				// vimeo
				'vimeo' => array(
					'name' => __( 'Vimeo', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ), 'desc' => __( 'Url of Vimeo page with video', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make player responsive', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Play video automatically when page is loaded', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Vimeo video', 'shortcodes-ultimate' ),
					'example' => 'media',
					'icon' => 'youtube-play'
				),
				// screenr
				'screenr' => array(
					'name' => __( 'Screenr', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url of Screenr page with video', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make player responsive', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Screenr video', 'shortcodes-ultimate' ),
					'icon' => 'youtube-play'
				),
				// dailymotion
				'dailymotion' => array(
					'name' => __( 'Dailymotion', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url of Dailymotion page with video', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make player responsive', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Start the playback of the video automatically after the player load. May not work on some mobile OS versions', 'shortcodes-ultimate' )
						),
						'background' => array(
							'type' => 'color',
							'default' => '#FFC300',
							'name' => __( 'Background color', 'shortcodes-ultimate' ),
							'desc' => __( 'HTML color of the background of controls elements', 'shortcodes-ultimate' )
						),
						'foreground' => array(
							'type' => 'color',
							'default' => '#F7FFFD',
							'name' => __( 'Foreground color', 'shortcodes-ultimate' ),
							'desc' => __( 'HTML color of the foreground of controls elements', 'shortcodes-ultimate' )
						),
						'highlight' => array(
							'type' => 'color',
							'default' => '#171D1B',
							'name' => __( 'Highlight color', 'shortcodes-ultimate' ),
							'desc' => __( 'HTML color of the controls elements\' highlights', 'shortcodes-ultimate' )
						),
						'logo' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show logo', 'shortcodes-ultimate' ),
							'desc' => __( 'Allows to hide or show the Dailymotion logo', 'shortcodes-ultimate' )
						),
						'quality' => array(
							'type' => 'select',
							'values' => array(
								'240'  => '240',
								'380'  => '380',
								'480'  => '480',
								'720'  => '720',
								'1080' => '1080'
							),
							'default' => '380',
							'name' => __( 'Quality', 'shortcodes-ultimate' ),
							'desc' => __( 'Determines the quality that must be played by default if available', 'shortcodes-ultimate' )
						),
						'related' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show related videos', 'shortcodes-ultimate' ),
							'desc' => __( 'Show related videos at the end of the video', 'shortcodes-ultimate' )
						),
						'info' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show video info', 'shortcodes-ultimate' ),
							'desc' => __( 'Show videos info (title/author) on the start screen', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Dailymotion video', 'shortcodes-ultimate' ),
					'icon' => 'youtube-play'
				),
				// audio
				'audio' => array(
					'name' => __( 'Audio', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'type' => 'upload',
							'default' => '',
							'name' => __( 'File', 'shortcodes-ultimate' ),
							'desc' => __( 'Audio file url. Supported formats: mp3, ogg', 'shortcodes-ultimate' )
						),
						'width' => array(
							'values' => array(),
							'default' => '100%',
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width. You can specify width in percents and player will be responsive. Example values: <b%value>200px</b>, <b%value>100&#37;</b>', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Play file automatically when page is loaded', 'shortcodes-ultimate' )
						),
						'loop' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Loop', 'shortcodes-ultimate' ),
							'desc' => __( 'Repeat when playback is ended', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Custom audio player', 'shortcodes-ultimate' ),
					'example' => 'media',
					'icon' => 'play-circle'
				),
				// video
				'video' => array(
					'name' => __( 'Video', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'type' => 'upload',
							'default' => '',
							'name' => __( 'File', 'shortcodes-ultimate' ),
							'desc' => __( 'Url to mp4/flv video-file', 'shortcodes-ultimate' )
						),
						'poster' => array(
							'type' => 'upload',
							'default' => '',
							'name' => __( 'Poster', 'shortcodes-ultimate' ),
							'desc' => __( 'Url to poster image, that will be shown before playback', 'shortcodes-ultimate' )
						),
						'title' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Title', 'shortcodes-ultimate' ),
							'desc' => __( 'Player title', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Player width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 300,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Player height', 'shortcodes-ultimate' )
						),
						'controls' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Controls', 'shortcodes-ultimate' ),
							'desc' => __( 'Show player controls (play/pause etc.) or not', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Play file automatically when page is loaded', 'shortcodes-ultimate' )
						),
						'loop' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Loop', 'shortcodes-ultimate' ),
							'desc' => __( 'Repeat when playback is ended', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Custom video player', 'shortcodes-ultimate' ),
					'example' => 'media',
					'icon' => 'play-circle'
				),
				// table
				'table' => array(
					'name' => __( 'Table', 'shortcodes-ultimate' ),
					'type' => 'mixed',
					'group' => 'content',
					'atts' => array(
						'url' => array(
							'type' => 'upload',
							'default' => '',
							'name' => __( 'CSV file', 'shortcodes-ultimate' ),
							'desc' => __( 'Upload CSV file if you want to create HTML-table from file', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( "<table>\n<tr>\n\t<td>Table</td>\n\t<td>Table</td>\n</tr>\n<tr>\n\t<td>Table</td>\n\t<td>Table</td>\n</tr>\n</table>", 'shortcodes-ultimate' ),
					'desc' => __( 'Styled table from HTML or CSV file', 'shortcodes-ultimate' ),
					'icon' => 'table'
				),
				// permalink
				'permalink' => array(
					'name' => __( 'Permalink', 'shortcodes-ultimate' ),
					'type' => 'mixed',
					'group' => 'content other',
					'atts' => array(
						'id' => array(
							'values' => array( ), 'default' => 1,
							'name' => __( 'ID', 'shortcodes-ultimate' ),
							'desc' => __( 'Post or page ID', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Same tab', 'shortcodes-ultimate' ),
								'blank' => __( 'New tab', 'shortcodes-ultimate' )
							),
							'default' => 'self',
							'name' => __( 'Target', 'shortcodes-ultimate' ),
							'desc' => __( 'Link target. blank - link will be opened in new window/tab', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => '',
					'desc' => __( 'Permalink to specified post/page', 'shortcodes-ultimate' ),
					'icon' => 'link'
				),
				// members
				'members' => array(
					'name' => __( 'Members', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'message' => array(
							'default' => __( 'This content is for registered users only. Please %login%.', 'shortcodes-ultimate' ),
							'name' => __( 'Message', 'shortcodes-ultimate' ), 'desc' => __( 'Message for not logged users', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'default' => '#ffcc00',
							'name' => __( 'Box color', 'shortcodes-ultimate' ), 'desc' => __( 'This color will applied only to box for not logged users', 'shortcodes-ultimate' )
						),
						'login_text' => array(
							'default' => __( 'login', 'shortcodes-ultimate' ),
							'name' => __( 'Login link text', 'shortcodes-ultimate' ), 'desc' => __( 'Text for the login link', 'shortcodes-ultimate' )
						),
						'login_url' => array(
							'default' => wp_login_url(),
							'name' => __( 'Login link url', 'shortcodes-ultimate' ), 'desc' => __( 'Login link url', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Content for logged members', 'shortcodes-ultimate' ),
					'desc' => __( 'Content for logged in members only', 'shortcodes-ultimate' ),
					'icon' => 'lock'
				),
				// guests
				'guests' => array(
					'name' => __( 'Guests', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Content for guests', 'shortcodes-ultimate' ),
					'desc' => __( 'Content for guests only', 'shortcodes-ultimate' ),
					'icon' => 'user'
				),
				// feed
				'feed' => array(
					'name' => __( 'RSS Feed', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content other',
					'atts' => array(
						'url' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url to RSS-feed', 'shortcodes-ultimate' )
						),
						'limit' => array(
							'type' => 'slider',
							'min' => 1,
							'max' => 20,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Limit', 'shortcodes-ultimate' ), 'desc' => __( 'Number of items to show', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Feed grabber', 'shortcodes-ultimate' ),
					'icon' => 'rss'
				),
				// menu
				'menu' => array(
					'name' => __( 'Menu', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'other',
					'atts' => array(
						'name' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Menu name', 'shortcodes-ultimate' ), 'desc' => __( 'Custom menu name. Ex: Main menu', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Custom menu by name', 'shortcodes-ultimate' ),
					'icon' => 'bars'
				),
				// subpages
				'subpages' => array(
					'name' => __( 'Sub pages', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'other',
					'atts' => array(
						'depth' => array(
							'type' => 'select',
							'values' => array( 1, 2, 3, 4, 5 ), 'default' => 1,
							'name' => __( 'Depth', 'shortcodes-ultimate' ),
							'desc' => __( 'Max depth level of children pages', 'shortcodes-ultimate' )
						),
						'p' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Parent ID', 'shortcodes-ultimate' ),
							'desc' => __( 'ID of the parent page. Leave blank to use current page', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'List of sub pages', 'shortcodes-ultimate' ),
					'icon' => 'bars'
				),
				// siblings
				'siblings' => array(
					'name' => __( 'Siblings', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'other',
					'atts' => array(
						'depth' => array(
							'type' => 'select',
							'values' => array( 1, 2, 3 ), 'default' => 1,
							'name' => __( 'Depth', 'shortcodes-ultimate' ),
							'desc' => __( 'Max depth level', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'List of cureent page siblings', 'shortcodes-ultimate' ),
					'icon' => 'bars'
				),
				// document
				'document' => array(
					'name' => __( 'Document', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'url' => array(
							'type' => 'upload',
							'default' => '',
							'name' => __( 'Url', 'shortcodes-ultimate' ),
							'desc' => __( 'Url to uploaded document. Supported formats: doc, xls, pdf etc.', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Viewer width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Viewer height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make viewer responsive', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Document viewer by Google', 'shortcodes-ultimate' ),
					'icon' => 'file-text'
				),
				// gmap
				'gmap' => array(
					'name' => __( 'Gmap', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'media',
					'atts' => array(
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Map width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 400,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Map height', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make map responsive', 'shortcodes-ultimate' )
						),
						'address' => array(
							'values' => array( ),
							'default' => '',
							'name' => __( 'Marker', 'shortcodes-ultimate' ),
							'desc' => __( 'Address for the marker. You can type it in any language', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Maps by Google', 'shortcodes-ultimate' ),
					'icon' => 'globe'
				),
				// slider
				'slider' => array(
					'name' => __( 'Slider', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'gallery',
					'atts' => array(
						'source' => array(
							'type'    => 'image_source',
							'default' => 'none',
							'name'    => __( 'Source', 'shortcodes-ultimate' ),
							'desc'    => __( 'Choose images source. You can use images from Media library or retrieve it from posts (thumbnails) posted under specified blog category. You can also pick any custom taxonomy', 'shortcodes-ultimate' )
						),
						'limit' => array(
							'type' => 'slider',
							'min' => -1,
							'max' => 100,
							'step' => 1,
							'default' => 20,
							'name' => __( 'Limit', 'shortcodes-ultimate' ),
							'desc' => __( 'Maximum number of image source posts (for recent posts, category and custom taxonomy)', 'shortcodes-ultimate' )
						),
						'link' => array(
							'type' => 'select',
							'values' => array(
								'none'       => __( 'None', 'shortcodes-ultimate' ),
								'image'      => __( 'Full-size image', 'shortcodes-ultimate' ),
								'lightbox'   => __( 'Lightbox', 'shortcodes-ultimate' ),
								'custom'     => __( 'Slide link (added in media editor)', 'shortcodes-ultimate' ),
								'attachment' => __( 'Attachment page', 'shortcodes-ultimate' ),
								'post'       => __( 'Post permalink', 'shortcodes-ultimate' )
							),
							'default' => 'none',
							'name' => __( 'Links', 'shortcodes-ultimate' ),
							'desc' => __( 'Select which links will be used for images in this gallery', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Same window', 'shortcodes-ultimate' ),
								'blank' => __( 'New window', 'shortcodes-ultimate' )
							),
							'default' => 'self',
							'name' => __( 'Links target', 'shortcodes-ultimate' ),
							'desc' => __( 'Open links in', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ), 'desc' => __( 'Slider width (in pixels)', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 200,
							'max' => 1600,
							'step' => 20,
							'default' => 300,
							'name' => __( 'Height', 'shortcodes-ultimate' ), 'desc' => __( 'Slider height (in pixels)', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make slider responsive', 'shortcodes-ultimate' )
						),
						'title' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show titles', 'shortcodes-ultimate' ), 'desc' => __( 'Display slide titles', 'shortcodes-ultimate' )
						),
						'centered' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Center', 'shortcodes-ultimate' ), 'desc' => __( 'Is slider centered on the page', 'shortcodes-ultimate' )
						),
						'arrows' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Arrows', 'shortcodes-ultimate' ), 'desc' => __( 'Show left and right arrows', 'shortcodes-ultimate' )
						),
						'pages' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Pagination', 'shortcodes-ultimate' ),
							'desc' => __( 'Show pagination', 'shortcodes-ultimate' )
						),
						'mousewheel' => array(
							'type' => 'bool',
							'default' => 'yes', 'name' => __( 'Mouse wheel control', 'shortcodes-ultimate' ),
							'desc' => __( 'Allow to change slides with mouse wheel', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'number',
							'min' => 0,
							'max' => 100000,
							'step' => 100,
							'default' => 5000,
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose interval between slide animations. Set to 0 to disable autoplay', 'shortcodes-ultimate' )
						),
						'speed' => array(
							'type' => 'number',
							'min' => 0,
							'max' => 20000,
							'step' => 100,
							'default' => 600,
							'name' => __( 'Speed', 'shortcodes-ultimate' ), 'desc' => __( 'Specify animation speed', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Customizable image slider', 'shortcodes-ultimate' ),
					'icon' => 'picture-o'
				),
				// carousel
				'carousel' => array(
					'name' => __( 'Carousel', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'gallery',
					'atts' => array(
						'source' => array(
							'type'    => 'image_source',
							'default' => 'none',
							'name'    => __( 'Source', 'shortcodes-ultimate' ),
							'desc'    => __( 'Choose images source. You can use images from Media library or retrieve it from posts (thumbnails) posted under specified blog category. You can also pick any custom taxonomy', 'shortcodes-ultimate' )
						),
						'limit' => array(
							'type' => 'slider',
							'min' => -1,
							'max' => 100,
							'step' => 1,
							'default' => 20,
							'name' => __( 'Limit', 'shortcodes-ultimate' ),
							'desc' => __( 'Maximum number of image source posts (for recent posts, category and custom taxonomy)', 'shortcodes-ultimate' )
						),
						'link' => array(
							'type' => 'select',
							'values' => array(
								'none'       => __( 'None', 'shortcodes-ultimate' ),
								'image'      => __( 'Full-size image', 'shortcodes-ultimate' ),
								'lightbox'   => __( 'Lightbox', 'shortcodes-ultimate' ),
								'custom'     => __( 'Slide link (added in media editor)', 'shortcodes-ultimate' ),
								'attachment' => __( 'Attachment page', 'shortcodes-ultimate' ),
								'post'       => __( 'Post permalink', 'shortcodes-ultimate' )
							),
							'default' => 'none',
							'name' => __( 'Links', 'shortcodes-ultimate' ),
							'desc' => __( 'Select which links will be used for images in this gallery', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Same window', 'shortcodes-ultimate' ),
								'blank' => __( 'New window', 'shortcodes-ultimate' )
							),
							'default' => 'self',
							'name' => __( 'Links target', 'shortcodes-ultimate' ),
							'desc' => __( 'Open links in', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 100,
							'max' => 1600,
							'step' => 20,
							'default' => 600,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Carousel width (in pixels)', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 20,
							'max' => 1600,
							'step' => 20,
							'default' => 100,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Carousel height (in pixels)', 'shortcodes-ultimate' )
						),
						'responsive' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Responsive', 'shortcodes-ultimate' ),
							'desc' => __( 'Ignore width and height parameters and make carousel responsive', 'shortcodes-ultimate' )
						),
						'items' => array(
							'type' => 'number',
							'min' => 1,
							'max' => 20,
							'step' => 1,
							'default' => 3,
							'name' => __( 'Items to show', 'shortcodes-ultimate' ),
							'desc' => __( 'How much carousel items is visible', 'shortcodes-ultimate' )
						),
						'scroll' => array(
							'type' => 'number',
							'min' => 1,
							'max' => 20,
							'step' => 1, 'default' => 1,
							'name' => __( 'Scroll number', 'shortcodes-ultimate' ),
							'desc' => __( 'How much items are scrolled in one transition', 'shortcodes-ultimate' )
						),
						'title' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Show titles', 'shortcodes-ultimate' ), 'desc' => __( 'Display titles for each item', 'shortcodes-ultimate' )
						),
						'centered' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Center', 'shortcodes-ultimate' ), 'desc' => __( 'Is carousel centered on the page', 'shortcodes-ultimate' )
						),
						'arrows' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Arrows', 'shortcodes-ultimate' ), 'desc' => __( 'Show left and right arrows', 'shortcodes-ultimate' )
						),
						'pages' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Pagination', 'shortcodes-ultimate' ),
							'desc' => __( 'Show pagination', 'shortcodes-ultimate' )
						),
						'mousewheel' => array(
							'type' => 'bool',
							'default' => 'yes', 'name' => __( 'Mouse wheel control', 'shortcodes-ultimate' ),
							'desc' => __( 'Allow to rotate carousel with mouse wheel', 'shortcodes-ultimate' )
						),
						'autoplay' => array(
							'type' => 'number',
							'min' => 0,
							'max' => 100000,
							'step' => 100,
							'default' => 5000,
							'name' => __( 'Autoplay', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose interval between auto animations. Set to 0 to disable autoplay', 'shortcodes-ultimate' )
						),
						'speed' => array(
							'type' => 'number',
							'min' => 0,
							'max' => 20000,
							'step' => 100,
							'default' => 600,
							'name' => __( 'Speed', 'shortcodes-ultimate' ), 'desc' => __( 'Specify animation speed', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Customizable image carousel', 'shortcodes-ultimate' ),
					'icon' => 'picture-o'
				),
				// custom_gallery
				'custom_gallery' => array(
					'name' => __( 'Gallery', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'gallery',
					'atts' => array(
						'source' => array(
							'type'    => 'image_source',
							'default' => 'none',
							'name'    => __( 'Source', 'shortcodes-ultimate' ),
							'desc'    => __( 'Choose images source. You can use images from Media library or retrieve it from posts (thumbnails) posted under specified blog category. You can also pick any custom taxonomy', 'shortcodes-ultimate' )
						),
						'limit' => array(
							'type' => 'slider',
							'min' => -1,
							'max' => 100,
							'step' => 1,
							'default' => 20,
							'name' => __( 'Limit', 'shortcodes-ultimate' ),
							'desc' => __( 'Maximum number of image source posts (for recent posts, category and custom taxonomy)', 'shortcodes-ultimate' )
						),
						'link' => array(
							'type' => 'select',
							'values' => array(
								'none'       => __( 'None', 'shortcodes-ultimate' ),
								'image'      => __( 'Full-size image', 'shortcodes-ultimate' ),
								'lightbox'   => __( 'Lightbox', 'shortcodes-ultimate' ),
								'custom'     => __( 'Slide link (added in media editor)', 'shortcodes-ultimate' ),
								'attachment' => __( 'Attachment page', 'shortcodes-ultimate' ),
								'post'       => __( 'Post permalink', 'shortcodes-ultimate' )
							),
							'default' => 'none',
							'name' => __( 'Links', 'shortcodes-ultimate' ),
							'desc' => __( 'Select which links will be used for images in this gallery', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Same window', 'shortcodes-ultimate' ),
								'blank' => __( 'New window', 'shortcodes-ultimate' )
							),
							'default' => 'self',
							'name' => __( 'Links target', 'shortcodes-ultimate' ),
							'desc' => __( 'Open links in', 'shortcodes-ultimate' )
						),
						'width' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 1600,
							'step' => 10,
							'default' => 90,
							'name' => __( 'Width', 'shortcodes-ultimate' ), 'desc' => __( 'Single item width (in pixels)', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 1600,
							'step' => 10,
							'default' => 90,
							'name' => __( 'Height', 'shortcodes-ultimate' ), 'desc' => __( 'Single item height (in pixels)', 'shortcodes-ultimate' )
						),
						'title' => array(
							'type' => 'select',
							'values' => array(
								'never' => __( 'Never', 'shortcodes-ultimate' ),
								'hover' => __( 'On mouse over', 'shortcodes-ultimate' ),
								'always' => __( 'Always', 'shortcodes-ultimate' )
							),
							'default' => 'hover',
							'name' => __( 'Show titles', 'shortcodes-ultimate' ),
							'desc' => __( 'Title display mode', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Customizable image gallery', 'shortcodes-ultimate' ),
					'icon' => 'picture-o'
				),
				// posts
				'posts' => array(
					'name' => __( 'Posts', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'other',
					'atts' => array(
						'template' => array(
							'default' => 'templates/default-loop.php', 'name' => __( 'Template', 'shortcodes-ultimate' ),
							'desc' => __( '<b>Do not change this field value if you do not understand description below.</b><br/>Relative path to the template file. Default templates is placed under the plugin directory (templates folder). You can copy it under your theme directory and modify as you want. You can use following default templates that already available in the plugin directory:<br/><b%value>templates/default-loop.php</b> - posts loop<br/><b%value>templates/teaser-loop.php</b> - posts loop with thumbnail and title<br/><b%value>templates/single-post.php</b> - single post template<br/><b%value>templates/list-loop.php</b> - unordered list with posts titles', 'shortcodes-ultimate' )
						),
						'id' => array(
							'default' => '',
							'name' => __( 'Post ID\'s', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter comma separated ID\'s of the posts that you want to show', 'shortcodes-ultimate' )
						),
						'posts_per_page' => array(
							'type' => 'number',
							'min' => -1,
							'max' => 10000,
							'step' => 1,
							'default' => get_option( 'posts_per_page' ),
							'name' => __( 'Posts per page', 'shortcodes-ultimate' ),
							'desc' => __( 'Specify number of posts that you want to show. Enter -1 to get all posts', 'shortcodes-ultimate' )
						),
						'post_type' => array(
							'type' => 'select',
							'multiple' => true,
							'values' => Su_Tools::get_types(),
							'default' => 'post',
							'name' => __( 'Post types', 'shortcodes-ultimate' ),
							'desc' => __( 'Select post types. Hold Ctrl key to select multiple post types', 'shortcodes-ultimate' )
						),
						'taxonomy' => array(
							'type' => 'select',
							'values' => Su_Tools::get_taxonomies(),
							'default' => 'category',
							'name' => __( 'Taxonomy', 'shortcodes-ultimate' ),
							'desc' => __( 'Select taxonomy to show posts from', 'shortcodes-ultimate' )
						),
						'tax_term' => array(
							'type' => 'select',
							'multiple' => true,
							'values' => Su_Tools::get_terms( 'category' ),
							'default' => '',
							'name' => __( 'Terms', 'shortcodes-ultimate' ),
							'desc' => __( 'Select terms to show posts from', 'shortcodes-ultimate' )
						),
						'tax_operator' => array(
							'type' => 'select',
							'values' => array( 'IN', 'NOT IN', 'AND' ),
							'default' => 'IN', 'name' => __( 'Taxonomy term operator', 'shortcodes-ultimate' ),
							'desc' => __( 'IN - posts that have any of selected categories terms<br/>NOT IN - posts that is does not have any of selected terms<br/>AND - posts that have all selected terms', 'shortcodes-ultimate' )
						),
						// 'author' => array(
						// 	'type' => 'select',
						// 	'multiple' => true,
						// 	'values' => Su_Tools::get_users(),
						// 	'default' => 'default',
						// 	'name' => __( 'Authors', 'shortcodes-ultimate' ),
						// 	'desc' => __( 'Choose the authors whose posts you want to show. Enter here comma-separated list of users (IDs). Example: 1,7,18', 'shortcodes-ultimate' )
						// ),
						'author' => array(
							'default' => '',
							'name' => __( 'Authors', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter here comma-separated list of author\'s IDs. Example: 1,7,18', 'shortcodes-ultimate' )
						),
						'meta_key' => array(
							'default' => '',
							'name' => __( 'Meta key', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter meta key name to show posts that have this key', 'shortcodes-ultimate' )
						),
						'offset' => array(
							'type' => 'number',
							'min' => 0,
							'max' => 10000,
							'step' => 1, 'default' => 0,
							'name' => __( 'Offset', 'shortcodes-ultimate' ),
							'desc' => __( 'Specify offset to start posts loop not from first post', 'shortcodes-ultimate' )
						),
						'order' => array(
							'type' => 'select',
							'values' => array(
								'desc' => __( 'Descending', 'shortcodes-ultimate' ),
								'asc' => __( 'Ascending', 'shortcodes-ultimate' )
							),
							'default' => 'DESC',
							'name' => __( 'Order', 'shortcodes-ultimate' ),
							'desc' => __( 'Posts order', 'shortcodes-ultimate' )
						),
						'orderby' => array(
							'type' => 'select',
							'values' => array(
								'none' => __( 'None', 'shortcodes-ultimate' ),
								'id' => __( 'Post ID', 'shortcodes-ultimate' ),
								'author' => __( 'Post author', 'shortcodes-ultimate' ),
								'title' => __( 'Post title', 'shortcodes-ultimate' ),
								'name' => __( 'Post slug', 'shortcodes-ultimate' ),
								'date' => __( 'Date', 'shortcodes-ultimate' ), 'modified' => __( 'Last modified date', 'shortcodes-ultimate' ),
								'parent' => __( 'Post parent', 'shortcodes-ultimate' ),
								'rand' => __( 'Random', 'shortcodes-ultimate' ), 'comment_count' => __( 'Comments number', 'shortcodes-ultimate' ),
								'menu_order' => __( 'Menu order', 'shortcodes-ultimate' ), 'meta_value' => __( 'Meta key values', 'shortcodes-ultimate' ),
							),
							'default' => 'date',
							'name' => __( 'Order by', 'shortcodes-ultimate' ),
							'desc' => __( 'Order posts by', 'shortcodes-ultimate' )
						),
						'post_parent' => array(
							'default' => '',
							'name' => __( 'Post parent', 'shortcodes-ultimate' ),
							'desc' => __( 'Show childrens of entered post (enter post ID)', 'shortcodes-ultimate' )
						),
						'post_status' => array(
							'type' => 'select',
							'values' => array(
								'publish' => __( 'Published', 'shortcodes-ultimate' ),
								'pending' => __( 'Pending', 'shortcodes-ultimate' ),
								'draft' => __( 'Draft', 'shortcodes-ultimate' ),
								'auto-draft' => __( 'Auto-draft', 'shortcodes-ultimate' ),
								'future' => __( 'Future post', 'shortcodes-ultimate' ),
								'private' => __( 'Private post', 'shortcodes-ultimate' ),
								'inherit' => __( 'Inherit', 'shortcodes-ultimate' ),
								'trash' => __( 'Trashed', 'shortcodes-ultimate' ),
								'any' => __( 'Any', 'shortcodes-ultimate' ),
							),
							'default' => 'publish',
							'name' => __( 'Post status', 'shortcodes-ultimate' ),
							'desc' => __( 'Show only posts with selected status', 'shortcodes-ultimate' )
						),
						'ignore_sticky_posts' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Ignore sticky', 'shortcodes-ultimate' ),
							'desc' => __( 'Select Yes to ignore posts that is sticked', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Custom posts query with customizable template', 'shortcodes-ultimate' ),
					'icon' => 'th-list'
				),
				// dummy_text
				'dummy_text' => array(
					'name' => __( 'Dummy text', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content',
					'atts' => array(
						'what' => array(
							'type' => 'select',
							'values' => array(
								'paras' => __( 'Paragraphs', 'shortcodes-ultimate' ),
								'words' => __( 'Words', 'shortcodes-ultimate' ),
								'bytes' => __( 'Bytes', 'shortcodes-ultimate' ),
							),
							'default' => 'paras',
							'name' => __( 'What', 'shortcodes-ultimate' ),
							'desc' => __( 'What to generate', 'shortcodes-ultimate' )
						),
						'amount' => array(
							'type' => 'slider',
							'min' => 1,
							'max' => 100,
							'step' => 1,
							'default' => 1,
							'name' => __( 'Amount', 'shortcodes-ultimate' ),
							'desc' => __( 'How many items (paragraphs or words) to generate. Minimum words amount is 5', 'shortcodes-ultimate' )
						),
						'cache' => array(
							'type' => 'bool',
							'default' => 'yes',
							'name' => __( 'Cache', 'shortcodes-ultimate' ),
							'desc' => __( 'Generated text will be cached. Be careful with this option. If you disable it and insert many dummy_text shortcodes the page load time will be highly increased', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Text placeholder', 'shortcodes-ultimate' ),
					'icon' => 'text-height'
				),
				// dummy_image
				'dummy_image' => array(
					'name' => __( 'Dummy image', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content',
					'atts' => array(
						'width' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 1600,
							'step' => 10,
							'default' => 500,
							'name' => __( 'Width', 'shortcodes-ultimate' ),
							'desc' => __( 'Image width', 'shortcodes-ultimate' )
						),
						'height' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 1600,
							'step' => 10,
							'default' => 300,
							'name' => __( 'Height', 'shortcodes-ultimate' ),
							'desc' => __( 'Image height', 'shortcodes-ultimate' )
						),
						'theme' => array(
							'type' => 'select',
							'values' => array(
								'any'       => __( 'Any', 'shortcodes-ultimate' ),
								'abstract'  => __( 'Abstract', 'shortcodes-ultimate' ),
								'animals'   => __( 'Animals', 'shortcodes-ultimate' ),
								'business'  => __( 'Business', 'shortcodes-ultimate' ),
								'cats'      => __( 'Cats', 'shortcodes-ultimate' ),
								'city'      => __( 'City', 'shortcodes-ultimate' ),
								'food'      => __( 'Food', 'shortcodes-ultimate' ),
								'nightlife' => __( 'Night life', 'shortcodes-ultimate' ),
								'fashion'   => __( 'Fashion', 'shortcodes-ultimate' ),
								'people'    => __( 'People', 'shortcodes-ultimate' ),
								'nature'    => __( 'Nature', 'shortcodes-ultimate' ),
								'sports'    => __( 'Sports', 'shortcodes-ultimate' ),
								'technics'  => __( 'Technics', 'shortcodes-ultimate' ),
								'transport' => __( 'Transport', 'shortcodes-ultimate' )
							),
							'default' => 'any',
							'name' => __( 'Theme', 'shortcodes-ultimate' ),
							'desc' => __( 'Select the theme for this image', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Image placeholder with random image', 'shortcodes-ultimate' ),
					'icon' => 'picture-o'
				),
				// animate
				'animate' => array(
					'name' => __( 'Animation', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'type' => array(
							'type' => 'select',
							'values' => array_combine( self::animations(), self::animations() ),
							'default' => 'bounceIn',
							'name' => __( 'Animation', 'shortcodes-ultimate' ),
							'desc' => __( 'Select animation type', 'shortcodes-ultimate' )
						),
						'duration' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 20,
							'step' => 0.5,
							'default' => 1,
							'name' => __( 'Duration', 'shortcodes-ultimate' ),
							'desc' => __( 'Animation duration (seconds)', 'shortcodes-ultimate' )
						),
						'delay' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 20,
							'step' => 0.5,
							'default' => 0,
							'name' => __( 'Delay', 'shortcodes-ultimate' ),
							'desc' => __( 'Animation delay (seconds)', 'shortcodes-ultimate' )
						),
						'inline' => array(
							'type' => 'bool',
							'default' => 'no',
							'name' => __( 'Inline', 'shortcodes-ultimate' ),
							'desc' => __( 'This parameter determines what HTML tag will be used for animation wrapper. Turn this option to YES and animated element will be wrapped in SPAN instead of DIV. Useful for inline animations, like buttons', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Animated content', 'shortcodes-ultimate' ),
					'desc' => __( 'Wrapper for animation. Any nested element will be animated', 'shortcodes-ultimate' ),
					'example' => 'animations',
					'icon' => 'bolt'
				),
				// meta
				'meta' => array(
					'name' => __( 'Meta', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'data',
					'atts' => array(
						'key' => array(
							'default' => '',
							'name' => __( 'Key', 'shortcodes-ultimate' ),
							'desc' => __( 'Meta key name', 'shortcodes-ultimate' )
						),
						'default' => array(
							'default' => '',
							'name' => __( 'Default', 'shortcodes-ultimate' ),
							'desc' => __( 'This text will be shown if data is not found', 'shortcodes-ultimate' )
						),
						'before' => array(
							'default' => '',
							'name' => __( 'Before', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown before the value', 'shortcodes-ultimate' )
						),
						'after' => array(
							'default' => '',
							'name' => __( 'After', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown after the value', 'shortcodes-ultimate' )
						),
						'post_id' => array(
							'default' => '',
							'name' => __( 'Post ID', 'shortcodes-ultimate' ),
							'desc' => __( 'You can specify custom post ID. Leave this field empty to use an ID of the current post. Current post ID may not work in Live Preview mode', 'shortcodes-ultimate' )
						),
						'filter' => array(
							'default' => '',
							'name' => __( 'Filter', 'shortcodes-ultimate' ),
							'desc' => __( 'You can apply custom filter to the retrieved value. Enter here function name. Your function must accept one argument and return modified value. Example function: ', 'shortcodes-ultimate' ) . "<br /><pre><code style='display:block;padding:5px'>function my_custom_filter( \$value ) {\n\treturn 'Value is: ' . \$value;\n}</code></pre>"
						)
					),
					'desc' => __( 'Post meta', 'shortcodes-ultimate' ),
					'icon' => 'info-circle'
				),
				// user
				'user' => array(
					'name' => __( 'User', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'data',
					'atts' => array(
						'field' => array(
							'type' => 'select',
							'values' => array(
								'display_name'        => __( 'Display name', 'shortcodes-ultimate' ),
								'ID'                  => __( 'ID', 'shortcodes-ultimate' ),
								'user_login'          => __( 'Login', 'shortcodes-ultimate' ),
								'user_nicename'       => __( 'Nice name', 'shortcodes-ultimate' ),
								'user_email'          => __( 'Email', 'shortcodes-ultimate' ),
								'user_url'            => __( 'URL', 'shortcodes-ultimate' ),
								'user_registered'     => __( 'Registered', 'shortcodes-ultimate' ),
								'user_activation_key' => __( 'Activation key', 'shortcodes-ultimate' ),
								'user_status'         => __( 'Status', 'shortcodes-ultimate' )
							),
							'default' => 'display_name',
							'name' => __( 'Field', 'shortcodes-ultimate' ),
							'desc' => __( 'User data field name', 'shortcodes-ultimate' )
						),
						'default' => array(
							'default' => '',
							'name' => __( 'Default', 'shortcodes-ultimate' ),
							'desc' => __( 'This text will be shown if data is not found', 'shortcodes-ultimate' )
						),
						'before' => array(
							'default' => '',
							'name' => __( 'Before', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown before the value', 'shortcodes-ultimate' )
						),
						'after' => array(
							'default' => '',
							'name' => __( 'After', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown after the value', 'shortcodes-ultimate' )
						),
						'user_id' => array(
							'default' => '',
							'name' => __( 'User ID', 'shortcodes-ultimate' ),
							'desc' => __( 'You can specify custom user ID. Leave this field empty to use an ID of the current user', 'shortcodes-ultimate' )
						),
						'filter' => array(
							'default' => '',
							'name' => __( 'Filter', 'shortcodes-ultimate' ),
							'desc' => __( 'You can apply custom filter to the retrieved value. Enter here function name. Your function must accept one argument and return modified value. Example function: ', 'shortcodes-ultimate' ) . "<br /><pre><code style='display:block;padding:5px'>function my_custom_filter( \$value ) {\n\treturn 'Value is: ' . \$value;\n}</code></pre>"
						)
					),
					'desc' => __( 'User data', 'shortcodes-ultimate' ),
					'icon' => 'info-circle'
				),
				// post
				'post' => array(
					'name' => __( 'Post', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'data',
					'atts' => array(
						'field' => array(
							'type' => 'select',
							'values' => array(
								'ID'                    => __( 'Post ID', 'shortcodes-ultimate' ),
								'post_author'           => __( 'Post author', 'shortcodes-ultimate' ),
								'post_date'             => __( 'Post date', 'shortcodes-ultimate' ),
								'post_date_gmt'         => __( 'Post date', 'shortcodes-ultimate' ) . ' GMT',
								'post_content'          => __( 'Post content', 'shortcodes-ultimate' ),
								'post_title'            => __( 'Post title', 'shortcodes-ultimate' ),
								'post_excerpt'          => __( 'Post excerpt', 'shortcodes-ultimate' ),
								'post_status'           => __( 'Post status', 'shortcodes-ultimate' ),
								'comment_status'        => __( 'Comment status', 'shortcodes-ultimate' ),
								'ping_status'           => __( 'Ping status', 'shortcodes-ultimate' ),
								'post_name'             => __( 'Post name', 'shortcodes-ultimate' ),
								'post_modified'         => __( 'Post modified', 'shortcodes-ultimate' ),
								'post_modified_gmt'     => __( 'Post modified', 'shortcodes-ultimate' ) . ' GMT',
								'post_content_filtered' => __( 'Filtered post content', 'shortcodes-ultimate' ),
								'post_parent'           => __( 'Post parent', 'shortcodes-ultimate' ),
								'guid'                  => __( 'GUID', 'shortcodes-ultimate' ),
								'menu_order'            => __( 'Menu order', 'shortcodes-ultimate' ),
								'post_type'             => __( 'Post type', 'shortcodes-ultimate' ),
								'post_mime_type'        => __( 'Post mime type', 'shortcodes-ultimate' ),
								'comment_count'         => __( 'Comment count', 'shortcodes-ultimate' )
							),
							'default' => 'post_title',
							'name' => __( 'Field', 'shortcodes-ultimate' ),
							'desc' => __( 'Post data field name', 'shortcodes-ultimate' )
						),
						'default' => array(
							'default' => '',
							'name' => __( 'Default', 'shortcodes-ultimate' ),
							'desc' => __( 'This text will be shown if data is not found', 'shortcodes-ultimate' )
						),
						'before' => array(
							'default' => '',
							'name' => __( 'Before', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown before the value', 'shortcodes-ultimate' )
						),
						'after' => array(
							'default' => '',
							'name' => __( 'After', 'shortcodes-ultimate' ),
							'desc' => __( 'This content will be shown after the value', 'shortcodes-ultimate' )
						),
						'post_id' => array(
							'default' => '',
							'name' => __( 'Post ID', 'shortcodes-ultimate' ),
							'desc' => __( 'You can specify custom post ID. Leave this field empty to use an ID of the current post. Current post ID may not work in Live Preview mode', 'shortcodes-ultimate' )
						),
						'filter' => array(
							'default' => '',
							'name' => __( 'Filter', 'shortcodes-ultimate' ),
							'desc' => __( 'You can apply custom filter to the retrieved value. Enter here function name. Your function must accept one argument and return modified value. Example function: ', 'shortcodes-ultimate' ) . "<br /><pre><code style='display:block;padding:5px'>function my_custom_filter( \$value ) {\n\treturn 'Value is: ' . \$value;\n}</code></pre>"
						)
					),
					'desc' => __( 'Post data', 'shortcodes-ultimate' ),
					'icon' => 'info-circle'
				),
				// post_terms
				// 'post_terms' => array(
				// 	'name' => __( 'Post terms', 'shortcodes-ultimate' ),
				// 	'type' => 'single',
				// 	'group' => 'data',
				// 	'atts' => array(
				// 		'post_id' => array(
				// 			'default' => '',
				// 			'name' => __( 'Post ID', 'shortcodes-ultimate' ),
				// 			'desc' => __( 'You can specify custom post ID. Leave this field empty to use an ID of the current post. Current post ID may not work in Live Preview mode', 'shortcodes-ultimate' )
				// 		),
				// 		'links' => array(
				// 			'type' => 'bool',
				// 			'default' => 'yes',
				// 			'name' => __( 'Show links', 'shortcodes-ultimate' ),
				// 			'desc' => __( 'Show terms names as hyperlinks', 'shortcodes-ultimate' )
				// 		),
				// 		'format' => array(
				// 			'type' => 'select',
				// 			'values' => array(
				// 				'text' => __( 'Terms separated by commas', 'shortcodes-ultimate' ),
				// 				'br' => __( 'Terms separated by new lines', 'shortcodes-ultimate' ),
				// 				'ul' => __( 'Unordered list', 'shortcodes-ultimate' ),
				// 				'ol' => __( 'Ordered list', 'shortcodes-ultimate' ),
				// 			),
				// 			'default' => 'text',
				// 			'name' => __( 'Format', 'shortcodes-ultimate' ),
				// 			'desc' => __( 'Choose how to output the terms', 'shortcodes-ultimate' )
				// 		),
				// 	),
				// 	'desc' => __( 'Terms list', 'shortcodes-ultimate' ),
				// 	'icon' => 'info-circle'
				// ),
				// template
				'template' => array(
					'name' => __( 'Template', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'other',
					'atts' => array(
						'name' => array(
							'default' => '',
							'name' => __( 'Template name', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'Use template file name (with optional .php extension). If you need to use templates from theme sub-folder, use relative path. Example values: %s, %s, %s', 'shortcodes-ultimate' ), '<b%value>page</b>', '<b%value>page.php</b>', '<b%value>includes/page.php</b>' )
						)
					),
					'desc' => __( 'Theme template', 'shortcodes-ultimate' ),
					'icon' => 'puzzle-piece'
				),
				// qrcode
				'qrcode' => array(
					'name' => __( 'QR code', 'shortcodes-ultimate' ),
					'type' => 'single',
					'group' => 'content',
					'atts' => array(
						'data' => array(
							'default' => '',
							'name' => __( 'Data', 'shortcodes-ultimate' ),
							'desc' => __( 'The text to store within the QR code. You can use here any text or even URL', 'shortcodes-ultimate' )
						),
						'title' => array(
							'default' => '',
							'name' => __( 'Title', 'shortcodes-ultimate' ),
							'desc' => __( 'Enter here short description. This text will be used in alt attribute of QR code', 'shortcodes-ultimate' )
						),
						'size' => array(
							'type' => 'slider',
							'min' => 10,
							'max' => 1000,
							'step' => 10,
							'default' => 200,
							'name' => __( 'Size', 'shortcodes-ultimate' ),
							'desc' => __( 'Image width and height (in pixels)', 'shortcodes-ultimate' )
						),
						'margin' => array(
							'type' => 'slider',
							'min' => 0,
							'max' => 50,
							'step' => 5,
							'default' => 0,
							'name' => __( 'Margin', 'shortcodes-ultimate' ),
							'desc' => __( 'Thickness of a margin (in pixels)', 'shortcodes-ultimate' )
						),
						'align' => array(
							'type' => 'select',
							'values' => array(
								'none' => __( 'None', 'shortcodes-ultimate' ),
								'left' => __( 'Left', 'shortcodes-ultimate' ),
								'center' => __( 'Center', 'shortcodes-ultimate' ),
								'right' => __( 'Right', 'shortcodes-ultimate' ),
							),
							'default' => 'none',
							'name' => __( 'Align', 'shortcodes-ultimate' ),
							'desc' => __( 'Choose image alignment', 'shortcodes-ultimate' )
						),
						'link' => array(
							'default' => '',
							'name' => __( 'Link', 'shortcodes-ultimate' ),
							'desc' => __( 'You can make this QR code clickable. Enter here the URL', 'shortcodes-ultimate' )
						),
						'target' => array(
							'type' => 'select',
							'values' => array(
								'self' => __( 'Open link in same window/tab', 'shortcodes-ultimate' ),
								'blank' => __( 'Open link in new window/tab', 'shortcodes-ultimate' ),
							),
							'default' => 'blank',
							'name' => __( 'Link target', 'shortcodes-ultimate' ),
							'desc' => __( 'Select link target', 'shortcodes-ultimate' )
						),
						'color' => array(
							'type' => 'color',
							'default' => '#000000',
							'name' => __( 'Primary color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick a primary color', 'shortcodes-ultimate' )
						),
						'background' => array(
							'type' => 'color',
							'default' => '#ffffff',
							'name' => __( 'Background color', 'shortcodes-ultimate' ),
							'desc' => __( 'Pick a background color', 'shortcodes-ultimate' )
						),
						'class' => array(
							'default' => '',
							'name' => __( 'Class', 'shortcodes-ultimate' ),
							'desc' => __( 'Extra CSS class', 'shortcodes-ultimate' )
						)
					),
					'desc' => __( 'Advanced QR code generator', 'shortcodes-ultimate' ),
					'icon' => 'qrcode'
				),
				// scheduler
				'scheduler' => array(
					'name' => __( 'Scheduler', 'shortcodes-ultimate' ),
					'type' => 'wrap',
					'group' => 'other',
					'atts' => array(
						'time' => array(
							'default' => '',
							'name' => __( 'Time', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'In this field you can specify one or more time ranges. Every day at this time the content of shortcode will be visible. %s %s %s - show content from 9:00 to 18:00 %s - show content from 9:00 to 13:00 and from 14:00 to 18:00 %s - example with minutes (content will be visible each day, 45 minutes) %s - example with seconds', 'shortcodes-ultimate' ), '<br><br>', __( 'Examples (click to set)', 'shortcodes-ultimate' ), '<br><b%value>9-18</b>', '<br><b%value>9-13, 14-18</b>', '<br><b%value>9:30-10:15</b>', '<br><b%value>9:00:00-17:59:59</b>' )
						),
						'days_week' => array(
							'default' => '',
							'name' => __( 'Days of the week', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'In this field you can specify one or more days of the week. Every week at these days the content of shortcode will be visible. %s 0 - Sunday %s 1 - Monday %s 2 - Tuesday %s 3 - Wednesday %s 4 - Thursday %s 5 - Friday %s 6 - Saturday %s %s %s - show content from Monday to Friday %s - show content only at Sunday %s - show content at Sunday and from Wednesday to Friday', 'shortcodes-ultimate' ), '<br><br>', '<br>', '<br>', '<br>', '<br>', '<br>', '<br>', '<br><br>', __( 'Examples (click to set)', 'shortcodes-ultimate' ), '<br><b%value>1-5</b>', '<br><b%value>0</b>', '<br><b%value>0, 3-5</b>' )
						),
						'days_month' => array(
							'default' => '',
							'name' => __( 'Days of the month', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'In this field you can specify one or more days of the month. Every month at these days the content of shortcode will be visible. %s %s %s - show content only at first day of month %s - show content from 1th to 5th %s - show content from 10th to 15th and from 20th to 25th', 'shortcodes-ultimate' ), '<br><br>', __( 'Examples (click to set)', 'shortcodes-ultimate' ), '<br><b%value>1</b>', '<br><b%value>1-5</b>', '<br><b%value>10-15, 20-25</b>' )
						),
						'months' => array(
							'default' => '',
							'name' => __( 'Months', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'In this field you can specify the month or months in which the content will be visible. %s %s %s - show content only in January %s - show content from February to June %s - show content in January, March and from May to July', 'shortcodes-ultimate' ), '<br><br>', __( 'Examples (click to set)', 'shortcodes-ultimate' ), '<br><b%value>1</b>', '<br><b%value>2-6</b>', '<br><b%value>1, 3, 5-7</b>' )
						),
						'years' => array(
							'default' => '',
							'name' => __( 'Years', 'shortcodes-ultimate' ),
							'desc' => sprintf( __( 'In this field you can specify the year or years in which the content will be visible. %s %s %s - show content only in 2014 %s - show content from 2014 to 2016 %s - show content in 2014, 2018 and from 2020 to 2022', 'shortcodes-ultimate' ), '<br><br>', __( 'Examples (click to set)', 'shortcodes-ultimate' ), '<br><b%value>2014</b>', '<br><b%value>2014-2016</b>', '<br><b%value>2014, 2018, 2020-2022</b>' )
						),
						'alt' => array(
							'default' => '',
							'name' => __( 'Alternative text', 'shortcodes-ultimate' ),
							'desc' => __( 'In this field you can type the text which will be shown if content is not visible at the current moment', 'shortcodes-ultimate' )
						)
					),
					'content' => __( 'Scheduled content', 'shortcodes-ultimate' ),
					'desc' => __( 'Allows to show the content only at the specified time period', 'shortcodes-ultimate' ),
					'note' => __( 'This shortcode allows you to show content only at the specified time.', 'shortcodes-ultimate' ) . '<br><br>' . __( 'Please pay special attention to the descriptions, which are located below each text field. It will save you a lot of time', 'shortcodes-ultimate' ) . '<br><br>' . __( 'By default, the content of this shortcode will be visible all the time. By using fields below, you can add some limitations. For example, if you type 1-5 in the Days of the week field, content will be only shown from Monday to Friday. Using the same principles, you can limit content visibility from years to seconds.', 'shortcodes-ultimate' ),
					'icon' => 'clock-o'
				),
			) );
		// Return result
		return ( is_string( $shortcode ) ) ? $shortcodes[sanitize_text_field( $shortcode )] : $shortcodes;
	}
}

class Shortcodes_Ultimate_Data extends Su_Data {
	function __construct() {
		parent::__construct();
	}
}
