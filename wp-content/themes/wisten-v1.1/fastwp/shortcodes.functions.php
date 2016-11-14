<?php
/**
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

class fastwp_theme_shortcodes{
		
	static function member_item($atts, $content){
		global $fastwp_social_networks;
		extract(shortcode_atts(array(
	      'id' 			=> '',
		), $atts));
		if($id == '') return;
		$social_template = '<a href="%s"><i class="fa fa-%s"></i></a>';						
		
		
		$social = '';
		$value 	= get_post_meta( $id, '_fastwp_meta', true );
		if(isset($value['member_pic']) && !empty($value['member_pic'])){
			$image 	= $value['member_pic'];
		}else {
			$image 	= wp_get_attachment_image_src( get_post_thumbnail_id($id), 'full');
			$image 	= $image['0'];
		}

		foreach($fastwp_social_networks as $sname=>$icon){
			$current_value 	= (isset($value['social'][$sname]))?esc_attr( $value['social'][$sname] ):'';
			if($current_value == '' || $current_value == '#') continue;
			$social 		.= sprintf($social_template, $current_value, str_replace('_','-', $icon));
		}
		$title 		= (isset($value['title']))? $value['title'] : '';
		$excerpt 	= (isset($value['excerpt']))? $value['excerpt'] : '';
		return '<div class="fastwp-member-vcard"><div class="profile-image"><img src="'.$image.'" width="100%"></div><div class="member-name">'.get_the_title($id).'</div><div class="member-title">'.$title.'</div><div class="member-description">'.$excerpt.'</div><div class="socials">'.$social.'</div></div>';
	}
	
	static function portfolio($atts, $content){
		global $post;
		extract(shortcode_atts(array(
	      'orderby' 			=> 'menu_order',
	      'cat_orderby' 		=> 'count',
		  'hide_empty'			=> 0,
		  'screen_breakpoint'	=> '0',
		  'hide_filters'		=> 'no',
		), $atts));
		
		$base 			= get_template_directory_uri();
		$sort_html		= '';
		$portfolio_html	= '';
		$description 	= '';

		$all_projects_filter 		= new stdClass;
		$all_projects_filter->class_name = 'selected';
		$all_projects_filter->slug 	= '*';
		$all_projects_filter->name 	= __('All projects','wisten');
		
		$categories 	= array_merge(array($all_projects_filter), get_terms('portfolio-category', array('orderby' => $cat_orderby, 'hide_empty' => $hide_empty)));
		$_rand 			= rand(1000,9999);
		$sort_item 		= '<li><a href="#filter" data-option-value="%s" class="%s">%s</a></li>';
		$sort_wrapper	= '<div class="isotope-options filter-menu inline"><ul id="filters" class="filters option-set" data-option-key="filter">%s</ul></div>';
		$portfolio_wrap = '<div class="works fwp-isotope" id="portfolio-'.$_rand.'">%s<div class="inner"><div class="items">%s<div class="clear"></div></div></div><div class="fwp-expander clearfix"></div></div>%s';
		/* filters | thumbnail | Zoom href | zoom rel | detail url | project name | project description */
		$portfolio_item	= '
						<div class="work col-xs-4 %s">
							<div class="work-inner">
								<div class="work-img">
									<img src="%s" alt=""/>
									<div class="mask">
										<a class="button zoom" href="%s" data-rel="zoom-image"><i class="fa fa-search"></i></a>
										<a class="button detail" href="%s" data-rel="%s"><i class="fa fa-film"></i></a>
									</div>
								</div>
								<div class="work-desc">
									<h4>%s</h4>
									<p>%s</p>
								</div>
							</div>
						</div>';
		if($hide_filters != 'yes'){
			foreach($categories as $cat){
				$filter		= (isset($cat->slug) && $cat->slug != '*')? '.'.$cat->slug : '*';
				$class		= (isset($cat->class_name))? $cat->class_name : '';
				$name		= (isset($cat->name))? $cat->name : '';
				$sort_html	.= sprintf($sort_item, $filter, $class, $name);
			}
			$sort_wrapper_h = sprintf($sort_wrapper, $sort_html);
		}else {
			$sort_wrapper_h = '';
		}

		query_posts('post_type=fwp_portfolio&numberposts=-1&order=ASC&orderby='.$orderby);
		if(have_posts()){
			while(have_posts()){
				the_post();
				$item 		= $post;
				$tl 		= wp_get_object_terms($item->ID, 'portfolio-category');
				$filters 	= $thumb = $zoom_href = $url_rel = $url = $name = $description = '';
				$_meta 		= get_post_meta( $item->ID, '_fastwp_meta', true );

				foreach($tl as $term){
					$filters .= ' '.$term->slug;
				}
					
				$thumb 		= wp_get_attachment_image_src( get_post_thumbnail_id($item->ID), 'portfolio-thumb' );
				$thumb 		= $thumb['0'];
				$zoom_href 	= wp_get_attachment_image_src( get_post_thumbnail_id($item->ID), 'full');
				$zoom_href 	= $zoom_href['0'];
				$url 		= get_permalink($item->ID);
				$name 		= (isset($item->post_title))? apply_filters('the_title', $item->post_title):'';
				switch(@$_meta['type']){
					case 'project':
						$url_rel 	= 'project';
					break;
					case 'external':
						$url_rel 	= 'external';
						$url 		= @$_meta['url'];
					break;
					default:
						$url_rel 	= 'expander';
					break;
				}
				$portfolio_html .= sprintf($portfolio_item, $filters, $thumb, $zoom_href, $url, $url_rel, $name, $description);
			}
		}
		wp_reset_query();
		$style = ($screen_breakpoint != '0')? "<style>@media only screen and (max-width: {$screen_breakpoint}px){ .filters li a,.filters li:last-child a,.filters li:first-child a{display:block;border-radius:6px;padding:8px 15px;margin:5px auto;font-size:14px;}}</style>":'';
		return sprintf($portfolio_wrap, $sort_wrapper_h, $portfolio_html, $style);
	}

	static function video_background($atts, $content){
		extract(shortcode_atts(array(
	      'video' 		=> '',
	      'ratio' 		=> '16/9',
	      'controls' 	=> 'false',
	      'target'		=> 'self',
	      'autoplay'	=> 'true',
	      'mute'		=> 'true',
	      'opacity'		=> '1',
	      'optimize'	=> 'false',
		  'height'		=> 'auto',
		), $atts));
		$style = ($height != 'auto')? "min-height: {$height}":'';
		if($style != '' && substr_count($style, '%') == 0) { $style .= 'px'; }
		return sprintf('<div class="video-background-player-wrapper" style="%s"><div class="video-background-player" data-property="{addRaster:true, videoURL:\'%s\',showControls:%s,containment:\'%s\',autoPlay:%s, mute:%s, startAt:0, opacity:%s, optimizeDisplay:%s, ratio: \'%s\'}"><div class="inner-content" style="%s">%s</div></div></div>', $style, $video, $controls, $target, $autoplay, $mute, $opacity, $optimize, $ratio, $style, do_shortcode($content));
	}

	static function vimeo_background($atts, $content){
		extract(shortcode_atts(array(
	      'video' 		=> '',
		  'height'		=> 'auto',
		  'target'		=> '',
		), $atts));
		$style = ($height != 'auto')? "min-height: {$height}":'';
		if($style != '' && substr_count($style, '%') == 0) { $style .= 'px'; }
		return sprintf('<div class="video-background-player-wrapper" style="%s"><div class="vimeo-background-player" data-options="{video:\'%s\',target: \'%s\', volume: 0}"><div class="inner-content" style="%s">%s</div></div></div>', $style, $video, $target, $style, do_shortcode($content));
	}
	
	
	
	
	static function rain($atts, $content){
		extract(shortcode_atts(array(
			'image'		=> '',
		), $atts));
		$item_html = '<div class="rainy-image-wrap"><img src="%s" class="raindrop"><div class="rainy-text">%s</div></div>';
		return sprintf($item_html, $image, do_shortcode($content));
	}

	static function timeline($atts, $content){
		global $post;
		extract(shortcode_atts(array(
	      'group' 	=> 'site-timeline',
		), $atts));
		
		$args = array(
			'post_type' 	 => 'fwp_timeline',
			'timeline_group' => $group,
			'orderby'		 =>'post_date',
			'order'			 =>'DESC',
		);

		$year 			= 0;
		$delay			= 0;
		$my_query 		= new WP_Query( $args );
		
		$module_wrap 	= '<ul class="timeline list-unstyled clearfix">%s<li class="start fa fa-bookmark"></li><li class="clear"></li></ul>';
		$year_wrap 		= '<li class="year">%s</li>';
		$item_wrap 		= '<li %s class="note animated" data-animation="%s" data-animation-delay="%s"><h4>%s</h4><p class="desc">%s</p><span class="date">%s</span><span class="arrow fa fa-play"></span></li>';
		
		$items = '';
		$is_left = false;
		if($my_query->have_posts()) :
			while ($my_query->have_posts()) : $my_query->the_post();
				$_meta 		 = get_post_meta( $post->ID, '_fastwp_meta', true );
				$this_year 	 = date('Y',strtotime($post->post_date));
				if($this_year != $year){
					$year 	 = $this_year;
					$items 	.= sprintf($year_wrap, $year);
					$is_left = false;
				}
				$_target 	= '';
				if(isset($_meta['url']) && !empty($_meta['url'])){
					$target  = ($_meta['url'] == 'self')? get_permalink($post->ID) : $_meta['url'];
					$_target = ' onClick="document.location=\''.$target.'\'; return false;"';
				}

				$class 			= ($is_left == true)?'fadeInLeft':'fadeInRight';
				$post_title 	= apply_filters('the_title',$post->post_title);
				$post_content 	= do_shortcode((isset($_meta['timeline_excerpt']) && !empty($_meta['timeline_excerpt']))? $_meta['timeline_excerpt'] : $post->post_content);
				$post_date 		= get_the_date();
				$delay 			+= 50;
				$items 			.= sprintf($item_wrap, $_target, $class, $delay, $post_title, $post_content, $post_date);
				$is_left 		= !$is_left;
			endwhile;
		endif;
		wp_reset_query(); 
	
	if($items != ''){
		return sprintf($module_wrap, $items);
	}
	return;
	}
}

add_shortcode('rain', array('fastwp_theme_shortcodes','rain'));
add_shortcode('team-member', array('fastwp_theme_shortcodes','member_item'));
add_shortcode('portfolio', array('fastwp_theme_shortcodes','portfolio'));
add_shortcode('timeline', array('fastwp_theme_shortcodes','timeline'));
add_shortcode('video-background', array('fastwp_theme_shortcodes','video_background'));
add_shortcode('video-bg', array('fastwp_theme_shortcodes','video_background'));
add_shortcode('with-video', array('fastwp_theme_shortcodes','video_background'));
add_shortcode('vimeo-bg', array('fastwp_theme_shortcodes','vimeo_background'));

