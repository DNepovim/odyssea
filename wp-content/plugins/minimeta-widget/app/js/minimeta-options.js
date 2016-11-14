jQuery(document).ready(function() {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	//Swits style and add link for part boxes
	//addd/remove linke when part box is active or not
	jQuery('.checkbox-active').click(function () { //click works btter than change in IE
		if ( jQuery(this).attr('checked') ) {
			jQuery(this).parent('span').parent('h4').parent('div:has(div > input),').children('h4').children('span').after(' <span style="float: right;" class="widget-edit-item">'+MiniMetaL10n.edit+'</span>');
			jQuery(this).parent('span').next('span').click( function() {
				jQuery(this).parent().parent().toggleClass('closed');
			});
		} else {
			jQuery(this).parent('span').next('span').remove();
			jQuery(this).parent().parent().parent().addClass('closed');
		}
    });
	//Start settings of selectet parts
	jQuery('.checkbox-active:checked').parent('span').parent('h4').parent('div:has(div > input),').children('h4').children('span').after(' <span style="float: right;" class="widget-edit-item">'+MiniMetaL10n.edit+'</span>');
	jQuery('.widget-edit-item').click( function() {
		jQuery(this).parent().parent().toggleClass('closed');
	});
	//extra link for general becouse click function was add to often
	jQuery('.widget-general-title').children('span').after(' <span style="float: right;" class="widget-general-edit-item">'+MiniMetaL10n.edit+'</span>');
	jQuery('.widget-general-edit-item').click( function() {
		jQuery(this).parent().parent().toggleClass('closed');
	});
	
	jQuery('#widget-logout-list,#widget-login-list').sortable({ handle: 'h4',axis: 'y'});
	jQuery('#widget-logout-list,#widget-login-list').disableSelection();
	jQuery('form').submit(function() {
		jQuery('#orderingin').val(jQuery('#widget-login-list').sortable('serialize'));
		jQuery('#orderingout').val(jQuery('#widget-logout-list').sortable('serialize'));
	});

});
