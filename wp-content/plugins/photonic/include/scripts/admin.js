jQuery(document).ready(function($) {
	window.photonicManagePanels = function(tabName) {
		var panels = $('.photonic-options-panel');
		var all_hidden = true;
		panels.each(function() {
			var current = $(this);
			if (current.attr('id') == tabName) {
				current.show();
				all_hidden = false;
			}
			else {
				current.hide();
			}
		});
		if (all_hidden) {
			panels.eq(0).show();
		}
	};

	window.photonicManageTabs = function(tabName) {
		var tabs = $('.photonic-section-tabs a');
		var no_active = true;
		$(tabs).each(function() {
			var current = $(this);
			if (current.attr('href').substr(1) == tabName) {
				current.addClass('active');
				no_active = false;
			}
			else {
				current.removeClass('active');
			}
		});
		if (no_active) {
			tabs.eq(0).addClass('active');
		}
	};

	window.photonicSetBorder = function(thisId, specificElement, specificColor) {
		var edges = ['top', 'right', 'bottom', 'left'];
		var border = '';
		for (var x in edges) {
			var edge = edges[x];
			var thisName = thisId + '-' + edge;
			border += edge + '::';
			var colorField = "#" + thisName + "-color";
			if (specificElement != undefined && colorField == '#' + specificElement) {
				border += 'color=' + specificColor + ';';
			}
			else {
				border += 'color=' + ($(colorField).val() == '' ? $(colorField).data('photonicDefaultColor') : $(colorField).val()) + ';';
			}
			border += 'colortype=' + $("input[name=" + thisName + "-colortype]:checked").val() + ';' +
				'style=' + $("#" + thisName + "-style").val() + ';' +
				'border-width=' + $("#" + thisName + "-border-width").val() + ';' +
				'border-width-type=' + $("#" + thisName + "-border-width-type").val() + ';';
			border += '||';
		}
		$('#' + thisId).val(border);
	};

	window.photonicSetBackground = function(thisId, specificElement, specificColor) {
		var thisName = thisId;
		var background = '';
		var colorField = "#" + thisName + "-bgcolor";

		if (specificElement != undefined && colorField == '#' + specificElement) {
			background += 'color=' + specificColor + ';';
		}
		else {
			background += 'color=' + ($(colorField).val() == '' ? $(colorField).data('photonicDefaultColor') : $(colorField).val()) + ';';
		}

		background += 'colortype=' + $("input[name=" + thisName + "-colortype]:checked").val() + ';' +
			'image=' + $("#" + thisName + "-bgimg").val() + ';' +
			'position=' + $("#" + thisName + "-position").val() + ';' +
			'repeat=' + $("#" + thisName + "-repeat").val() + ';' +
			'trans=' + $("#" + thisName + "-trans").val() + ';';

		$('#' + thisName).val(background);
	};

	window.photonicSetBorderOrBackgroundColor = function(element, color) {
		var thisId = $(element).attr('id');
		var container = $(element).parents('.photonic-border-options');
		if (container.length > 0) {// Border
			photonicSetBorder(thisId.substring(0, thisId.indexOf('-')), thisId, color);
		}
		else {
			container = $(element).parents('.photonic-background-options');
			if (container.length > 0) { // Background
				photonicSetBackground(thisId.substring(0, thisId.indexOf('-')), thisId, color);
			}
		}
	};

	photonicManagePanels(Photonic_Admin_JS.category);
	photonicManageTabs(Photonic_Admin_JS.category);

	$('.photonic-section-tabs a').on('click', function(e) {
		e.preventDefault();
		var tab = $(this);
		photonicManagePanels(tab.attr('href').substr(1));
		photonicManageTabs(tab.attr('href').substr(1));
	});

	$(".photonic-options-form :input[type='submit']").click(function() {
		//This is needed, otherwise the event handler cannot figure out which button was clicked.
		photonic_submit_button = $(this);
	});

	$('.photonic-options-form').submit(function(event) {
		var field = photonic_submit_button;
		var value = field.val();

		if (value.substring(0, 5) == 'Reset') {
			if (!confirm("This will reset your configurations to the original values!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
		else if (value.substring(0, 6) == 'Delete') {
			if (!confirm("This will delete all your Photonic configuration options!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
	});

	$('.photonic-options-form .color').wpColorPicker({
		change: function(event, ui) {
			var input = $(event.target);
			photonicSetBorderOrBackgroundColor($(input[0]), ui.color.toString());
		},
		clear: function(event) {
			var input = $($(event.target).siblings('.wp-color-picker')[0]);
			photonicSetBorderOrBackgroundColor($(input[0]), $(input[0]).data('photonicDefaultColor'));
		}
	});

	$('.photonic-border-options input[type="radio"], .photonic-border-options input[type="text"], .photonic-border-options select').change(function(event) {
		var thisId = event.currentTarget.name;
		photonicSetBorder(thisId.substring(0, thisId.indexOf('-')));
	});

	$('.photonic-background-options input[type="radio"], .photonic-background-options input[type="text"], .photonic-background-options select').change(function(event) {
		var thisName = event.currentTarget.name;
		photonicSetBackground(thisName.substring(0, thisName.indexOf('-')));
	});

	$('.photonic-padding-options input[type="text"], .photonic-padding-options select').change(function(event) {
		var thisId = event.currentTarget.id;
		thisId = thisId.substring(0, thisId.indexOf('-'));
		var edges = ['top', 'right', 'bottom', 'left'];
		var padding = '';
		for (var x in edges) {
			var edge = edges[x];
			var thisName = thisId + '-' + edge;
			padding += edge + '::';
			padding += 'padding=' + $("#" + thisName + "-padding").val() + ';' +
				'padding-type=' + $("#" + thisName + "-padding-type").val() + ';';
			padding += '||';
		}
		$('#' + thisId).val(padding);
	});
});
