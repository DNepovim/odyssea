$ = jQuery.noConflict();
jQuery(document).ready(function($) {
	$('.photonic-helper-box input[type="button"]').click(function() {
		$('.photonic-waiting').show();
		var formValues = $('#photonic-helper-form').serialize();
		var result = $(this).parent('.photonic-helper-box').find('.result');
		$.post(ajaxurl, "action=photonic_invoke_helper&helper=" + this.id + '&' + formValues, function(data) {
			$(result).html(data);
			$('.photonic-waiting').hide();
		});
	});

	$('.photonic-picasa-refresh').click(function(e) {
		e.preventDefault();
		$('.photonic-waiting').show();
		var result = $(this).parents('.photonic-helper-box').find('.result');
		var args = {'action': 'photonic_obtain_token', 'provider': 'picasa', 'code': $('#photonic-picasa-oauth-code').val(), 'state': $('#photonic-picasa-oauth-state').val() };
		$.post(ajaxurl, args, function(data) {
			data = $.parseJSON(data);
			$(this).remove();
			$("<span class='photonic-helper-button photonic-helper-button-disabled'>" +
				Photonic_Admin_JS.obtain_token == undefined ? 'Step 2: Obtain Token' : Photonic_Admin_JS.obtain_token +
				'</span>').insertBefore(result);
			$(result).html('<strong>Refresh Token:</strong> <code>' + data['refresh_token'] + '</code>');
			$('.photonic-waiting').hide();
		});
	});
});
