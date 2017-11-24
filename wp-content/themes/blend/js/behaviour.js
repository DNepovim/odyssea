var trackbackShowText	= behaviourL10n.trackbackShowText,
	trackbackHideText	= behaviourL10n.trackbackHideText,
	errorText 			= behaviourL10n.searchError,
	searchText			= behaviourL10n.searchPrompt+' ', //Stick a space on the end so someone can search for the search prompt if they need to.
	replyHideMany		= behaviourL10n.replyHideMany,
	replyShowMany		= behaviourL10n.replyShowMany,
	replyHideOne		= behaviourL10n.replyHideOne,
	replyShowOne		= behaviourL10n.replyShowOne;
	depth				= behaviourL10n.nestDepth;

jQuery(document).ready(function($){
	//Search Widget
	$("input[name=s]").each(function(){
		if ($(this).attr("value") === "" || $(this).attr("value") === undefined) {
			$(this).attr({value:searchText});
		}

		$(this).focus(function(){
			if ($(this).attr("value") == searchText){
				$(this).attr({value:""});
			}
			$(this).addClass("focused");
		});

		$(this).blur(function(){
			if ($(this).attr("value") === "" || $(this).attr("value") === undefined){
				$(this).attr({value:searchText});
			}
			$(this).removeClass("focused");
		});
	});

	// Stop search submission if nothing has been entered in the search box
	$("form:has(input[name=s])").submit(function(){
		var currentValue = $(this).find("input[name=s]").attr("value");

		if (currentValue === "" || currentValue === searchText || currentValue === undefined) {
			$(this).append('<p class="errorMessage">'+errorText+'</p>').children(".errorMessage").animate({top:"50px",opacity:0},1000,"swing",function(){
					$(this).remove();
				});
			return false;
		} else {
			return true;
		}
	});


	// Stop you from hitting submit on comments until all important fields are filled.
	$("#commentForm").submit(function(){
		var blankFields = false;
		$(this).find(".vital").each(function(){
			var value = $(this).attr("value");
			if (value === undefined || value ===  "") {
				blankFields = true;
				$(this).css({borderColor:"#f00"}).fadeOut(250).fadeIn(250);
			} else {
				$(this).css({borderColor:"#ccc"});
			}
		});

		if (blankFields) {
			return false;
		} else {
			return true;
		}
	});

	// Hide trackbacks from view if they take up too much space. Too much is 250px in my opinion but then I don't really like them. :P
	var trackbackHeight = $("#trackbackList").height();
		//subordinateLevels = $("#commentlist").width() - 25;

	if (trackbackHeight > 250) {
		$("#trackbackList").css({height:trackbackHeight}).hide().before('<strong class="trackbackToggle"><span class="switch"></span><span class="toggleText">'+trackbackShowText+'</span></strong>').prev(".trackbackToggle").click(function(){
			$(this).toggleClass('active').next("#trackbackList").slideToggle('500',function(){
				if ($(this).css('display') === 'none'){
					$(this).prev('.trackbackToggle').children('.toggleText').html(trackbackShowText);
				} else {
					$(this).prev('.trackbackToggle').children('.toggleText').html(trackbackHideText);
				}
			});
		});
	}

	// Collapse comments greter than depth-1 can be changed to any depth if you want to show some of the replies without having to click.
	$(".with-collapse .depth-"+depth+" ul.children").each(function(){
		var posterName = $(this).prev('div.comment-body').find('div.comment-author').children('cite.fn').text(),
			// replyQuant = $(this).find("li.comment").length, // Use to count all subordinate comments
			replyQuant = $(this).children("li.comment").length, // Use to count just those on the next level every reply in the tree
			replyText,
			replyTextHide;

		if (replyQuant == 1) {
			replyText 		= '<span class="switch"></span><span class="toggleText">'+replyShowOne.replace('%name%','<span class="posterName">'+posterName+"'s</span>").replace('%count%',replyQuant)+'</span>';
			replyTextHide 	= '<span class="switch"></span><span class="toggleText">'+replyHideOne.replace('%name%','<span class="posterName">'+posterName+"'s</span>").replace('%count%',replyQuant)+'</span>';
		} else {
			replyText 		= '<span class="switch"></span><span class="toggleText">'+replyShowMany.replace('%name%','<span class="posterName">'+posterName+"'s</span>").replace('%count%',replyQuant)+'</span>';
			replyTextHide 	= '<span class="switch"></span><span class="toggleText">'+replyHideMany.replace('%name%','<span class="posterName">'+posterName+"'s</span>").replace('%count%',replyQuant)+'</span>';
		}
		//alert();
		
		$(this).hide().before('<div class="toggle">'+replyText+'</div>').parent('li').addClass('with-replies').children('div.toggle').click(function(){
			if ($(this).next('ul.children').css('display') === 'none') {
				$(this).html(replyTextHide)
			} else {
				$(this).html(replyText)
			}
			$(this).toggleClass('active').next('ul.children').slideToggle();
		});
	});

	$("input[type='submit']").addClass("submit"); // Add a submit class to all submit buttons. Makes styling submit buttons easier in IE.
	$(".postBody table tr:odd").addClass("alternate"); // Zebra stripe tables the easy way.

	// Fix some IE 6 problems. The sooner ie6 dies the better
	$.each($.browser, function(i, val) {
		if(i=="msie" && val === true && $.browser.version.substr(0,1) == 6){
			// Add IE6 specific stuff here.
			$("#commentlist li.odd > div:not(.toggle)").addClass("commentOdd");
			$("#commentlist li.even > div:not(.toggle)").addClass("commentEven");
			$('#nav li').hover(function(){$(this).addClass('over');},function(){$(this).removeClass('over');});
		}
		if(!$.boxModel){
			var theBody = document.getElementsByTagName("BODY");
			theBody[0].className+=" quirky";
		}
	});
});
