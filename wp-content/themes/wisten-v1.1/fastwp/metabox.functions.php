<?php
/**
 * @package WordPress
 * @subpackage Wisten
 * @since Wisten 1.0
 */

 
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function fastwp_add_custom_box() {
	add_meta_box(
        'wisten_post_option',
        __( 'Setup post options', 'wisten' ),
        'fastwp_metabox',
        'post'
    );
	
	add_meta_box(
        '_fwp_service',
        __( 'Service settings', 'wisten' ),
        'fastwp_service_metabox',
        'fwp_service'
    );

	add_meta_box(
        '_fwp_timeline',
        __( 'Timeline target', 'wisten' ),
        'fastwp_timeline_metabox',
        'fwp_timeline'
    );

	add_meta_box(
        '_fwp_team',
        __( 'Member options', 'wisten' ),
        'fastwp_team_metabox',
        'fwp_team'
    );

	add_meta_box(
        '_fwp_project',
        __( 'Project settings', 'wisten' ),
        'fastwp_project_metabox',
        'fwp_portfolio',
		'advanced',
		'high'
    );


    $screens = array( 'post', 'page' );

    foreach ( $screens as $screen ) {
        add_meta_box(
            'wisten_section_'.$screen,
            __( 'Page/Separator options', 'wisten' ),
            'fastwp_extra_section_attach',
            $screen
        );
    }
	

}
add_action( 'add_meta_boxes', 'fastwp_add_custom_box' );


function fastwp_project_metabox( $post ) {
	$value = get_post_meta( $post->ID, '_fastwp_meta', true );
	$input_template = '<div class="fwp-input-meta"><span class="icon"><i class="fa fa-%s"></i></span><input type="text" name="fastwp_meta[%s]" value="%s"> <span class="label">%s</span></div>';
?>
<h3>Project subtitle</h3>
<input type="text" class="top10" name="fastwp_meta[subtitle]" value="<?php echo esc_attr( @$value['subtitle'] ); ?>">

<h3>Open type</h3>
<div class="top10">
<?php
	$options = array(
		'expander' => 'Expander',
		'project' => 'Project page',
		'external' => 'External url',
	);
			$fieldName = 'fastwp_meta';
	echo fwpHelper::selectBox('type','fastwp_meta',$options, esc_attr( @$value['type'] ));
?>
</div>
<h3>External url</h3>
<input type="text" class="top10" name="fastwp_meta[url]" value="<?php echo esc_attr( @$value['url'] ); ?>">
<h3>Top parallax image</h3>
<li class="with-preview with-image top10"><div class="fwp-preview"><img src="<?php echo @$value['top_parallax']; ?>" width="100%"></div><input type="text" name="fastwp_meta[top_parallax]" value="<?php echo @$value['top_parallax']; ?>" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a></li>
<h3>Show featured projects on project page</h3>
<div class="group">
<?php
	$fieldId = 'featured';
	echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], '#fastwp_featured_items', array(__('Yes','fastwp'), __('No','fastwp')));
?>
<div id="fastwp_featured_items" style="<?php echo (isset($value[$fieldId]) && $value[$fieldId] == '1')?'':'display:none'; ?>">
<?php 
	echo sprintf($input_template, 'cogs', 'featured_title', ((@$value['featured_title'])?$value['featured_title']:__('OUR FEATURED WORKS','fastwp')), 'Featured work title');
	echo sprintf($input_template, 'cogs', 'featured_descr', ((@$value['featured_descr'])?$value['featured_descr']:''), 'Featured work description');

	echo fwpHelper::imageUpload('featured_parallax', $fieldName, @$value['featured_parallax'],'Image url','single-image-metabox');
?>

	Select featured items (3 recommended)<br>
<?php
$projects = query_posts('post_type=fwp_portfolio&post_status=publish&posts_per_page=-1&numberposts=-1&exclude='.$post->ID);
$project_list = array();
if(count($projects) > 0){
	foreach($projects as $prj){
		$project_list[$prj->ID] = $prj->post_title;

	}
}
wp_reset_query();
echo fwpHelper::selectBox('project_featured', 'fastwp_meta', $project_list,  @$value['project_featured'] , true); 
//var_dump($projects);
?>
</div>
<h3>Use side by side layout instead of default full width gallery</h3><div class="group">
<?php
$fieldId = 'layout_side';
echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], null, array(__('Yes','fastwp'), __('No','fastwp')));
?>
</div>
<h3>Client</h3>
<input type="text" class="top10" name="fastwp_meta[client]" value="<?php echo esc_attr( @$value['client'] ); ?>">

<h3>Project Type</h3><br>
<?php 
$project_types = array(
'gallery' => 'Gallery',
'image' => 'Image',
'video' => 'Video',
'audio' => 'Audio',
);
echo fwpHelper::selectBox('project_type', 'fastwp_meta', $project_types, esc_attr( @$value['project_type'] )); 
?>

<h3>Project media settings</h3><br>
<div class="ui-tabs fastwp-tabs">
	<ul>
		<li><a href="#fastwp-meta-gallery">Gallery</a></li>
		<li><a href="#fastwp-meta-image">Single Image</a></li>
		<li><a href="#fastwp-meta-video">Video</a></li>
		<li><a href="#fastwp-meta-audio">Audio</a></li>
	</ul>
	<div id="fastwp-meta-gallery">
		<?php
			$fieldId = 'gallery';
		?>
	<a href="#" class="fastwp-add-slide" data-name="<?php echo $fieldName; ?>" data-id="<?php echo $fieldId; ?>"><i class="fa fa-plus"></i> Add slide</a>	
		<ul class="gallery fastwp-sortable">
			<?php
			if(is_array($value) && count(@$value[$fieldId]) > 0){
				for($i=0;$i<count($value[$fieldId]);$i++){
					echo '<li class="with-preview with-image"><div class="fwp-preview"><img src="'.@$value[$fieldId][$i].'" width="100%"></div><input type="text" name="'.$fieldName.'['.$fieldId.'][]" value="'.@$value[$fieldId][$i].'" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <a href="#" class="submitdelete fastwp-delete-slide"><i class="fa fa-times"></i></a></li>';
				}
			}else {
				echo '<li class="no-slides">'.__('No slides to show. Click above button to add a slide.','fastwp').'</li>';
			}
			?>
		</ul>
	</div>
	<div id="fastwp-meta-image">
		<li class="with-preview with-image"><div class="fwp-preview"><img src="<?php echo esc_attr( @$value['image'] ); ?>" width="100%"></div><input type="text" name="fastwp_meta[image]" value="<?php echo esc_attr( @$value['image'] ); ?>" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <a href="#" class="submitdelete fastwp-delete-slide"><i class="fa fa-times"></i></a></li>
	</div>
	<div id="fastwp-meta-video">
		<div class="fwp-input-meta"><span class="icon"><i class="fa fa-youtube"></i></span><input type="text" name="fastwp_meta[video]" value="<?php echo esc_attr( @$value['video'] ); ?>"> <span class="label">Youtube/Vimeo video url</span></div>
		<?php
		$fieldId = 'video_ar';
		echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], null, array(__('Wide (16:9)','fastwp'), __('Normal (4:3)','fastwp')));
		?>
	</div>
	<div id="fastwp-meta-audio">
		<div class="fwp-input-meta"><span class="icon"><i class="fa fa-volume-up"></i></span><input type="text" name="fastwp_meta[audio]" value="<?php echo esc_attr( @$value['audio'] ); ?>"> <span class="label">Soundcloud audio url</span></div>
	</div>
	
</div>
</div>
<?php
}



function fastwp_service_metabox( $post ) {
	$value = get_post_meta( $post->ID, '_fastwp_meta', true );
?>
<h3>Service url</h3><br>
<input type="text" class="" name="fastwp_meta[url]" value="<?php echo esc_attr( @$value['url'] ); ?>">
<br><br>
<h3>Service icon</h3>
<div class="fastwp-select-icon">
<input type="hidden" class="hidden_icon" name="fastwp_meta[service_icon]" value="<?php echo esc_attr( @$value['service_icon'] ); ?>">
<?php
	echo admin_choose_fa_icon(esc_attr( @ $value['service_icon'] ));
?>
</div>
<?php
}
	
function fastwp_team_metabox( $post ) {
	global $fastwp_social_networks;
	$value = get_post_meta( $post->ID, '_fastwp_meta', true );
	$item_template = '<div class="fwp-input-meta"><span class="icon"><i class="fa fa-%s"></i></span><input type="text" name="fastwp_meta[social][%s]" value="%s"> <span class="label">%s</span></div>';

?>
	<h3>Custom member image</h3>
	<small>Select big image to be used on member vcard shortcode</small><br>
	<div class="with-preview with-image top10"><div class="fwp-preview"><img src="<?php echo @$value['member_pic']; ?>" width="100%"></div><input type="text" name="fastwp_meta[member_pic]" value="<?php echo @$value['member_pic']; ?>" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a></div>

	<h3>Member title</h3><br>
	<div class="fwp-input-meta"><span class="icon"><i class="fa fa-user"></i></span><input type="text" class="" name="fastwp_meta[title]" value="<?php echo (isset($value['title']))?esc_attr( $value['title'] ):''; ?>"></div>

	<h3>Member short description</h3><br>
	<div class="fwp-input-meta"><span class="icon"><i class="fa fa-comment"></i></span><textarea name="fastwp_meta[excerpt]"><?php echo (isset($value['excerpt']))?esc_attr( $value['excerpt'] ):''; ?></textarea></div>

	<h3>External url</h3>
	<small><em>TIP: Fill this field only if you want to go elsewhere when click on this member.</em></small><br><br>
	<div class="fwp-input-meta"><span class="icon"><i class="fa fa-link"></i></span><input type="text" class="" name="fastwp_meta[url]" value="<?php echo (isset($value['url']))?esc_attr( $value['url'] ):''; ?>"></div>

	<h3>Social icons</h3>
	<small><em>TIP: Don't use more than 6 networks for a member in order to look like on demo website.</em></small><br><br>
<?php
	foreach($fastwp_social_networks as $name=>$icon){
		$current_value = (isset($value['social'][$name]))?esc_attr( $value['social'][$name] ):'';
		echo sprintf($item_template, $icon, $name, $current_value, str_replace('_',' ', $name));
	}
}
		
function fastwp_timeline_metabox( $post ) {
	$value = get_post_meta( $post->ID, '_fastwp_meta', true );
?>
<h3>Target url</h3><br>
<input type="text" class="" name="fastwp_meta[url]" value="<?php echo (isset($value['url']))?esc_attr( $value['url'] ):'self'; ?>">


<?php
	$fieldName = 'fastwp_meta';
	$fieldId = 'timeline_excerpt';
	echo '<div class="fwp heading">Timeline excerpt</div><small>Leave this empty if you want to show post content in timeline</small>';
	echo '<div class="fwp-margin-top">';
	wp_editor(@$value[$fieldId],$fieldId, array('textarea_name'=> $fieldName.'['.$fieldId.']'));
	echo '</div>';
?>


<?php
}
	
function fastwp_metabox( $post ) {
	$value = get_post_meta( $post->ID, '_fastwp_meta', true );
	$format = get_post_format( $post->ID );
	if ( false === $format ) {
		$format = 'standard';
	}
	$dn = 'style="display:none"';
	$fieldName = 'fastwp_meta';
?>
<div class="fwp heading">Title background image</div>
<div class="with-preview with-image top10"><div class="fwp-preview"><img src="<?php echo @$value['top_parallax']; ?>" width="100%"></div><input type="text" name="fastwp_meta[top_parallax]" value="<?php echo @$value['top_parallax']; ?>" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a></div>
<div id="fastwp_settings">
	<div id="fwp_type_0" <?php echo ($format != 'standard')?$dn:''; ?>>  </div>
	<div id="fwp_type_video" <?php echo ($format != 'video')?$dn:''; ?>>
<?php
	$fieldId1 = 'video';
	echo '<div class="fwp heading">Youtube/Vimeo video url</div>';
	echo '<div class="fwp-margin-top">';
	echo '<input type="text" name="'.$fieldName.'['.$fieldId1.']" value="'.@$value[$fieldId1].'">';
	echo '</div>';
	echo '<div class="fwp heading">Video aspect ratio</div>';
	$fieldId = 'video_wide';
	echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], null, array(__('16:9','fastwp'), __('4:3','fastwp')));

?>	
	</div>
	<div id="fwp_type_image" <?php echo ($format != 'image')?$dn:''; ?>>
<?php
	$fieldId1 = 'image';
	echo '<div class="fwp heading">Post image</div>';
	echo '<div class="fwp-margin-top with-preview with-image">';
	echo '<div class="fwp-preview"><img src="'.@$value[$fieldId1].'" width="100%"></div>';
	echo '<input type="text" name="'.$fieldName.'['.$fieldId1.']" value="'.@$value[$fieldId1].'" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a>';
	echo '</div>';
?>
	</div>
	<div id="fwp_type_audio" <?php echo ($format != 'audio')?$dn:''; ?>> 
<?php
	$fieldId1 = 'audio';
	echo '<div class="fwp heading">Soundcloud audio url</div>';
	echo '<div class="fwp-margin-top">';
	echo '<input type="text" name="'.$fieldName.'['.$fieldId1.']" value="'.@$value[$fieldId1].'">';
	echo '</div>';
?>
	</div>
	<div id="fwp_type_quote" <?php echo ($format != 'quote')?$dn:''; ?>> 
<?php

	$fieldId = 'quote_excerpt';
	$fieldId1 = 'quote_author';
	echo '<div class="fwp heading">Quote author</div>';
	echo '<div class="fwp-margin-top">';
	echo '<input type="text" name="'.$fieldName.'['.$fieldId1.']" value="'.@$value[$fieldId1].'">';
	echo '</div>';
	echo '<div class="fwp heading">Quote excerpt</div>';
	echo '<div class="fwp-margin-top">';
	wp_editor(@$value[$fieldId],$fieldId, array('textarea_name'=> $fieldName.'['.$fieldId.']'));
	echo '</div>';
?>
	</div>
	<div id="fwp_type_gallery" <?php echo ($format != 'gallery')?$dn:''; ?>>
	<?php
		$fieldId = 'gallery';
	?>
	<div class="fwp heading">Image Gallery Setup</div>
<a href="#" class="fastwp-add-slide" data-name="<?php echo $fieldName; ?>" data-id="<?php echo $fieldId; ?>"><i class="fa fa-plus"></i> Add slide</a>	
	<ul class="gallery fastwp-sortable">
		<?php
		if(is_array($value) && count(@$value[$fieldId]) > 0){
			for($i=0;$i<count($value[$fieldId]);$i++){
				echo '<li class="with-preview with-image"><div class="fwp-preview"><img src="'.@$value[$fieldId][$i].'" width="100%"></div><input type="text" name="'.$fieldName.'['.$fieldId.'][]" value="'.@$value[$fieldId][$i].'" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <a href="#" class="submitdelete fastwp-delete-slide"><i class="fa fa-times"></i></a></li>';
			}
		}else {
			echo '<li class="no-slides">'.__('No slides to show. Click above button to add a slide.','fastwp').'</li>';
		}
		?>
	</ul>
	</div>
</div>
<?php 
}

function fastwp_extra_section_attach( $post ) {
  // Add an nonce field so we can check for it later.
  wp_nonce_field( 'fastwp_extra_section_attach', 'fastwp_extra_section_attach_nonce' );

  /*
   * Use get_post_meta() to retrieve an existing value
   * from the database and use the value for the form.
   */
	$value = get_post_meta( $post->ID, '_attach_section', true );

	$fieldName = 'fastwp_use_section';
	$fieldId = 'fastwp_parallax';
	echo '<div class="fwp heading">Parallax background</div>';
	echo '<div class="group">';
	echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], '#fastwp_metabox_parallax');
	echo '</div>';
	// $fieldId = 'fastwp_section_ct';
	
	/* TODO: Replace with fwpHelper::imageUpload()*/
	echo '<div class="fwp-margin-top" id="fastwp_metabox_parallax" style="'.((@$value[$fieldId] != '1')?'display:none':'').'">';
	echo '<div class="fwp-input-meta"><input type="text" class="fwp-iris" name="'.$fieldName.'[color]" value="'.esc_attr( @$value['color'] ).'"> <span class="label">Overlay color</span></div>';
	echo '<div class="fwp-input-meta fwp-slider-meta"><span class="icon"><i class="fa fa-signal"></i></span><div class="fwp-slider-ui" data-name="'.$fieldName.'" data-id="opacity" data-value="'. ((isset($value['opacity']) && !empty($value['opacity']))?esc_attr($value['opacity'] ):'20').'"></div><input type="hidden" id="'.$fieldName.'-opacity" name="'.$fieldName.'[opacity]" value="'.((isset($value['opacity']) && !empty($value['opacity']))?esc_attr($value['opacity'] ):'20').'"> <span class="label">Overlay opacity (<span class="value">'.((isset($value['opacity']) && !empty($value['opacity']))?esc_attr($value['opacity'] ):'20').'</span>%)</span></div>';
	echo '<div class="fwp-input-meta fwp-slider-meta"><span class="icon"><i class="fa fa-cogs"></i></span><div class="fwp-slider-ui" data-name="'.$fieldName.'" data-id="speed" data-value="'. ((isset($value['speed']) && !empty($value['speed']))?esc_attr($value['speed'] ):'50').'"></div><input type="hidden" id="'.$fieldName.'-speed" name="'.$fieldName.'[speed]" value="'.((isset($value['speed']) && !empty($value['speed']))?esc_attr($value['speed'] ):'50').'"> <span class="label">Parallax speed (<span class="value">'.((isset($value['speed']) && !empty($value['speed']))?esc_attr($value['speed'] ):'50').'</span>)</span></div>';
	echo '<div class="fwp-margin-top with-preview with-image single-image">';
	$fieldId = 'p_image';
	echo '<div class="fwp-preview"><img src="'.@$value[$fieldId].'" width="100%"></div>';
	echo '<div class="fwp-input-meta"><span class="icon"><i class="fa fa-picture-o"></i></span><input type="text" name="'.$fieldName.'['.$fieldId.']" value="'.@$value[$fieldId].'" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <span class="label">Image url</span></div>';
	echo '</div>';
	echo '</div>';

	$fieldId = 'fastwp_attach_separator';
	echo '<div class="fwp heading">Attach content before footer</div>';
	echo '<div class="group">';
	echo fwpHelper::switchButton($fieldId, $fieldName, @$value[$fieldId], '#fastwp_metabox_separator');
	echo '</div>';
	echo '<div class="fwp-margin-top" id="fastwp_metabox_separator" style="'.((@$value[$fieldId] != '1')?'display:none':'').'">';
	$fieldId = 'fastwp_section_ct';
	wp_editor(@$value[$fieldId],$fieldId, array('textarea_name'=> $fieldName.'['.$fieldId.']'));
	echo '</div>';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function fastwp_save_postdata( $post_id ) {

  /*
   * We need to verify this came from the our screen and with proper authorization,
   * because save_post can be triggered at other times.
   */

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;

  // Check the user's permissions.
  if (isset($_POST['post_type'])  && 'page' == $_POST['post_type']) {

    if ( ! current_user_can( 'edit_page', $post_id ) )
        return $post_id;
  
  } else {

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;
  }

  /* OK, its safe for us to save the data now. */
  $section	= isset($_POST['fastwp_use_section'])? $_POST['fastwp_use_section'] : array();

  $metabox	= isset($_POST['fastwp_meta'])? $_POST['fastwp_meta'] : array();

  // Update the meta field in the database.
  update_post_meta( $post_id, '_attach_section', $section );
  update_post_meta( $post_id, '_fastwp_meta',	 $metabox );

}
add_action( 'save_post', 'fastwp_save_postdata' );
