jQuery(function($){
	/* Handle switches */
	$('.fwp-switch-options > label').on('click', function(e){

		var elId = $(this).data('id');
		var parent = $(this).parent();
		var folds = false;
		if(typeof parent.data('folds') == 'string'){
			folds = parent.data('folds');
		}
		if($(this).hasClass('cb-enable')){
			parent.find('.cb-enable').addClass('selected');
			parent.find('.cb-disable').removeClass('selected');
			parent.find('input#'+elId).val(1);
			if(folds != false){
				$(folds).slideDown();
			}
		}else {
			parent.find('.cb-disable').addClass('selected');
			parent.find('.cb-enable').removeClass('selected');
			parent.find('input#'+elId).val(0);
			if(folds != false){
				$(folds).slideUp();
			}
		}
	});
	
	$('.fwp-icons > span').live('click', function(e){
		var me = $(this);
		$(this).parent().find('span').removeClass('selected');
		me.addClass('selected');
		var icon = me.find('i').attr('id');
		
		me.parents('.fastwp-select-icon').find('.hidden_icon').val(icon);
		
	//	$(this).parent().find('span').each(function(e){
			
		//});
	
	});
	
	/* Handle nav menu */
	$('.fastwp-edit-menu-type select').on('change', function(){
		var current_selection = $('option:selected', this).val();
		var icon_selector = $(this).parents('.menu-item-settings').find('.fastwp-edit-menu-icon');
		var icon_holder = $(this).parents('.menu-item-settings').find('.fastwp-select-icon');
		if(current_selection == 'page'){
			icon_selector.hide();
			icon_holder.slideUp();
		}else {
			icon_selector.show();
			var sel_val = $('option:selected', icon_selector).val();
			
			if(sel_val == 'noicon'){
				icon_holder.slideUp();
			}else{
				icon_holder.slideDown();
			}
		}
	//	alert(current_selection);
	// menu-item-settings
	});
	$('.fastwp-edit-menu-icon select').on('change', function(){
		var icon_selector = $(this);
		var sel_val = $('option:selected', icon_selector).val();
		var icon_holder = $(this).parents('.menu-item-settings').find('.fastwp-select-icon');
		if(sel_val == 'noicon'){
			icon_holder.slideUp();
		}else{
			icon_holder.slideDown();
		}
	
	});
	
	/* Handle custom post type metabox */
	$('#post-formats-select input[type="radio"]').on('click', function(){
		var active_option = '#fwp_type_' + $(this).val();
		$('#fastwp_settings > div').hide();
		$(active_option).show();
	});
	
	
	/* Handle image uploading */
	var lastOpenedObject = false;
	var lastOpenedPreview = false;
	jQuery(".upload_image_button").live("click", function(e) {
		e.preventDefault();
		lastOpenedObject = jQuery(this).prev(".upload_image");
		lastOpenedPreview = jQuery(this).parents('.with-image').find(".fwp-preview");
		formfield = lastOpenedObject.attr("name");
		tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
		
		var ori_send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html) {
			imgurl = jQuery("img",html).attr("src");
			if(lastOpenedObject != false)
				lastOpenedObject.val(imgurl);
				if(typeof lastOpenedPreview.html() == 'string'){
					$('img', lastOpenedPreview).attr('src', imgurl);
				}
			window.send_to_editor = ori_send_to_editor;
			tb_remove();
		}
		
		
		
	return false;
	});
/*
	window.send_to_editor = function(html) {
		imgurl = jQuery("img",html).attr("src");
		if(lastOpenedObject != false)
			lastOpenedObject.val(imgurl);
			// alert(typeof lastOpenedPreview.html())
			if(typeof lastOpenedPreview.html() == 'string'){
				$('img', lastOpenedPreview).attr('src', imgurl);
			}
		tb_remove();
	}
	*/
	
	/* Handle gallery slide add /remove */
	$('.fastwp-add-slide').on('click', function(e){
		e.preventDefault();
		var $fieldName = $(this).data('name')
		var $fieldId = $(this).data('id')
		var to_add = '<li class="with-preview with-image"><div class="fwp-preview"><img src="" width="100%"></div><input type="text" name="'+$fieldName+'['+$fieldId+'][]" value="" class="upload_image"> <a href="#" class="upload_image_button"><i class="fa fa-camera"></i></a> <a href="#" class="submitdelete fastwp-delete-slide"><i class="fa fa-times"></i></a></li>';
		$('.gallery.fastwp-sortable .no-slides').remove();
		$('.gallery.fastwp-sortable').append(to_add);
		
	});
	
	$('.fastwp-delete-slide').live('click', function(e){
		e.preventDefault();
		if($(this).parents('ul').find('li').length == 1){
			$('.gallery.fastwp-sortable').append('<li class="no-slides">No slides to show. Click above button to add a slide.</li>');
		}
		$(this).parents('li').slideUp().remove();
	});
	
	/* Enable sortable on gallery items */
	if(typeof $('body').sortable == 'function'){
		$('.fastwp-sortable').sortable();
	}
	/* Enable tabs */
	if(typeof $('body').tabs == 'function'){
		jQuery('.fastwp-tabs').tabs();
	}
	
	/* Handle UI Sliders */
	jQuery('.fwp-slider-ui').each(function(e){
		var value = (typeof $(this).data('value') != 'undefined')? $(this).data('value') : '0'; 
		$(this).slider({
			step:1,
			min:0,
			max:100,
			value: value,
			slide: function( event, ui ) { 
				var f_name = $(this).data('name');
				var f_id = $(this).data('id');
				var field = '#'+f_name + '-'+f_id;
				$(field).parents('.fwp-slider-meta').find('.value').html(ui.value);
				$(field).val(ui.value);
			}
		});
	});
	
	/* Handle color picker */
	jQuery('.fwp-iris').wpColorPicker();
	//jQuery('.fwp-slider-ui').slider();
	
})

function fwp_build_shortcode_builder(t){
	var selected = jQuery('option:selected',t);
	var shortcode_to_build = selected.val();
	if(typeof shortcode_to_build != 'string') return; 
	var data = {
		action: 'fwp_get_shortcode',
		shortcode: shortcode_to_build
	};
	
	jQuery.post(ajaxurl, data, function(response) {
		var data = eval('('+response+')');
		jQuery('.fwp_shortcode_tag').html(data.sc)
		// alert('Got this from the server: ' + response);
	});
}

function fwp_add_shortcode(){
	var $ = jQuery;
	var out = jQuery('#shortcode_output');
	var has_content = false;
	var params = '';
	$('#fwp_shortcode_form *[rel="is_param"]').each(function(){
		if( $(this).attr('id') == 's_content'){
			has_content = true;
		}else {
			params += ' '+ $(this).attr('id') +'="'+$(this).val()+'"';
		}
	});
	var selected_shortcode = $('#fwp_available_shortcodes option:selected').val();
	var HTML = '['+selected_shortcode+params+']';
	if(has_content == true){
		HTML += $('#fwp_shortcode_form #s_content').val();
		HTML += '[/'+selected_shortcode+']';
	}
	out.val(HTML);
return false;
}

function fwp_insert_into_editor(){
	var shortcode = jQuery('#shortcode_output').val();
	tinyMCE.activeEditor.execCommand( "mceInsertContent", false, shortcode )
	tb_remove();
}