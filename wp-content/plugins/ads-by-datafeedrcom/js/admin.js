(function($) {

	$('select#dfads_groups').attr('multiple', 'multiple');

	$('.code_type').change(
		function() {
			$('#dfads_build_qs').css('display','block');
			$('#code_middle').css('display','inline');
			if ($(this).val() == 'sc') {
				$('#code_begin, #code_middle, #code_end').removeClass('php').addClass('sc');
				$('#code_begin').text("[dfads params='");
				$('#code_end').text("']");
			} else if ($(this).val() == 'php') {
				$('#code_begin, #code_middle, #code_end').removeClass('sc').addClass('php');
				$('#code_begin').html("&lt;?php <span class='dfads_echo'>echo</span> dfads<span class='dfads_paren'>(</span> <span class='dfads_quote'>'</span>");
				$('#code_end').html("<span class='dfads_quote'>'</span> <span class='dfads_paren'>);</span> ?&gt;");
			}
		}
	);

	$('#dfads_orderby').change(
		function() {
			if ($('#dfads_orderby').find(":selected").val() != 'random' && $('#dfads_orderby').find(":selected").val() != '') {
				$('#dfads_order_field').css('display','block');
			} else {
				$('#dfads_order_field').css('display','none');
			}	
		}
	);
	
	// http://jsfiddle.net/edelman/KcX6A/1507/
	function SelectText(element) {
		var doc = document
			, text = doc.getElementById(element)
			, range, selection
		;    
		if (doc.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(text);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();        
			range = document.createRange();
			range.selectNodeContents(text);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	}
	
    $('#code_area').click(function() {
        SelectText('code_area');
    });
	
	// http://css-tricks.com/snippets/javascript/htmlentities-for-javascript/
	function htmlEntities(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}
	
	// http://api.jquery.com/serializeArray/
	function showValues() {
	
		var fields = $("#dfads_build_qs :input").serializeArray();
	
		$("#code_middle").empty();
		params = new Array;
		groupIds = new Array;
		group_qs = '';

		// For groups
		jQuery.each(fields, function(i, field){
			if (field.name == 'groups') {
				groupIds.push(field.value);
			}
		});

		// Not for groups
		jQuery.each(fields, function(i, field){
			if (field.name != 'groups') {
				if (field.value.length > 0) {
					params.push(field.name+'='+htmlEntities(field.value));
				}
			}
		});

		qs = params.join('&');
	
		if (groupIds.length > 0) {
			amp = '';
			if (qs.length > 0) {
				amp = '&';
			}
			group_qs = 'groups='+groupIds.join(',')+amp;
		}

		$("#code_middle").append(group_qs+qs);
		$("#code_area").hide().fadeIn(500);
	}

	$("#dfads_build_qs :checkbox, #dfads_build_qs :radio, .code_type").click(showValues);
	$("#dfads_build_qs select").change(showValues);
	$("#dfads_build_qs input").keyup(showValues);
	//showValues();
	
})( jQuery );