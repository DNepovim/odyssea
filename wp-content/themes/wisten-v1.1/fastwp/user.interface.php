<?php
/*
 * Build date: 09/01/2014
 * Last update: 09/01/2014
 *
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */
class FastWP_UI {
	static function get_nav_menu_items($showHome = '0'){
		/* Get data */
		$menu_locations 	= get_nav_menu_locations();
		$menu_query_args 	= array('order'=>'ASC', 'orderby'=>'menu_order', 'post_type'=>'nav_menu_item', 'post_status'=>'publish', 'output_key'=>'menu_order', 'nopaging'=>true, 'update_post_term_cache'=>false ); 
		$primary_menu		= (isset($menu_locations) && isset($menu_locations['primary']))? $menu_locations['primary'] : '';
		$menu_items 		= wp_get_nav_menu_items($primary_menu, $menu_query_args );
		/* Add home button if needed */
		if($showHome === '1'){
			$menu_home_item 		= new stdClass;
			$menu_home_item->title 	= __('Home' ,'fastwp');
			$menu_home_item->url 	= home_url();
			$menu_home_item->type 	= null;
			$menu_home_item->object_id 	= null;
			$menu_home_item->menutype 	= null;
			$menu_items 			= array_merge(array($menu_home_item), $menu_items);
		}
		return $menu_items;
	}
	
	static function get_nav_menu($showHome = '0'){
		
		/* Initialize */
		$BASEURL 	= '';
		$HTML		= '';
		$item_tpl	= '<li class="%s"><a class="scroll" href="%s" data-hash="%s">%s</a>%s</li>'; 
		/* Request data */
		$menu_items = self::get_nav_menu_items($showHome);
		/* Do verifications */	
		if(((!is_page() && !is_single()) || basename(get_page_template()) != 'wisten-multi.php')||
			((is_page() || is_single()) &&  basename(get_page_template()) != 'wisten-multi.php')){
			$BASEURL = home_url().'/';
		}
		/* Build UI*/
		if(is_array($menu_items)){
			for($i=0;$i<count($menu_items);$i++){
				if(isset($menu_items[$i]->menutype) && $menu_items[$i]->menutype == 'separator') {
					continue;
				}
				if(isset($menu_items[$i]->menu_item_parent) && $menu_items[$i]->menu_item_parent != '0'){
					continue;
				}
				$url 		= (isset($menu_items[$i]->menutype) && $menu_items[$i]->menutype == 'section')? $BASEURL.'#s-'.sanitize_title(trim($menu_items[$i]->title)) : $menu_items[$i]->url;
				$sub_menu 	= '';
				$item_class = '';
				if(isset($menu_items[$i+1]->menu_item_parent) && $menu_items[$i+1]->menu_item_parent != '0'){
					$sub_menu 	= self::get_subnav_menu($menu_items, $menu_items[$i]->ID, 1, $BASEURL);
					$item_class = 'is-parent dropdown-toggle nav-toggle';
				}
				$item_class .= self::nav_item_is_active($menu_items[$i], $i);
				$data_hash	= (isset($menu_items[$i]->menutype) && $menu_items[$i]->menutype == 'section')? '#s-'.sanitize_title(trim($menu_items[$i]->title)) : '';
				$item_title	= $menu_items[$i]->title.(($sub_menu != '')?'<b data-toggle="dropdown" class="caret"></b>':'');
				$HTML		.= sprintf($item_tpl, $item_class, $url, $data_hash, $item_title, $sub_menu);
			}
		}
		/* Return UI */
		return $HTML;
	}
	
	static function get_subnav_menu($menu_items, $parent, $level = 1, $BASEURL = ''){
		$HTML 		= '';
		$submenu_tpl= '<ul class="sub-menu dropdown-menu" style="display:none">%s</ul>';
		$item_tpl	= '<li class="%s"><a class="scroll" href="%s" data-hash="%s">%s</a></li>'; 
		for($i=0;$i<count($menu_items);$i++){
			if(isset($menu_items[$i]->menutype) && $menu_items[$i]->menutype == 'separator') continue;
			
			if(isset($menu_items[$i]->menu_item_parent) && $menu_items[$i]->menu_item_parent == $parent){
				$is_active 	= self::nav_item_is_active($menu_items[$i]);
				$url 		= ($menu_items[$i]->menutype == 'section')? $BASEURL.'#s-'.sanitize_title(trim($menu_items[$i]->title)) : $menu_items[$i]->url;
				$data_hash	=(isset($menu_items[$i]->menutype) && $menu_items[$i]->menutype == 'section')? '#s-'.sanitize_title(trim($menu_items[$i]->title)) : '';
				$HTML		.= sprintf($item_tpl, $is_active, $url, $data_hash, $menu_items[$i]->title);
			}
		}
		if($HTML != ''){
			$HTML = sprintf($submenu_tpl, $HTML);
		}
		return $HTML;
	}
	
	static function nav_item_is_active($menu_item, $index = 0){
		global $post;
		$is_active = (($index==0 && (is_page() || is_single()) && basename(get_page_template()) == 'wisten-multi.php')?' active x ':'');
		if($menu_item->type == 'taxonomy' && is_category()){
			$current_category = get_the_category();
			if($current_category[0]->term_id == $menu_item->object_id){
				$is_active = ' active cat-active ';
			}
		} else if (is_page()){
			if($menu_item->object_id == $post->ID){
				$is_active = ' active ';
			}else {
				$is_active = '';
			}
		}
		return $is_active;
	}
	
	static function nav_menu($showHome = '0'){
		/* Print nav menu on screen */
		echo self::get_nav_menu($showHome);
	}

	static function get_social_icons($KEY = 'social_', $class=''){
		/* Initialize function */
		global $smof_data;
		$output = '';
		/* Define networks */
		$networks = array(
			'contact' 	=> 'envelope',
			'bitbucket' 	=> 'bitbucket',
			'facebook' 	=> 'facebook',
			'pinterest' 	=> 'pinterest',
			'dribbble' 	=> 'dribbble',
			'github' 	=> 'github',
			'google_plus' 	=> 'google-plus',
			'instagram' 	=> 'instagram',
			'linkedin' 	=> 'linkedin',
			'skype' 	=> 'skype',
			'stack_exchange' 	=> 'stack-exchange',
			'tumblr' 	=> 'tumblr',
			'vimeo' 	=> 'vimeo-square',
			'weibo' 	=> 'weibo',
			'facebook'	=> 'facebook',
			'twitter'	=> 'twitter',
			'pinterest'	=> 'pinterest',
			'youtube'	=> 'youtube',
			'rss'		=> 'rss'
		);
		/* Loop trough items */
		foreach($networks as $net=>$icon){
			$key = $KEY.$net;
			if(isset($smof_data[$key]) && !empty($smof_data[$key]) && $smof_data[$key] != ''){
				if($net == 'contact'){
					if(substr_count($smof_data[$key],'@') == '1' && !substr_count($smof_data[$key],'mailto:')){
						$smof_data[$key] = 'mailto:'.$smof_data[$key];
					}
				}
				$output .= '<a href="'.$smof_data[$key].'" target="_blank" class="'.$class.'"><i class="fa fa-'.$icon.'"></i></a>';
			}
		}
		/* Return the magic */
		return $output;
	}

	static function get_slogan(){
		/* Initialize function */
		global $smof_data;
		$slogans = array();
		/* Loop trough items */
		for($i=1; $i<=5; $i++){
			if(isset($smof_data['top_slogan_'.$i])&&!empty($smof_data['top_slogan_'.$i])){
				$slogans[] = $smof_data['top_slogan_'.$i];
			}
		}
		/* Do verifications */
		if(count($slogans) > 1){
			$rand = rand(0, count($slogans)-1);
			$slogan = $slogans[$rand];
		} else {
			$slogan = $slogans[0];
		}
		/* Return the magic */
		return $slogan;
	}
	
	static function get_logo(){
		/* Initialize function */
		global $smof_data;
		$style = '';
		/* Do verifications */
		if(isset($smof_data['logo_left'])){ $style .= 'margin-left:'.$smof_data['logo_left'].'px;';}
		if(isset($smof_data['logo_top'])){ $style .= 'margin-top:'.$smof_data['logo_top'].'px;';}
		/* Return the magic */
		return '
			<div class="logo" style="'.$style.'">
				<a class="scroll" href="'.home_url().'#s-home" data-hash="#s-home"><img src="'.$smof_data['media_logo'].'" alt="" class="logo-image"/></a>
			</div>
		';
	}

	static function build_multi_page($echo = false){
		global $smof_data;
		/* Initialize function, get resources */
		$menu_locations 	= get_nav_menu_locations();		
		$menu_query_args 	= array('order'=>'ASC', 'orderby'=>'menu_order', 'post_type'=>'nav_menu_item', 'post_status'=>'publish', 'output_key'=>'menu_order', 'nopaging'=>true, 'update_post_term_cache'=>false ); 
		$menu_items 		= wp_get_nav_menu_items( (isset($menu_locations['primary']))?$menu_locations['primary']:'', $menu_query_args );
		$menu_items 		= apply_filters('fastwp_alter_sections', $menu_items);
		$pageContent 		= '';
		/* Do verifications & Loop trough items */
		if(is_array($menu_items) && count($menu_items) > 0){
			$j = 0;
			for($i=0;$i<count($menu_items);$i++){
				if($menu_items[$i]->menutype == 'section' || $menu_items[$i]->menutype == 'separator'){
					$aditional_info = $menu_items[$i];
					if($menu_items[$i]->menutype == 'separator' && $aditional_info->classes[0] == ''){
						 $aditional_info->classes[0] = 'full';
					}
					$pageContent .= self::make_page_section($menu_items[$i]->object_id, sanitize_title(trim($menu_items[$i]->title)), $aditional_info);
					if($j == '0'){
						if(isset($smof_data['menu_after_home']) && $smof_data['menu_after_home'] == '1'){ $pageContent .= fwpHelper::logo_menu_markup(false); }
					}
					$j++;
				}
			}
		}
		/* Return the magic */
		if($echo == true){
			echo 	$pageContent;
		}
		else {
			return 	$pageContent;
		}
	}
	
	static function make_page_section($pageId, $id, $extra_info = array()){
		/* Get resources */
		$page 		= get_page($pageId, OBJECT, 'display');
		/* Do verifications */
		if(!isset( $page->post_content)) return;
		/* Get more resources */
		$template 	= get_post_meta( $pageId, '_wp_page_template', true );
		$content 	= apply_filters('the_content', trim($page->post_content));
		$title 		= apply_filters('the_title', $page->post_title);
		$section 	= '';
		switch($template){
			case 'fastwp-blank-template.php':
			break;
			default:
				$sectionIcon = (!empty($extra_info->menuicon) && $extra_info->menuicon != 'noicon')? '<div class="relative no-height"><a class="contain-logo br scroll-here" href="#s-'.$id.'"><i class="fa '.$extra_info->menuicon_selected.'"></i></a></div>':'';
				$extra_section_class = (!empty($extra_info->menuicon) && $extra_info->menuicon == 'iconborder')? 'top-border':'';
				if($extra_info->menutype == 'separator' || $extra_info->menutype == 'section'){
					$content = apply_filters('fastwp_separator', $content, $pageId);
				}
				if(@$extra_info->classes[0] == 'full' || $template == 'page-fp_full.php'){
					$section .= '<section id="s-'.$id.'" class="contain clearfix '.$extra_section_class.'">'.$sectionIcon.$content.'</section>';
				}else {
					$section .= '<section id="s-'.$id.'" class="contain clearfix"><div class="inner '.$extra_section_class.'">'.$sectionIcon.$content.'</div></section>';
				}
			break;
		}
		/* Return the magic */
		return $section;
	}

	static function build_post_navigation(){
		global $paged, $wp_query, $smof_data;

		if(isset($smof_data['blog_navi_type']) && $smof_data['blog_navi_type'] == 1){
			return '<ul class="pagination left">
			<li>'.get_previous_posts_link( '<i class="fa fa-chevron-left"></i>', $wp_query->max_num_pages ).'</li>
			<li>'.get_next_posts_link( '<i class="fa fa-chevron-right"></i>', $wp_query->max_num_pages ).'</li>
			</ul>';
		}
		
        $big = 999999999; /* need an unlikely integer */
        $args = array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?page=%#%',
            'total' => $wp_query->max_num_pages,
            'current' => max( 1, get_query_var( 'paged') ),
            'show_all' => false,
            'end_size' => 3,
            'mid_size' => 2,
            'prev_next' => True,
            'prev_text' => __('&laquo;' ,'fastwp'),
            'next_text' => __('&raquo;' ,'fastwp'),
            'type' => 'list',
            );

		return str_replace("'page-numbers'>", '"pagination right">', paginate_links($args));
	}
}




class fwpHelper {
	public static function logo_menu_markup($echo = true){
		global $smof_data;
		
		$in 			= array('dropdown-toggle nav-toggle', 'dropdown-menu');
		$out 			= array('dropdown-toggle mobile-toggle', 'dropdown-menu dr-mobile');			
		$html_template 	= '<section id="navigation" class="shadow"><div class="inner navigation">%s<div class="nav-menu"><ul class="nav main-nav">%s</ul></div><div class="dropdown mobile-drop"><a data-toggle="dropdown" class="mobile-menu" href="#"><i class="fa fa-bars"></i></a><ul class="nav dropdown-menu fullwidth" role="menu" >%s</ul></div><div class="clear"></div></div></section>';
		$logo 			= FastWP_UI::get_logo();
		$menu 			= FastWP_UI::get_nav_menu($smof_data['home_in_menu']);
		$menu_mobile 	= str_replace($in, $out, $menu);
		$markup 		= sprintf($html_template, $logo, $menu, $menu_mobile);
		if($echo == true){ 
			echo $markup; 
		} else { 
			return $markup; 
		}
	}
	
	public static function switchButton($field, $name = '', $selected = 0, $folds = null, $labels = null){
		if($labels == null){
			$labels = array(__('On' ,'fastwp'), __('Off' ,'fastwp'));
		}
		
		if($name != ''){
			$field_name = $name.'['.$field.']';
		}else {
			$field_name = $field;
		}
		return '
		<p class="fwp-switch-options group" '.($folds != null?'data-folds="'.$folds.'"':'').'>
			<label class="s_fld cb-enable '. (($selected == 1)? 'selected':'') .'" data-id="'.$field.'">
				<span>'.$labels[0].'</span>
			</label>
			<label class="s_fld cb-disable '. (($selected == 0)? 'selected':'') .'" data-id="'.$field.'">
				<span>'.$labels[1].'</span>
			</label>
			<input type="hidden" class="of-input" name="'.$field_name.'" id="'.$field.'" value="'.$selected.'">
		</p>
		';
	}

	public static function selectBox($field, $name, $values, $selected = false, $multiple = false){
		$HTML 		= '<div class="fwp-select">';
		$HTML 		.= '<select name="'.$name.'['.$field.']'.(($multiple==true)?'[]':'').'" '.(($multiple==true)?'multiple="multiple" class="multiple-selection"':'').'>';
		foreach($values as $k=>$v){
			$HTML 	.= '<option value="'.$k.'" '.(($k==$selected || (is_array($selected) && in_array($k, $selected)))?'selected="selected"':'').'>'.$v.'</option>';
		}
		$HTML 		.= '</select></div>';
		return $HTML;
	}

	public static function imageUpload($field, $name, $value, $label = 'Image url', $class='single-image'){
		$field_name 	= $name.'['.$field.']';
		$image_template = '<div class="fwp-margin-top with-preview with-image %s"><div class="fwp-preview"><img src="%s" width="%s"></div><div class="fwp-input-meta"><span class="icon"><i class="fa fa-picture-o"></i></span><input type="text" name="%s" value="%s" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <span class="label">%s</span></div></div>';
		return sprintf($image_template, $class, $value, '100%',$field_name, $value, $label);
	}
}
