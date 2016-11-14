jQuery(document).ready(function($) {
	$('.su-vote').prependTo($('#wpcontent')).slideDown();
	$('.su-vote-action').on('click', function(e) {
		var $this = $(this);
		e.preventDefault();
		$.ajax({
			type: 'get',
			url: $this.attr('href'),
			beforeSend: function() {
				$('.su-vote').slideUp();
				if (typeof $this.data('action') !== 'undefined') window.open($this.data('action'));
			},
			success: function(data) {}
		});
	});
});