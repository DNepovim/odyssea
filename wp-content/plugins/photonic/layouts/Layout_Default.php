<?php

/**
 * Layout Manager to generate the grid layouts and the "Justified Grid" layout, all of which use the same markup. The Justified Grid layout is
 * modified by JS on the front-end, however the base markup for it is similar to the square and circular thumbnails layout.
 *
 * All other layout managers extend this, and might implement their own versions of generate_level_1_gallery and generate_level_2_gallery
 *
 * @package Photonic
 * @subpackage Layouts
 */
class Photonic_Layout {
	private $library, $bypass_popup;

	function __construct() {
		global $photonic_slideshow_library, $photonic_custom_lightbox;
		if ($photonic_slideshow_library != 'custom') {
			$this->library = $photonic_slideshow_library;
		}
		else {
			$this->library = $photonic_custom_lightbox;
		}
	}

	/**
	 * Generates the markup for a single photo.
	 *
	 * @param $data array Pertinent pieces of information about the photo - the source (src), the photo page (href), title and caption
	 * @param $processor Photonic_Processor The object calling this. A CSS class is created in the header, photonic-single-<code>$processor->provider</code>-photo-header
	 * @return string
	 */
	function generate_single_photo_markup($data, $processor) {
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
			$ret .= "\t".'<h3 class="photonic-single-photo-header photonic-single-'.$processor->provider.'-photo-header">'.$photo['title']."</h3>\n";
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
	 * The code for the random layouts is handled in JS, but just the HTML markers for it are provided here.
	 *
	 * @param $photos
	 * @param array $options
	 * @param $short_code
	 * @param $processor
	 * @return string
	 */
	function generate_level_1_gallery($photos, $options, $short_code, $processor) {
		$layout = !empty($short_code['layout']) ? $short_code['layout'] : 'square';
		$columns = !empty($short_code['columns']) ? $short_code['columns'] : 'auto';
		$display = !empty($short_code['display']) ? $short_code['display'] : 'in-page';
		$more = !empty($short_code['more']) ? esc_attr($short_code['more']) : '';
		$panel = !empty($short_code['panel']) ? $short_code['panel'] : '';

		$title_position = empty($short_code['title_position']) ? $options['title_position'] : $short_code['title_position'];
		$row_constraints = isset($options['row_constraints']) && is_array($options['row_constraints']) ? $options['row_constraints'] : array();
		$sizes = isset($options['sizes']) && is_array($options['sizes']) ? $options['sizes'] : array();
		$show_lightbox = !isset($options['show_lightbox']) ? true: $options['show_lightbox'];
		$type = !empty($options['type']) ? $options['type'] : 'photo';
		$parent = !empty($options['parent']) ? $options['parent'] : 'stream';
		$level_2_meta = isset($options['level_2_meta']) && is_array($options['level_2_meta']) ? $options['level_2_meta'] : array();
		$indent = !isset($options['indent']) ? "\t" : $options['indent'];

		$non_standard = $layout == 'random' || $layout == 'masonry' || $layout == 'mosaic';

		$col_class = '';
		if (Photonic::check_integer($columns)) {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}

		if ($col_class == '' && $row_constraints['constraint-type'] == 'padding') {
			$col_class = 'photonic-pad-photos';
		}
		else if ($col_class == '') {
			$col_class = 'photonic-gallery-'.$row_constraints['count'].'c';
		}
		$col_class .= ' photonic-level-1 photonic-thumb photonic-thumb-'.$layout;

		$link_attributes = $this->get_lightbox_attributes($display, $col_class, $show_lightbox, $panel, $processor);

		$effect = $this->get_thumbnail_effect($short_code, $layout, $title_position);
		$ul_class = "class='title-display-$title_position photonic-level-1-container ".($non_standard ? 'photonic-'.$layout.'-layout' : 'photonic-standard-layout')." photonic-thumbnail-effect-$effect'";
		if ($display == 'popup') {
			$ul_class = "class='slideshow-grid-panel lib-{$this->library} title-display-$title_position'";
		}

		$ret = '';
		if (!$non_standard && $display != 'popup') {
			$container_tag = 'ul';
			$element_tag = 'li';
		}
		else {
			$container_tag = 'div';
			$element_tag = 'div';
		}

		$level_2_data = '';
		if (!empty($level_2_meta)) {
			$level_2_data = array();
			// Should have total, start, end, per-page
			foreach ($level_2_meta as $meta => $value) {
				$level_2_data[] = 'data-photonic-stream-'.$meta.'="'.$value.'"';
			}
			$level_2_data = implode(' ', $level_2_data);
			$level_2_data .= ' data-photonic-stream-provider="'.$processor->provider.'"';
		}

		$to_be_glued = '';
		if (!empty($short_code)) {
			$to_be_glued = array();
			foreach ($short_code as $name => $value) {
				if (is_scalar($value)) {
					$to_be_glued[] = $name.'='.$value;
				}
			}
			$to_be_glued = implode('&',$to_be_glued);
			$to_be_glued = esc_attr($to_be_glued);
		}

		$level_2_data .= ' data-photonic-stream-query="'.$to_be_glued.'"';
		$columns_data = ' data-photonic-gallery-columns="'.$columns.'"';

		$start_with = "$indent<$container_tag $ul_class $level_2_data $columns_data>\n";
		$ret .= $start_with;

		global $photonic_external_links_in_new_tab;
		if (!empty($photonic_external_links_in_new_tab)) {
			$target = " target='_blank' ";
		}
		else {
			$target = '';
		}

		$counter = 0;
		$thumbnail_class = " class='$layout' ";

		foreach ($photos as $photo) {
			$counter++;

			$thumb = ($non_standard && $display == 'in-page') ? (isset($photo['tile_image']) ? $photo['tile_image'] : $photo['main_image']) : $photo['thumbnail'];
			$orig = $photo['main_image'];
			$url = $photo['main_page'];
			$title = esc_attr($photo['title']);
			$description = esc_attr($photo['description']);
			$alt = esc_attr($photo['alt_title']);
			$orig = ($this->library == 'none' || !$show_lightbox) ? $url : $orig;

			$title = empty($title) ? ((empty($alt) && $processor->link_lightbox_title && $this->library != 'thickbox') ? apply_filters('photonic_default_lightbox_text', __('View', 'photonic')) : $alt) : $title;
			$ret .= "$indent\t<".$element_tag.' class="photonic-'.$processor->provider.'-image photonic-'.$processor->provider.'-'.$type.' '.$col_class.'">'."\n";

			$deep_value = 'gallery[photonic-'.$processor->provider.'-'.$parent.'-'.(empty($panel) ? $processor->gallery_index : $panel).']/'.(empty($photo['id']) ? $counter : $photo['id']).'/';
			$deep_link = ' data-photonic-deep="'.$deep_value.'" ';

			$style = array();
			if (!empty($sizes['thumb-width'])) $style[] = 'width:'.$sizes['thumb-width'].'px';
			if (!empty($sizes['thumb-height'])) $style[] = 'height:'.$sizes['thumb-height'].'px';
			if (!empty($style)) $style = 'style="'.implode(';', $style).'"'; else $style = '';
			if ($processor->link_lightbox_title && $this->library != 'thickbox') {
				$title_link_start = esc_attr("<a href='$url' $target>");
				$title_link_end = esc_attr("</a>");
			}
			else {
				$title_link_start = '';
				$title_link_end = '';
			}

			if ($short_code['caption'] == 'desc' || ($short_code['caption'] == 'title-desc' && empty($title)) || ($short_code['caption'] == 'desc-title' && !empty($description))) {
				$title = $description;
			}
			else if (($short_code['caption'] == 'desc-title' && empty($title)) || $short_code['caption'] == 'none') {
				$title = '';
			}

			if (!empty($title)) {
				$title_markup = $title_link_start.esc_attr($title).$title_link_end;
			}
			else {
				$title_markup = '';
			}

			$shown_title = '';
			if (in_array($title_position, array('below', 'hover-slideup-show', 'hover-slidedown-show')) && !empty($title)) {
				$shown_title = '<div class="photonic-title-info"><div class="photonic-photo-title photonic-title">'.wp_specialchars_decode($title, ENT_QUOTES).'</div></div>';
			}

			$photo_data = array('title' => $title_markup, 'deep' => $deep_value, 'raw_title' => $title, 'href' => $orig);
			if (!empty($photo['download'])) {
				$photo_data['download'] = $photo['download'];
			}
			$lb_specific_data = $this->get_lightbox_specific_photo_data($photo_data);
//			$ret .= $indent."\t\t".'<a '.$link_attributes.' href="'.$orig.'" title="'.$title_markup.'" data-title="'.$title_markup.'" '.$lb_specific_data.' '.$target.$deep_link.">\n";
			$ret .= $indent."\t\t".'<a '.$link_attributes.' href="'.$orig.'" title="'.esc_attr($title).'" data-title="'.$title_markup.'" '.$lb_specific_data.' '.$target.$deep_link.">\n";
			$ret .= $indent."\t\t\t".'<img alt="'.$alt.'" src="'.$thumb.'" '.$style.$thumbnail_class."/>\n";
			$ret .= $indent."\t\t\t".$shown_title."\n";
			$ret .= $indent."\t\t"."</a>\n";
			$ret .= $indent."\t"."</$element_tag>\n";
		}

		if ($ret != $start_with) {
			$trailing = strlen($element_tag) + 3;
			if (substr($ret, -$trailing) != "</$element_tag>" && $short_code['popup'] == 'show' && !$non_standard) {
				$ret .= "$indent</$element_tag><!-- last $element_tag.photonic-pad-photos -->";
			}

			$ret .= "$indent</$container_tag> <!-- ./photonic-level-1-container -->\n";
			if (!empty($level_2_meta) && isset($level_2_meta['end']) && isset($level_2_meta['total']) && $level_2_meta['total'] > $level_2_meta['end']) {
				$ret .= !empty($more) ? "<a href='#' class='photonic-more-button photonic-more-dynamic'>$more</a>\n" : '';
			}
		}
		else {
			$ret = '';
		}

		if (is_archive()) {
			global $photonic_archive_thumbs;
			if (!empty($photonic_archive_thumbs) && $counter < $photonic_archive_thumbs) {
				$processor->is_more_required = false;
			}
		}

		return $ret;
	}

	/**
	 * Generates the HTML for a group of level-2 items, i.e. Photosets (Albums) and Galleries for Flickr, Albums for Picasa,
	 * Albums for SmugMug, Collections for 500px, and Photosets (Albums and Collections) for Zenfolio. No concept of albums
	 * exists in native WP and Instagram.
	 *
	 * @param $objects
	 * @param $options
	 * @param $short_code
	 * @param $processor
	 * @return string
	 */
	function generate_level_2_gallery($objects, $options, $short_code, $processor) {
		$row_constraints = isset($options['row_constraints']) && is_array($options['row_constraints']) ? $options['row_constraints'] : array();
		$type = $options['type'];
		$singular_type = $options['singular_type'];
		$title_position = $options['title_position'];
		$level_1_count_display = $options['level_1_count_display'];
		$indent = !isset($options['indent']) ? '' : $options['indent'];
		$provider = $processor->provider;

		$columns = $short_code['columns'];
		$layout = !isset($short_code['layout']) ? 'square' : $short_code['layout'];
		$popup = ' data-photonic-popup="'.$short_code['popup'].'"';

		$non_standard = $layout == 'random' || $layout == 'masonry' || $layout == 'mosaic';
		$effect = $this->get_thumbnail_effect($short_code, $layout, $title_position);
		$ul_class = "class='title-display-$title_position photonic-level-2-container ".($non_standard ? 'photonic-'.$layout.'-layout' : 'photonic-standard-layout')." photonic-thumbnail-effect-$effect'";

		$columns_data = ' data-photonic-gallery-columns="'.$columns.'"';

		$ret = "\n$indent<ul $ul_class $columns_data>";
		if ($non_standard) {
			$ret = "\n$indent<div $ul_class>";
		}

		if ($columns != 'auto') {
			$col_class = 'photonic-gallery-'.$columns.'c';
		}
		else if ($row_constraints['constraint-type'] == 'padding') {
			$col_class = 'photonic-pad-'.$type;
		}
		else {
			$col_class = 'photonic-gallery-'.$row_constraints['count'].'c';
		}

		$col_class .= ' photonic-level-2 photonic-thumb';

		$counter = 0;
		foreach ($objects as $object) {
			$data_attributes = isset($object['data_attributes']) && is_array($object['data_attributes']) ? $object['data_attributes'] : array();
			$data_array = array();
			foreach ($data_attributes as $attr => $value) {
				$data_array[] = 'data-photonic-'.$attr.'="'.$value.'"';
			}

			$data_array = implode(' ', $data_array);


			$id = empty($object['id_1']) ? '' : $object['id_1'].'-';
			$id = $id.$processor->gallery_index;
			$id = empty($object['id_2']) ? $id : ($id.'-'.$object['id_2']);
			$title = esc_attr($object['title']);
			$image = "<img src='".(($non_standard && isset($object['tile_image'])) ? $object['tile_image'] : $object['thumbnail'])."' alt='".$title."' class='$layout'/>";
			$additional_classes = !empty($object['classes']) ? implode(' ', $object['classes']) : '';
			$realm_class = '';
			if (!empty($object['classes'])) {
				foreach ($object['classes'] as $class) {
					if (stripos($class, 'photonic-'.$provider.'-realm') !== FALSE) {
						$realm_class = $class;
					}
				}
			}
			$anchor = "\n{$indent}\t\t<a href='{$object['main_page']}' class='photonic-{$provider}-$singular_type-thumb $additional_classes' id='photonic-{$provider}-$singular_type-thumb-$id' title='".$title."' data-title='".$title."' $data_array$popup>\n$indent\t\t\t".$image;
			$text = '';
			if (in_array($title_position, array('below', 'hover-slideup-show', 'hover-slidedown-show'))) {
				$text = "\n{$indent}\t\t\t<div class='photonic-title-info'>\n{$indent}\t\t\t\t<div class='photonic-$singular_type-title photonic-title'>".$title."";
				if (!$level_1_count_display && !empty($object['counter'])) {
					$text .= '<span class="photonic-'.$singular_type.'-photo-count">'.sprintf(__('%s photos', 'photonic'), $object['counter']).'</span>';
				}
			}
			if ($text != '') {
				$text .= "</div>\n{$indent}\t\t\t</div>";
			}

			$anchor .= $text."\n{$indent}\t\t</a>";
			$password_prompt = '';
			if (!empty($object['passworded'])) {
				$prompt_title = esc_attr__('Protected Content', 'photonic');
				$prompt_submit = esc_attr__('Access', 'photonic');
				$password_type = " type='password' ";
				$prompt_type = 'password';
				$prompt_text = esc_attr__('This album is password-protected. Please provide a valid password.', 'photonic');
				if (in_array("photonic-$provider-passworded-authkey", $object['classes'])) {
					$prompt_text = esc_attr__('This album is protected. Please provide a valid authorization key.', 'photonic');
				}
				else if (in_array("photonic-$provider-passworded-link", $object['classes'])) {
					$prompt_text = esc_attr__('This album is protected. Please provide the short-link for it.', 'photonic');
					$password_type = '';
					$prompt_type = 'link';
				}
				$password_prompt = "
							<div class='photonic-password-prompter $realm_class' id='photonic-{$provider}-$singular_type-prompter-$id' title='$prompt_title' data-photonic-prompt='$prompt_type'>
								<p>$prompt_text</p>
								<input $password_type name='photonic-{$provider}-password' />
								<span class='photonic-{$provider}-submit photonic-password-submit'><a href='#'>$prompt_submit</a></span>
							</div>";
			}

			if ($non_standard) {
				$ret .= "\n$indent\t<div class='photonic-{$provider}-image photonic-{$provider}-$singular_type-thumb $col_class' id='photonic-{$provider}-$singular_type-$id'>{$anchor}{$password_prompt}\n$indent\t</div>";
			}
			else {
				$ret .= "\n$indent\t<li class='photonic-{$provider}-image photonic-{$provider}-$singular_type-thumb $col_class' id='photonic-{$provider}-$singular_type-$id'>{$anchor}{$password_prompt}\n$indent\t</li>";
			}
			$counter++;
		}

		if ($ret != "\n$indent<ul $ul_class>" && !$non_standard) {
			$ret .= "\n$indent</ul>\n";
		}
		else if ($non_standard) {
			$ret .= "\n$indent</div>\n";
		}
		else {
			$ret = '';
		}

		if (is_archive()) {
			global $photonic_archive_thumbs;
			if (!empty($photonic_archive_thumbs) && $counter < $photonic_archive_thumbs) {
				$processor->is_more_required = false;
			}
		}
		return $ret;
	}

	/**
	 * Depending on the lightbox library, this function provides the CSS class and the rel tag for the thumbnail. This method borrows heavily from
	 * Justin Tadlock's Cleaner Gallery Plugin.
	 *
	 * @param $display
	 * @param $col_class
	 * @param $show_lightbox
	 * @param $rel_id
	 * @param $processor
	 * @return string
	 */
	function get_lightbox_attributes($display, $col_class, $show_lightbox, $rel_id, $processor) {
		global $photonic_slideshow_mode;
		$class = '';
		$rel = '';
		$lightbox_specific_attr = '';
		if ($this->library != 'none' && $show_lightbox) {
			$class = 'photonic-launch-gallery launch-gallery-'.$this->library." ".$this->library;
			$rel = 'lightbox-photonic-'.$processor->provider.'-stream-'.(empty($rel_id) ? $processor->gallery_index : $rel_id);
			switch ($this->library) {
				case 'lightbox':
				case 'slimbox':
				case 'jquery_lightbox_plugin':
				case 'jquery_lightbox_balupton':
					$class = 'launch-gallery-lightbox lightbox';
					$rel = "lightbox[{$rel}]";
					break;

				case 'fancybox2':
					$class = 'photonic-launch-gallery launch-gallery-fancybox fancybox';
					break;

				case 'prettyphoto':
					$rel = 'photonic-prettyPhoto['.$rel.']';
					break;

				case 'lightcase':
					$lightbox_specific_attr = ' data-rel="lightcase:lightbox-photonic-'.$processor->provider.'-stream-'.(empty($rel_id) ? $processor->gallery_index : $rel_id).((isset($photonic_slideshow_mode) && $photonic_slideshow_mode == 'on') ? ':slideshow' : '').'" ';
					break;

				case 'strip':
					$lightbox_specific_attr = ' data-strip-group="'.$rel.'" ';
					break;

				default:
					$class = 'photonic-launch-gallery launch-gallery-'.$this->library." ".$this->library;
					$rel = 'lightbox-photonic-'.$processor->provider.'-stream-'.(empty($rel_id) ? $processor->gallery_index : $rel_id);
					break;
			}

			if ($display == 'popup') {
				$class .= ' '.$col_class;
			}
			$class = " class='$class' ";
			$rel = " rel='$rel' ";
		}

		return $class.$rel.$lightbox_specific_attr;
	}

	/**
	 * Some lightboxes require some additional attributes for individual photos. E.g. Lightgallery requires something to show the title etc.
	 * This method returns such additional information. Not to be confused with <code>get_lightbox_attributes</code>, which
	 * returns information for the gallery as a whole.
	 *
	 * @param $photo_data
	 * @return string
	 */
	function get_lightbox_specific_photo_data($photo_data) {
		if ($this->library == 'lightgallery') {
			$download = !empty($photo_data['download']) ? 'data-download-url="'.$photo_data['download'].'" ' : '';
			return ' data-sub-html="'.$photo_data['title'].'" '.$download;
		}
		else if ($this->library == 'strip') {
			return ' data-strip-caption="'.$photo_data['title'].'" data-strip-options="onShow: function(a) { photonicSetHash('."'{$photo_data['deep']}'".'); }, afterHide: function() { photonicUnsetHash(); } " ';
		}
		return '';
	}

	/**
	 * Returns the thumbnail effect that should be used for a gallery. Not all effects can be used by all types of layouts.
	 *
	 * @param $short_code
	 * @param $layout
	 * @param $title_position
	 * @return string
	 */
	function get_thumbnail_effect($short_code, $layout, $title_position) {
		if (!empty($short_code['thumbnail_effect'])) {
			$effect = $short_code['thumbnail_effect'];
		}
		else {
			global $photonic_standard_thumbnail_effect, $photonic_justified_thumbnail_effect, $photonic_mosaic_thumbnail_effect, $photonic_masonry_thumbnail_effect;
			$effect = $layout == 'mosaic' ? $photonic_mosaic_thumbnail_effect :
				($layout == 'masonry' ? $photonic_masonry_thumbnail_effect :
					($layout == 'random' ? $photonic_justified_thumbnail_effect :
						$photonic_standard_thumbnail_effect));
		}

		if ($layout == 'circle' && $effect != 'opacity') { // "Zoom" doesn't work for circle
			$thumbnail_effect = 'none';
		}
		else if (($layout == 'square' || $layout == 'launch' || $layout == 'masonry') && $title_position == 'below') { // For these combinations, Zoom doesn't work
			$thumbnail_effect = 'none';
		}
		else {
			$thumbnail_effect = $effect;
		}
		return apply_filters('photonic_thumbnail_effect', $thumbnail_effect, $short_code, $layout, $title_position);
	}
}
