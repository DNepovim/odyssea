<?php
/**
 * Contains all fields required on the add / edit forms for the gallery.
 *
 * @package Photonic
 * @subpackage UI
 */

//$options = get_option('photonic_options');
//$layout = isset($options['thumbnail_style']) ? $options['thumbnail_style'] : 'square';
global $photonic_wp_title_caption, $photonic_picasa_use_desc, $photonic_smug_title_caption, $photonic_flickr_title_caption, $photonic_zenfolio_title_caption, $photonic_500px_title_caption,
	   $photonic_flickr_thumb_size, $photonic_flickr_main_size, $photonic_smug_thumb_size, $photonic_smug_main_size, $photonic_zenfolio_thumb_size, $photonic_zenfolio_main_size, $photonic_thumbnail_style;
$layout = isset($photonic_thumbnail_style) ? $photonic_thumbnail_style : 'square';

$fields = array(
	'default' => array(
		'name' => __('WP Galleries', 'photonic'),
		'fields' => array(
			array(
				'id' => 'id',
				'name' => __('Gallery ID', 'photonic'),
				'type' => 'text',
				'req' => true,
			),

			array(
				'id' => 'ids',
				'name' => __('Image IDs', 'photonic'),
				'type' => 'text',
				'hint' => __('Comma-separated. You can specify this if there is no Gallery ID specified.', 'photonic'),
			),

			array(
				'id' => 'style',
				'name' => __('Display Style', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(true),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
			),

			array(
				'id' => 'count',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_wp_title_caption,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
				'std' => 3,
			),

			array(
				'id' => 'thumb_width',
				'name' => __('Thumbnail width', 'photonic'),
				'type' => 'text',
				'std' => 75,
				'hint' => __('In pixels', 'photonic')
			),

			array(
				'id' => 'thumb_height',
				'name' => __('Thumbnail height', 'photonic'),
				'type' => 'text',
				'std' => 75,
				'hint' => __('In pixels', 'photonic')
			),

			array(
				'id' => 'thumbnail_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'raw',
				'std' => Photonic::get_image_sizes_selection('thumbnail_size', false),
				'hint' => __('Sizes defined by your theme. Image picked here will be resized to the dimensions above.', 'photonic')
			),

			array(
				'id' => 'slide_size',
				'name' => __('Slides image size', 'photonic'),
				'type' => 'raw',
				'std' => Photonic::get_image_sizes_selection('slide_size', true),
				'hint' => __('Sizes defined by your theme. Shown in a slideshow or lightbox. Avoid loading large sizes to reduce page loads.', 'photonic')
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => 'slide',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => 4000,
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => 1000,
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),
	'flickr' => array(
		'name' => __('Flickr', 'photonic'),
		'prelude' => __('You have to define your Flickr API Key under Photonic &rarr; Settings &rarr; Flickr &rarr; Flickr Settings.<br/> Documentation: <a href="https://aquoid.com/plugins/photonic/flickr/" target="_blank">Overall</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-photos/" target="_blank">Photos</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-photo/" target="_blank">Single Photos</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-photosets/" target="_blank">Photosets</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-galleries/" target="_blank">Galleries</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-collections/" target="_blank">Collections</a> | <a href="https://aquoid.com/plugins/photonic/flickr/flickr-authentication/" target="_blank">Authentication</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'user_id',
				'name' => __('User ID', 'photonic'),
				'type' => 'text',
				'req' => true,
				'hint' => __('Find your user ID from Photonic &rarr; Helpers.', 'photonic')
			),

			array(
				'id' => 'view',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'photos' => __('Photos', 'photonic'),
					'photosets' => __('Photosets', 'photonic'),
					'galleries' => __('Galleries', 'photonic'),
					'collections' => __('Collections', 'photonic'),
					'photo' => __('Single Photo', 'photonic'),
				),
				'req' => true,
			),

			array(
				'id' => 'photoset_id',
				'name' => __('Photoset ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a single photoset if "Display" is set to "Photosets"', 'photonic')
			),

			array(
				'id' => 'gallery_id',
				'name' => __('Gallery ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a single gallery if "Display" is set to "Galleries"', 'photonic')
			),

			array(
				'id' => 'collection_id',
				'name' => __('Collection ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show contents of a single collection if "Display" is set to "Collections"', 'photonic')
			),

			array(
				'id' => 'photo_id',
				'name' => __('Photo ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a single photo if "Display" is set to "Single Photo"', 'photonic')
			),

			array(
				'id' => 'filter',
				'name' => __('Filter', 'photonic'),
				'type' => 'text',
				'hint' => __('If "Display" is "Photosets" or "Galleries" or "Collections" and you provide a comma-separated list of values here, only these entities will be pulled. Useful if you want to display a single thumbnail for a single photoset / gallery, ignored if Photoset, Gallery or Collection ID is provided', 'photonic')
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'tags',
				'name' => __('Tags', 'photonic'),
				'type' => 'text',
				'hint' => __('Comma-separated list of tags', 'photonic')
			),

			array(
				'id' => 'tag_mode',
				'name' => __('Tag mode', 'photonic'),
				'type' => 'select',
				'options' => array(
					'any' => __('Any tag', 'photonic'),
					'all' => __('All tags', 'photonic'),
				),
			),

			array(
				'id' => 'text',
				'name' => __('With text', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'sort',
				'name' => __('Sort by', 'photonic'),
				'type' => 'select',
				'options' => array(
					'date-posted-desc' => __('Date posted, descending', 'photonic'),
					'date-posted-asc' => __('Date posted, ascending', 'photonic'),
					'date-taken-asc' => __('Date taken, ascending', 'photonic'),
					'date-taken-desc' => __('Date taken, descending', 'photonic'),
					'interestingness-desc' => __('Interestingness, descending', 'photonic'),
					'interestingness-asc' => __('Interestingness, ascending', 'photonic'),
					'relevance' => __('Relevance', 'photonic'),
				),
			),

			array(
				'id' => 'group_id',
				'name' => __('Group ID', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'per_page',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show at the most 100 by default, 500 is the maximum', 'photonic'),
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'privacy_filter',
				'name' => __('Privacy filter', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => __('None', 'photonic'),
					'1' => __('Public photos', 'photonic'),
					'2' => __('Private photos visible to friends', 'photonic'),
					'3' => __('Private photos visible to family', 'photonic'),
					'4' => __('Private photos visible to friends & family', 'photonic'),
					'5' => __('Completely private photos', 'photonic'),
				),
				'hint' => __('Applicable only if Flickr private photos are turned on', 'photonic'),
			),

			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_flickr_title_caption,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_flickr_thumb_size,
				"options" => array(
					's' => __('Small square, 75x75px', 'photonic'),
					'q' => __('Large square, 150x150px', 'photonic'),
					't' => __('Thumbnail, 100px on longest side', 'photonic'),
					'm' => __('Small, 240px on longest side', 'photonic'),
					'n' => __('Small, 320px on longest side', 'photonic'),
				),
				'hint' => __('In pixels, only applicable to square and circular thumbnails', 'photonic')
			),

			array(
				'id' => 'main_size',
				'name' => __('Main image size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_flickr_main_size,
				'options' => array(
					'none' => __('Medium, 500px on the longest side', 'photonic'),
					'z' => __('Medium, 640px on longest side', 'photonic'),
					'c' => __('Medium, 800px on longest side (not always available)', 'photonic'),
					'b' => __('Large, 1024px on longest side (not always available)', 'photonic'),
					'h' => __('Large, 1600px on longest side (not always available)', 'photonic'),
					'o' => __('Original (not always available)', 'photonic'),
				),
			),

			array(
				'id' => 'collections_display',
				'name' => __('Expand Collections', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'lazy' => __('Lazy loading', 'photonic'),
					'expanded' => __('Expanded upfront', 'photonic'),
				),
				'hint' => __('The Collections API is slow, so, if you are displaying collections, pick <a href="https://aquoid.com/plugins/photonic/flickr/flickr-collections/">lazy loading</a> if your collections have many photosets.', 'photonic'),
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),
	'picasa' => array(
		'name' => __('Picasa', 'photonic'),
		'prelude' => __('Documentation: <a href="https://aquoid.com/plugins/photonic/picasa/" target="_blank">Overall</a> | <a href="https://aquoid.com/plugins/photonic/picasa/picasa-photos/" target="_blank">Photos</a> | <a href="https://aquoid.com/plugins/photonic/picasa/picasa-albums/" target="_blank">Albums</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'user_id',
				'name' => __('User ID', 'photonic'),
				'type' => 'text',
				'req' => true,
			),

			array(
				'id' => 'kind',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'album' => __('Albums', 'photonic'),
					'photo' => __('Photos', 'photonic'),
				),
			),

			array(
				'id' => 'album',
				'name' => __('Album', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'access',
				'name' => __('Displayed Access Levels', 'photonic'),
				'type' => 'select',
				'options' => array(
					'public,protected,private' => __('Show all public, protected and private albums', 'photonic'),
					'public' => __('Show public albums only', 'photonic'),
					'protected' => __('Show protected albums only', 'photonic'),
					'public,protected' => __('Show public and protected albums', 'photonic'),
					'protected,private' => __('Show protected and private albums', 'photonic'),
					'public,private' => __('Show public and private albums', 'photonic'),
				),
				'std' => 'public',
				'hint' => __('If "Display" is "Albums" you can decide to show public, private (Picasa only) or protected (Google Photos only) albums. You can use the <code>album</code> or <code>filter</code> attributes to filter the content further. See <a href="https://aquoid.com/plugins/photonic/picasa/picasa-albums/">here</a> for more details.', 'photonic')
			),

			array(
				'id' => 'filter',
				'name' => __('Filter', 'photonic'),
				'type' => 'text',
				'hint' => __('If "Display" is "Albums" and you provide a comma-separated list of values here, only these entities will be pulled. Useful if you want to display thumbnails for certain albums only, ignored if an album id is provided above', 'photonic')
			),

			array(
				'id' => 'protection',
				'name' => __('Protection for Private Albums', 'photonic'),
				'type' => 'select',
				'options' => array(
					'none' => __('None - visitors see albums without providing the authkey', 'photonic'),
					'authkey' => __('Authkey - visitors are prompted for the authkey', 'photonic'),
				),
				'std' => 'none',
				'hint' => __('If "Display" is "Albums" and "Displayed Access Levels" includes "private", you can opt to prompt your users for an <code>authkey</code> before they see the photos in your album. See <a href="https://aquoid.com/plugins/photonic/picasa/picasa-albums/">here</a> for more details.', 'photonic')
			),

			array(
				'id' => 'max_results',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),


			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_picasa_use_desc,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'text',
				'std' => 72,
				'hint' => __('In pixels, only applicable to square and circular thumbnails. Permitted values: 32, 48, 64, 72, 104, 144, 150, 160. To get an uncropped thumbnail, append a <code>u</code> to the size, e.g. 104u.', 'photonic')
			),

			array(
				'id' => 'main_size',
				'name' => __('Main image size', 'photonic'),
				'type' => 'text',
				'std' => 1600,
				'hint' => __('In pixels. Permitted values: 94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600. These are uncropped. To get a cropped thumbnail, append a <code>c</code> to the size, e.g. 1600c.', 'photonic')
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),

	'smugmug' => array(
		'name' => __('SmugMug', 'photonic'),
		'prelude' => __('You have to define your SmugMug API Key under Photonic &rarr; Settings &rarr; SmugMug &rarr; SmugMug Settings.<br/> Documentation: <a href="https://aquoid.com/plugins/photonic/smugmug/" target="_blank">Overall</a> | <a href="https://aquoid.com/plugins/photonic/smugmug/smugmug-photos/" target="_blank">Photos</a> | <a href="https://aquoid.com/plugins/photonic/smugmug/smugmug-albums/" target="_blank">Albums</a> | <a href="https://aquoid.com/plugins/photonic/smugmug/folders/" target="_blank">Folders</a> | <a href="https://aquoid.com/plugins/photonic/smugmug/smugmug-tree/" target="_blank">User Tree</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'view',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'tree' => __('Tree', 'photonic'),
					'albums' => __('All albums', 'photonic'),
					'album' => __('Single Album', 'photonic'),
					'folder' => __('Single Folder', 'photonic'),
				),
				'req' => true,
			),

			array(
				'id' => 'nick_name',
				'name' => __('Nickname', 'photonic'),
				'type' => 'text',
				'hint' => __('If your SmugMug URL is http://joe-sixpack.smugmug.com, this is "joe-sixpack". Required if the "Display" is "Tree" or "All albums".', 'photonic')
			),

			array(
				'id' => 'site_password',
				'name' => __('Site Password', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if your entire SmugMug site is password-protected. See documentation link for "Albums" above.', 'photonic')
			),

			array(
				'id' => 'album',
				'name' => __('Album', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if you are showing "Single Album" above. To find this, go to <i>Photonic &rarr; Helpers &rarr; SmugMug</i> on your dashboard and find the albums for your nickname.', 'photonic')
			),

			array(
				'id' => 'filter',
				'name' => __('Filter', 'photonic'),
				'type' => 'text',
				'hint' => __('If "Display" is "All albums" and you provide a comma-separated list of album keys here, only these entities will be pulled. Useful if you want to display thumbnails for certain albums only, ignored if an album is provided above', 'photonic')
			),

			array(
				'id' => 'password',
				'name' => __('Album Password', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if you are showing "Single Album" above, and if your album is password-protected. See documentation link for "Photos" above.', 'photonic')
			),

			array(
				'id' => 'folder',
				'name' => __('Folder', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if you are showing "Single Folder" above. To find this, go to <i>Photonic &rarr; Helpers &rarr; SmugMug</i> on your dashboard and find the folders for your nickname.', 'photonic')
			),

			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_smug_title_caption,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_smug_thumb_size,
				"options" => array(
					'Tiny' => __('Tiny', 'photonic'),
					'Thumb' => __('Thumb', 'photonic'),
					'Small' => __('Small', 'photonic'),
				),
				'hint' => __('In pixels, only applicable to square and circular thumbnails', 'photonic')
			),

			array(
				'id' => 'main_size',
				'name' => __('Main image size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_smug_main_size,
				'options' => array(
					'4K' => __('4K (not always available)', 'photonic'),
					'5K' => __('5K (not always available)', 'photonic'),
					'Medium' => __('Medium', 'photonic'),
					'Original' => __('Original (not always available)', 'photonic'),
					'Large' => __('Large', 'photonic'),
					'Largest' => __('Largest available', 'photonic'),
					'XLarge' => __('XLarge (not always available)', 'photonic'),
					'X2Large' => __('X2Large (not always available)', 'photonic'),
					'X3Large' => __('X3Large (not always available)', 'photonic'),
				),
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'count',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),
	'500px' => array(
		'name' => __('500px', 'photonic'),
		'prelude' => __('You have to define your Consumer API Key under Photonic &rarr; Settings &rarr; 500px &rarr; 500px Settings.<br/> Documentation: <a href="https://aquoid.com/plugins/photonic/500px/" target="_blank">500px</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'view',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'photos' => __('Photos', 'photonic'),
					'collections' => __('Collections', 'photonic'),
				),
				'req' => true,
			),

			array(
				'id' => 'view_id',
				'name' => __('Object ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if you are displaying a single photo', 'photonic')
			),

			array(
				'id' => 'filter',
				'name' => __('Filter', 'photonic'),
				'type' => 'text',
				'hint' => __('If "Display" is "Collections" and you provide a comma-separated list of values here, only these entities will be pulled. Useful if you want to display only certain collections', 'photonic')
			),

			array(
				'id' => 'feature',
				'name' => __('Feature', 'photonic'),
				'type' => 'select',
				'options' => array(
					'popular' => __('Popular photos', 'photonic'),
					'upcoming' => __('Upcoming photos', 'photonic'),
					'editors' => __("Editor's choices", 'photonic'),
					'fresh_today' => __('Fresh today', 'photonic'),
					'fresh_yesterday' => __('Fresh today', 'photonic'),
					'fresh_week' => __('Fresh today', 'photonic'),
					'user' => __("Specified user's photos", 'photonic'),
					'user_friends' => __("Photos of specified user's friends", 'photonic'),
					'user_favorites' => __("Specified user's favourite photos", 'photonic'),
				),
				'req' => true,
				'hint' => __('Required if you are displaying photos', 'photonic')
			),

			array(
				'id' => 'user_id',
				'name' => __('User ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Either User ID or User Name is required if Feature is user-specific', 'photonic')
			),

			array(
				'id' => 'username',
				'name' => __('User Name', 'photonic'),
				'type' => 'text',
				'hint' => __('Either User ID or User Name is required if Feature is user-specific', 'photonic')
			),

			array(
				'id' => 'only',
				'name' => __('Include category', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => __('All Categories', 'photonic'),
					'Abstract' => __('Abstract', 'photonic'),
					'Animals' => __('Animals', 'photonic'),
					'Black and White' => __("Black and White", 'photonic'),
					'Celebrities' => __('Celebrities', 'photonic'),
					'City and Architecture' => __('Fresh today', 'photonic'),
					'Commercial' => __('Commercial', 'photonic'),
					'Concert' => __("Concert", 'photonic'),
					'Family' => __("Family", 'photonic'),
					'Fashion' => __("Fashion", 'photonic'),
					'Film' => __("Film", 'photonic'),
					'Fine Art' => __("Fine Art", 'photonic'),
					'Food' => __("Food", 'photonic'),
					'Journalism' => __("Journalism", 'photonic'),
					'Landscapes' => __("Landscapes", 'photonic'),
					'Macro' => __("Macro", 'photonic'),
					'Nature' => __("Nature", 'photonic'),
					'Nude' => __("Nude", 'photonic'),
					'People' => __("People", 'photonic'),
					'Performing Arts' => __("Performing Arts", 'photonic'),
					'Sport' => __("Sport", 'photonic'),
					'Still Life' => __("Still Life", 'photonic'),
					'Street' => __("Street", 'photonic'),
					'Transportation' => __("Transportation", 'photonic'),
					'Travel' => __("Travel", 'photonic'),
					'Underwater' => __("Underwater", 'photonic'),
					'Urban Exploration' => __("Urban Exploration", 'photonic'),
					'Wedding' => __("Wedding", 'photonic'),
				),
			),

			array(
				'id' => 'exclude',
				'name' => __('Exclude category', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => __('No Category', 'photonic'),
					'Abstract' => __('Abstract', 'photonic'),
					'Animals' => __('Animals', 'photonic'),
					'Black and White' => __("Black and White", 'photonic'),
					'Celebrities' => __('Celebrities', 'photonic'),
					'City and Architecture' => __('Fresh today', 'photonic'),
					'Commercial' => __('Commercial', 'photonic'),
					'Concert' => __("Concert", 'photonic'),
					'Family' => __("Family", 'photonic'),
					'Fashion' => __("Fashion", 'photonic'),
					'Film' => __("Film", 'photonic'),
					'Fine Art' => __("Fine Art", 'photonic'),
					'Food' => __("Food", 'photonic'),
					'Journalism' => __("Journalism", 'photonic'),
					'Landscapes' => __("Landscapes", 'photonic'),
					'Macro' => __("Macro", 'photonic'),
					'Nature' => __("Nature", 'photonic'),
					'Nude' => __("Nude", 'photonic'),
					'People' => __("People", 'photonic'),
					'Performing Arts' => __("Performing Arts", 'photonic'),
					'Sport' => __("Sport", 'photonic'),
					'Still Life' => __("Still Life", 'photonic'),
					'Street' => __("Street", 'photonic'),
					'Transportation' => __("Transportation", 'photonic'),
					'Travel' => __("Travel", 'photonic'),
					'Underwater' => __("Underwater", 'photonic'),
					'Urban Exploration' => __("Urban Exploration", 'photonic'),
					'Wedding' => __("Wedding", 'photonic'),
				),
			),

			array(
				'id' => 'sort',
				'name' => __('Sort by', 'photonic'),
				'type' => 'select',
				'options' => array(
					'created_at' => __('Created at', 'photonic'),
					'rating' => __('Rating', 'photonic'),
					'times_viewed' => __('Times viewed', 'photonic'),
					'votes_count' => __('Votes count', 'photonic'),
					'favorites_count' => __('Favorites count', 'photonic'),
					'comments_count' => __('Comments count', 'photonic'),
					'taken_at' => __('Taken at', 'photonic'),
				),
			),

			array(
				'id' => 'tag',
				'name' => __('Tags', 'photonic'),
				'type' => 'text',
				'hint' => __('Comma-separated list of tags (above criteria will not apply)', 'photonic')
			),

			array(
				'id' => 'term',
				'name' => __('Search terms', 'photonic'),
				'type' => 'text',
				'hint' => __('Comma-separated list of search terms (above criteria will not apply)', 'photonic')
			),

			array(
				'id' => 'rpp',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
				'std' => 20,
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'date_from',
				'name' => __('From this date', 'photonic'),
				'type' => 'text',
				'hint' => __('Format: yyyy-mm-dd. Use this to filter from a certain date (this is the start date). Set "Sort by" to "Created at"!', 'photonic'),
			),

			array(
				'id' => 'date_to',
				'name' => __('To this date', 'photonic'),
				'type' => 'text',
				'hint' => __('Format: yyyy-mm-dd. Use this to filter to a certain date (this is the end date). Set "Sort by" to "Created at"!', 'photonic'),
			),

			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_500px_title_caption,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'select',
				'options' => array(
					'1' => __('75 &times; 75 px', 'photonic'),
					'2' => __('140 &times; 140 px', 'photonic'),
					'3' => __('280 &times; 280 px', 'photonic'),
					'100' => __('100 &times; 100 px', 'photonic'),
					'200' => __('200 &times; 200 px', 'photonic'),
					'440' => __('440 &times; 440 px', 'photonic'),
					'600' => __('600 &times; 600 px', 'photonic'),
				),
				'hint' => __('In pixels, only applicable to square and circular thumbnails', 'photonic')
			),

			array(
				'id' => 'main_size',
				'name' => __('Main image size', 'photonic'),
				'type' => 'select',
				'options' => array(
					'3' => __('280 &times; 280 px', 'photonic'),
					'4' => __('900px on the longest edge', 'photonic'),
					'5' => __('1170px on the longest edge', 'photonic'),
					'1080' => __('1080px on the longest edge', 'photonic'),
					'1600' => __('1600px on the longest edge', 'photonic'),
					'2048' => __('2048px on the longest edge', 'photonic'),
				),
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),

	'zenfolio' => array(
		'name' => __('Zenfolio', 'photonic'),
		'prelude' => __('Documentation: <a href="https://aquoid.com/plugins/photonic/zenfolio/" target="_blank">Overall</a> | <a href="https://aquoid.com/plugins/photonic/zenfolio/photos/" target="_blank">Photos</a> | <a href="https://aquoid.com/plugins/photonic/zenfolio/photosets/" target="_blank">Photosets</a> | <a href="https://aquoid.com/plugins/photonic/zenfolio/groups/" target="_blank">Groups</a> | <a href="https://aquoid.com/plugins/photonic/zenfolio/group-hierarchy/" target="_blank">Group Hierarchy</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'view',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'photos' => __('Photos', 'photonic'),
					'photosets' => __('Photosets', 'photonic'),
					'hierarchy' => __('Group Hierarchy', 'photonic'),
					'group' => __('Group', 'photonic'),
				),
				'req' => true,
			),

			array(
				'id' => 'object_id',
				'name' => __('Object ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Can be set if "Display" is <code>Photos</code>, <code>Photosets</code> or <code>Group</code>.', 'photonic'),
			),

			array(
				'id' => 'text',
				'name' => __('Search by text', 'photonic'),
				'type' => 'text',
				'hint' => __('Can be set if "Display" is <code>Photos</code> or <code>Photosets</code>.', 'photonic'),
			),

			array(
				'id' => 'category_code',
				'name' => __('Search by category code', 'photonic'),
				'type' => 'text',
				'hint' => __('Can be set if "Display" is <code>Photos</code> or <code>Photosets</code>.', 'photonic').'<br/>'.__('See the list of categories from <em>Photonic &rarr; Helpers</em>.', 'photonic'),
			),

			array(
				'id' => 'sort_order',
				'name' => __('Search results sort order', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'Date' => __('Date', 'photonic'),
					'Popularity' => __('Popularity', 'photonic'),
					'Rank' => __('Rank (for searching by text only)', 'photonic'),
				),
				'hint' => __('Can be set if "Display" is <code>Photos</code> or <code>Photosets</code>.', 'photonic').'<br/>'.__('For search results only.', 'photonic'),
			),

			array(
				'id' => 'photoset_type',
				'name' => __('Photoset type', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'Gallery' => __('Gallery', 'photonic'),
					'Collection' => __('Collection', 'photonic'),
				),
				'hint' => __('Mandatory if Display = Photosets and no Object ID is specified.', 'photonic'),
			),

			array(
				'id' => 'kind',
				'name' => __('Display classification', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'popular' => __('Popular', 'photonic'),
					'recent' => __('Recent', 'photonic'),
				),
				'hint' => __('Mandatory if "Display" is <code>Photos</code> or <code>Photosets</code>, and none of the other criteria above is specified.', 'photonic'),
			),

			array(
				'id' => 'login_name',
				'name' => __('Login name', 'photonic'),
				'type' => 'text',
				'hint' => __('Mandatory if Display = Hierarchy', 'photonic'),
			),

			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'caption',
				'name' => __('Photo title / caption', 'photonic'),
				'type' => 'select',
				'options' => Photonic::title_caption_options(),
				'std' => $photonic_zenfolio_title_caption,
				'hint' => __('This will be used as the title for your photos.', 'photonic'),
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_zenfolio_thumb_size,
				"options" => array(
					"1" => __("Square thumbnail, 60 &times; 60px, cropped square", 'photonic'),
					"0" => __("Small thumbnail, upto 80 &times; 80px", 'photonic'),
					"10" => __("Medium thumbnail, upto 120 &times; 120px", 'photonic'),
					"11" => __("Large thumbnail, upto 120 &times; 120px", 'photonic'),
					"2" => __("Small image, upto 400 &times; 400px", 'photonic'),
				),
				'hint' => __('In pixels, only applicable to square and circular thumbnails', 'photonic')
			),

			array(
				'id' => 'main_size',
				'name' => __('Main image size', 'photonic'),
				'type' => 'select',
				'std' => $photonic_zenfolio_main_size,
				'options' => array(
					'2' => __('Small image, upto 400 &times; 400px', 'photonic'),
					'3' => __('Medium image, upto 580 &times; 450px', 'photonic'),
					'4' => __('Large image, upto 800 &times; 630px', 'photonic'),
					'5' => __('X-Large image, upto 1100 &times; 850px', 'photonic'),
					'6' => __('XX-Large image, upto 1550 &times; 960px', 'photonic'),
				),
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'limit',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),

	'instagram' => array(
		'name' => __('Instagram', 'photonic'),
		'prelude' => __('You have to define your Instagram Client ID under Photonic &rarr; Settings &rarr; Instagram &rarr; Instagram Settings.<br/> Documentation: <a href="https://aquoid.com/plugins/photonic/instagram/" target="_blank">Instagram</a>', 'photonic'),
		'fields' => array(
			array(
				'id' => 'view',
				'name' => __('Display', 'photonic'),
				'type' => 'select',
				'options' => array(
					'recent' => __('Recent Photos', 'photonic'),
					'search' => __('Search', 'photonic'),
					'tag' => __('Tag', 'photonic'),
					'location' => __('Location', 'photonic'),
					'photo' => __('Single Photo', 'photonic'),
				),
				'req' => true,
			),

			array(
				'id' => 'user_id',
				'name' => __('User ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Find your user ID from Photonic &rarr; Helpers.', 'photonic').'<br/>'.__('Required if "Display" is set to "Recent Photos"', 'photnic')
			),

			array(
				'id' => 'media_id',
				'name' => __('Media ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if "Display" is set to "Single Photos".', 'photonic').'<br/>'.__('If your photo is at <code>http://instagram.com/p/ABcde5678fg/</code>, your media id is <code>ABcde5678fg</code>.', 'photonic')
			),

			array(
				'id' => 'tag_name',
				'name' => __('Tag', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if "Display" is set to "Tag"', 'photonic')
			),

			array(
				'id' => 'lat',
				'name' => __('Latitude', 'photonic'),
				'type' => 'text',
				'hint' => __('Latitude and Longitude are required if "Display" = "Search"', 'photonic')
			),

			array(
				'id' => 'lng',
				'name' => __('Longitude', 'photonic'),
				'type' => 'text',
				'hint' => __('Latitude and Longitude are required if "Display" = "Search"', 'photonic')
			),

			array(
				'id' => 'location_id',
				'name' => __('Location ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Required if "Display" is set to "Location".', 'photonic')
			),

			array(
				'id' => 'layout',
				'name' => __('Layout', 'photonic'),
				'type' => 'select',
				'options' => Photonic::layout_options(),
				'hint' => __('The first four options trigger a slideshow, the rest trigger a lightbox.', 'photonic'),
				'std' => $layout,
			),

			array(
				'id' => 'thumb_size',
				'name' => __('Thumbnail size', 'photonic'),
				'type' => 'text',
				'std' => 150,
				'hint' => __('In pixels, only applicable to square and circular thumbnails', 'photonic')
			),

			array(
				'id' => 'min_id',
				'name' => __('Min Photo ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Use the min and max ID to reduce your matches for "Location" and "Tag" displays', 'photonic')
			),

			array(
				'id' => 'max_id',
				'name' => __('Max Photo ID', 'photonic'),
				'type' => 'text',
				'hint' => __('Use the min and max ID to reduce your matches for "Location" and "Tag" displays', 'photonic')
			),

			array(
				'id' => 'columns',
				'name' => __('Number of columns', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'count',
				'name' => __('Number of photos to show', 'photonic'),
				'type' => 'text',
			),

			array(
				'id' => 'more',
				'name' => __('"More" button text', 'photonic'),
				'type' => 'text',
				'hint' => __('Will show a "More" button with the specified text if the number of photos is higher than the above entry. Leave blank to show no button', 'photonic'),
			),

			array(
				'id' => 'fx',
				'name' => __('Slideshow Effects', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'fade' => __('Fade', 'photonic'),
					'slide' => __('Slide', 'photonic'),
				),
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'controls',
				'name' => __('Slideshow Controls', 'photonic'),
				'type' => 'select',
				'options' => array(
					'' => '',
					'hide' => __('Hide', 'photonic'),
					'show' => __('Show', 'photonic'),
				),
				'hint' => __('Shows Previous and Next buttons on the slideshow.', 'photonic'),
			),

			array(
				'id' => 'timeout',
				'name' => __('Time between slides in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),

			array(
				'id' => 'speed',
				'name' => __('Time for each transition in ms', 'photonic'),
				'type' => 'text',
				'std' => '',
				'hint' => __('Applies to slideshows only', 'photonic')
			),
		),
	),
);

