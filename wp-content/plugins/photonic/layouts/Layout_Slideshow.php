<?php

/**
 * Generates the slideshow layout for level 1 objects. Level 2 cannot be displayed as slideshows.
 */
class Photonic_Layout_Slideshow extends Photonic_Layout {
	function generate_level_1_gallery($photos, $options = array(), $short_code = array(), $processor) {
		global $photonic_wp_slide_adjustment, $photonic_wp_slide_align;
		if (!is_array($photos) || empty($photos)) {
			return '';
		}

		$data_attr = '';
		foreach ($short_code as $key => $value) {
			if (in_array($key, array('speed', 'timeout', 'fx', 'pause', 'layout', 'strip-style', 'controls', 'columns'))) {
				$data_attr .= 'data-photonic-'.$key.'="'.esc_attr($value).'" ';
			}
		}

		$title_position = empty($short_code['title_position']) ? $options['title_position'] : $short_code['title_position'];
		$ret = "<div class='photonic-slideshow {$short_code['style']} title-display-{$title_position} fix'>\n";
		$ret .= "\t<ul id='photonic-slideshow-{$processor->gallery_index}' class='photonic-slideshow-content fix photonic-slideshow-$photonic_wp_slide_adjustment ".(!empty($photonic_wp_slide_align) ? 'photonic-slide-center' : '')."' $data_attr>\n";

		foreach ( $photos as $photo ) {
			$ret .= "\t\t<li class='photonic-slideshow-img' data-thumb='{$photo['thumbnail']}'>\n";
			$title = esc_attr($photo['title']);
			$description = esc_attr($photo['description']);
			if ($short_code['caption'] == 'desc' || ($short_code['caption'] == 'title-desc' && empty($title)) || ($short_code['caption'] == 'desc-title' && !empty($description))) {
				$title = $description;
			}
			else if (($short_code['caption'] == 'desc-title' && empty($title)) || $short_code['caption'] == 'none') {
				$title = '';
			}

			$ret .= "\t\t\t<img src='".$photo['main_image']."' alt='{$title}' title='".(($title_position == 'regular' || $title_position == 'tooltip') ? $title : '')."' id='photonic-slideshow-{$processor->gallery_index}-{$photo['id']}' />\n";
			$shown_title = '';
			if (in_array($title_position, array('below', 'hover-slideup-show', 'hover-slidedown-show')) && !empty($title)) {
				$shown_title = "\t\t\t".'<div class="photonic-title-info">'."\n\t\t\t\t".'<div class="photonic-photo-title photonic-title">'.wp_specialchars_decode($title, ENT_QUOTES).'</div>'."\n\t\t\t".'</div>'."\n";
			}

			if (!empty($title)) {
				$ret .= $shown_title;
			}
			$ret .= "\t\t</li>\n";
		}
		$ret .= "\t</ul>\n</div><!-- .photonic-slideshow-->\n";
		return $ret;
	}
}
