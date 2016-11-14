if(typeof $ == 'undefined'){
	var $ = jQuery;
}
if(typeof $f == 'undefined'){
	var $f = jQuery;
}

if(typeof fastwp_debug == 'undefined'){
	fastwp_debug = false;
}
if(typeof fastwp_owl_loaded == 'undefined'){
	var fastwp_owl_loaded = false;
}

/* Toggle theme function */
var scrollable_sections = 1;

 /* ==============================================
Home Super Slider (images)
=============================================== */
jQuery('#slides, .fastwp-superslides').superslides({
      animation: 'fade',
	  play:false
    });
	
/* ==============================================
Flex Slider Home Main
=============================================== */	
	
jQuery(function(){
 
	'use strict';
	if(typeof $('body').flexslider == 'function'){	
      $('.flexslider').flexslider({
        animation: "slide",
		selector: ".home-slides > li",
		controlNav: true,
		directionNav: false ,
		direction: "vertical",
        start: function(slider){
          $('body').removeClass('loading'); 
        }
      });
}else { if(fastwp_debug == true) console.warn('FlexSlider not loaded... '); }
 });
	

/* ==============================================
Flex Slider Blog
=============================================== */	
	
jQuery(window).load(function(){
	 'use strict';
	fastwp_init_flex();
 });

 function fastwp_init_flex(){
 if(typeof $('body').flexslider == 'function'){	
      $('.post-slide').flexslider({
        animation: "fade",
		selector: ".post-slides > .item",
		controlNav: false,
		directionNav: true ,
		direction: "vertical",
		slideshow:true,
		slideshowSpeed:7000,
		animationSpeed:1000,
		smoothHeight: false,
        start: function(slider){
          $('body').removeClass('loading'); 
        }
      });
}else { if(fastwp_debug == true) console.warn('FlexSlider not loaded... ');}
 }

/* ==============================================
Drop Down Menu Fade Effect
=============================================== */	

$('.nav-toggle').hover(function() {
    $(this).find('.dropdown-menu').first().stop(true, true).fadeIn(400);
    }, function() {
    $(this).find('.dropdown-menu').first().stop(true, true).fadeOut(400);
    });

/* ==============================================
Drop Down Menu For Mobile
=============================================== */	

$('.mobile-toggle').hover(function() {
    $(this).find('.dr-mobile').first().stop(true, true).slideToggle(400);
    }, function() {
    $(this).find('.dr-mobile').first().stop(true, true).slideToggle(400);
    });
	
/* ==============================================
Pretty Photo
=============================================== */	
	
	jQuery(document).ready(function(){
    jQuery("a[data-rel^='prettyPhoto']").prettyPhoto({
        theme: "light_square"
    });
  });

/* ==============================================
Scroll Navigation
=============================================== */	

$(function() {
		$('.scroll').bind('click', function(event) {
			var $anchor = $(this);
			if(typeof $anchor.data('hash') == 'string' && $anchor.data('hash').length > 2){
				var hash 	= $anchor.data('hash');
				var headerH = $('#navigation').outerHeight();
				$('html, body').stop().animate({
					scrollTop : $(hash).offset().top - headerH + "px"
				}, 1200, 'easeInOutExpo');
				event.preventDefault();
			}
		});
	});

/* ==============================================
Our Works / isotope Scripts
===============================================	*/
	function isotope_num_columns(is_box){
		var _total_width = $('body').width();
		if(_total_width > 625){
			return 3;
		} else if(_total_width > 465){
			return 2;
		} else {
			return 1;
		}
	}

    $(function(){
      var $container = $('.items');
		$container.each(function(e){
			var me = $(this);
			me.isotope({
				resizable: false, 
				masonry: { columnWidth: $(this).width() / isotope_num_columns() },
				itemSelector : '.work'
			});
			me.imagesLoaded(function() {
			    me.isotope('reLayout');
			  });
		});

		$(window).smartresize(function(){
			 $container.each(function(e){
				$(this).isotope({
					resizable: false, 
					masonry: { columnWidth: $(this).width() / isotope_num_columns() },
					itemSelector : '.work'
				});
			});
		});
      
      
      var $optionSets = $('.isotope-options .option-set'),
          $optionLinks = $optionSets.find('a');

      $optionLinks.click(function(){
        var $this = $(this);
        /* don't proceed if already selected */
        if ( $this.hasClass('selected') ) {
          return false;
        }
        var $optionSet = $this.parents('.option-set');
        $optionSet.find('.selected').removeClass('selected');
        $this.addClass('selected');
  
		var $this_container = $this.parents('.fwp-isotope').find('.items');
        /*  make option object dynamically, i.e. { filter: '.my-filter-class' }*/
        var options = {},
            key = $optionSet.attr('data-option-key'),
            value = $this.attr('data-option-value');
        /*  parse 'false' as false boolean*/
        value = value === 'false' ? false : value;
        options[ key ] = value;
        if ( key === 'layoutMode' && typeof changeLayoutMode === 'function' ) {
          /* changes in layout modes need extra logic*/
          changeLayoutMode( $this, options )
        } else {
          /*  otherwise, apply new options*/
          $this_container.isotope( options );
        }
        
        return false;
      });

      $('a[data-rel="zoom-image"]').magnificPopup({type:'image'});

	  $('a[data-rel="expander"]').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();
		var expander_wrap = $(this).parents('.fwp-isotope').find('.fwp-expander');
		var project_url = $(this).attr('href');
		if(typeof expander_wrap.html() != 'string') { if(fastwp_debug == true) console.warn('Expander unable to open: Root element is missing.'); return; }
		if(typeof project_url != 'string' || project_url.length < 3) { if(fastwp_debug == true) console.warn('Expander unable to open: Project url is not correct.'); return; }
		
		if(expander_wrap.css('display') == 'block'){
			expander_wrap.stop().animate({opacity: 0}, 800, function(){ $(this).slideUp().removeClass('expander-open'); });
		}
		$.get(project_url, function(data){
			var data = data.replace('<body', '<body><div id="ajax_body"').replace('</body>','</div></body>');
			var project_data = $(data);
			var project_body = project_data.filter('#ajax_body');
			expander_wrap.html(project_body);
			fastwp_init_flex();
			expander_wrap.stop().delay(300).slideDown( function(){ 
				var new_position = expander_wrap.offset();
				$('body,html').delay(100).animate({scrollTop:new_position.top - 85});
				$(this).animate({opacity: 1}, 800).addClass('expander-open'); 
			});
			$('<div class="expander-close" onClick="closeExpander(this)">X</div>').css({opacity:0}).appendTo('.fwp-expander').animate({opacity:1});
		});
		

	  });

    });
	function closeExpander(t){
		var expander_wrap = $(t).parents('.fwp-expander');
		if(typeof expander_wrap.html() == 'string' && expander_wrap.css('display') == 'block'){
			var new_position = expander_wrap.parents('.works').offset();
			expander_wrap.stop().animate({opacity: 0}, 800, function(){ $(this).slideUp(); });
			$('body,html').delay(200).animate({scrollTop:new_position.top - 100});
		}
	}
 /* ==============================================
Page Loader
=============================================== */

'use strict';

$(window).load(function() {
$("#pageloader").hide();
	$(".loader-item").delay(700).fadeOut();
	$("#pageloader").delay(1200).fadeOut("slow");
});


$(document).ready(function(){
	set_parallax_overlay_height();
	$(window).smartresize(function(){
		set_parallax_overlay_height();
	});
});

function set_parallax_overlay_height(){
	var page_parallax = $('section > .fastwp-parallax-bg');	
	if(page_parallax.length >= 1){
		page_parallax.each(function(e){
			var overlay = $('.relative > .overlay', this);
			if(typeof overlay.html() == 'string'){
				var height = $(this).outerHeight();
				overlay.height(height);
			}
		});
	}

}
/* ==============================================
Parallax Calling
=============================================== */


( function ( $ ) {
'use strict';
$(document).ready(function(){
	$(window).bind('load', function () {
		parallaxInit();						  
	});
	function parallaxInit() {
		testMobile = isMobile.any();
		if (testMobile == null)
		{
			$('.fastwp-parallax-bg, .fastwp-parallax').each(function(e){ 
				var speed = $(this).data('speed');
				if(parseInt(speed) < 1 || speed == '') return;
				var spd = parseFloat(speed / 100);
				if($(this).parent().hasClass('slides-container')){ $(this).parallax("-50%", spd); return; }
				$(this).css({backgroundAttachment:'fixed', backgroundRepeat: 'repeat', position: 'static',backgroundSize:'cover'});
				$(this).parallax("-50%", spd);
			});
	
		}
	}	
});	
//Mobile Detect
var testMobile;
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
}( jQuery ));
	
	
/* ==============================================
Carousel Slider
=============================================== */		
    $(document).ready(function($) {
		'use strict';
		if(fastwp_owl_loaded == false){
			if(fastwp_debug == true){ if(fastwp_debug == true) console.warn('OWL Sliders loaded from theme.'); }
			$(".slide-boxes").owlCarousel();
		
			fastwp_owl_loaded = true;
		} 
    });


/* ==============================================
Navigation Menu, Sticky
=============================================== */

	$(window).load(function(){
		'use strict';
		var spacing = 0;
		if($('#wpadminbar').is(':visible')){
			spacing = $('#wpadminbar').height();
		}
		$("#navigation").sticky({ topSpacing: spacing });
    });
	
/* ==============================================
Load Project
=============================================== */
	$('.carousel').on('slide.bs.carousel', function () {
		'use strict';
		$('.carousel').carousel({
		  interval: 3000
		})
	})

/* ==============================================
Video Script
=============================================== */

jQuery(function(){
    jQuery(".player").mb_YTPlayer();
});
	
 /* ==============================================
Contact Form
=============================================== */	

$(document).ready(function() {

		'use strict';
		
		$('form#contact-us').submit(function() {
			$('form#contact-us .error').remove();
			var hasError = false;
			$('.requiredField').each(function() {
				if($.trim($(this).val()) == '') {
					var labelText = $(this).prev('label').text();
					$(this).parent().append('<span class="error">Your forgot to enter your '+labelText+'.</span>');
					$(this).addClass('inputError');
					hasError = true;
				} else if($(this).hasClass('email')) {
					var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
					if(!emailReg.test($.trim($(this).val()))) {
						var labelText = $(this).prev('label').text();
						$(this).parent().append('<span class="error">Sorry! You\'ve entered an invalid '+labelText+'.</span>');
						$(this).addClass('inputError');
						hasError = true;
					}
				}
			});
			if(!hasError) {
				var formInput = $(this).serialize();
				$.post($(this).attr('action'),formInput, function(data){
					$('form#contact-us').fadeOut("slow", function() {				   
						$(this).before('<div class="mail-message"><p class="mail-head">Thank You!</p><p>Your email has been delivered.</p></div>');
					});
				});
			}
			
			return false;	
		});
	});
	
 /* ==============================================
Animated Items
=============================================== */	
	jQuery(document).ready(function($) {
	
	'use strict';
	
    	$('.animated').appear(function() {
	        var elem = $(this);
	        var animation = elem.data('animation');
	        if ( !elem.hasClass('visible') ) {
	        	var animationDelay = elem.data('animation-delay');
	            if ( animationDelay ) {

	                setTimeout(function(){
	                    elem.addClass( animation + " visible" );
	                }, animationDelay);

	            } else {
	                elem.addClass( animation + " visible" );
	            }
	        }
	    });
});

 /* ==============================================
Revolution Slider
=============================================== */
/*
var revapi;

		jQuery(document).ready(function() {
			if(typeof jQuery('.tp-banner').revolution == 'function'){
			   revapi = jQuery('.tp-banner').revolution(
				{
					delay:9000,
					startwidth:1170,
					startheight:550,
					hideThumbs:10,
					fullWidth:"on",
					forceFullWidth:"on"
				});
			}
		});	*/
		
 /* ==============================================
Fit Videos
=============================================== */
jQuery(document).ready(function() {	
	if(typeof $('<div>').fitVids == 'function'){
		$(".fit-vids").fitVids();
	}else {
		if(fastwp_debug == true)
			console.warn('Fit vids class is not loaded.');
	}
});


/* =============================================== *
scrollable_sections
 * =============================================== */
jQuery(document).ready(function($) {	
	jQuery('.scroll-here').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();
		var _href = $(this).attr('href');
		if(_href.charAt(0) == '#'){
			var _element = $(_href);
			if(typeof _element.html() == 'string'){
				var _navi = $('#navigation');
				var _navi_height = 0;
				if(typeof _navi.html() == 'string'){
					_navi_height = _navi.outerHeight();
				}
				var _position = _element.offset().top - _navi_height;
				jQuery('body,html').stop().animate({scrollTop: _position});
			}
		}
	});
});


/* Pair container heights */
jQuery(function($){
	fastwp_pair_containers();
	$(window).resize(function(){
		fastwp_pair_containers();
	});
});

function queue_container_equalization(){
	setTimeout(function(){ fastwp_pair_containers(); }, 1000);
}

function fastwp_pair_containers(){
	jQuery('.fastwp-pair').each(function(){
		var pair_with = jQuery(this).data('pairwith');
		if(typeof pair_with == 'string' && typeof jQuery(pair_with).html() == 'string'){
			if(window.innerWidth > 640){
				jQuery(pair_with).css({minHeight: jQuery(this).height()});
			}else {
				jQuery(pair_with).css({minHeight: 0});
			}
		}
	})
}

/* ==============================================
Back To Top Button
=============================================== */	
jQuery(document).ready(function($){
	$("#back-top").hide();
	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#back-top').fadeIn();
			} else {
				$('#back-top').fadeOut();
			}
		});

		$('#back-top a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});

});

 /* ==============================================
Count Factors
 =============================================== */	
jQuery(function($) {
/*	$(".facts").appear(function(){console.log(this); */
		$('.facts').each(function(){
	        dataperc = $(this).attr('data-perc'),
			$(this).find('.factor').delay(6000).countTo({
	            from: 50,
	            to: dataperc,
	            speed: 3000,
	            refreshInterval: 50,
	            
        	});  
		});
/*	}); */
});

(function($) {
    $.fn.countTo = function(options) {
        // merge the default plugin settings with the custom options
        options = $.extend({}, $.fn.countTo.defaults, options || {});

        // how many times to update the value, and how much to increment the value on each update
        var loops = Math.ceil(options.speed / options.refreshInterval),
            increment = (options.to - options.from) / loops;

        return $(this).each(function() {
            var _this = this,
                loopCount = 0,
                value = options.from,
                interval = setInterval(updateTimer, options.refreshInterval);

            function updateTimer() {
                value += increment;
                loopCount++;
                $(_this).html(value.toFixed(options.decimals));

                if (typeof(options.onUpdate) == 'function') {
                    options.onUpdate.call(_this, value);
                }

                if (loopCount >= loops) {
                    clearInterval(interval);
                    value = options.to;

                    if (typeof(options.onComplete) == 'function') {
                        options.onComplete.call(_this, value);
                    }
                }
            }
        });
    };

    $.fn.countTo.defaults = {
        from: 0,  // the number the element should start at
        to: 100,  // the number the element should end at
        speed: 1000,  // how long it should take to count between the target numbers
        refreshInterval: 100,  // how often the element should be updated
        decimals: 0,  // the number of decimal places to show
        onUpdate: null,  // callback method for every time the element is updated,
        onComplete: null,  // callback method for when the element finishes updating
    };
})(jQuery);

jQuery(function($){
	if(typeof $(".video-background-player").mb_YTPlayer != 'undefined'){
		$(".video-background-player").mb_YTPlayer();
	}
});

jQuery('.project-item .share a').click(function(e) {
	e.preventDefault();
});

var share_on = {
	twitter: 	function(){
		window.open( 'http://twitter.com/intent/tweet?text='+jQuery(".project-title-nav h2").text() +' '+window.location, "twitterWindow", "width=650,height=350" );
	},
	facebook: 	function(){
		window.open( 'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href), 'facebookWindow', 'width=650,height=350');
	},
	pinterest: 	function(){
		window.open( 'http://pinterest.com/pin/create/bookmarklet/?media='+ jQuery('.project-item img').first().attr('src') + '&description='+jQuery('.project-title-nav h2').text()+' '+encodeURIComponent(location.href), 'pinterestWindow', 'width=750,height=430, resizable=1');
	},
	google: 	function(){
		window.open( 'https://plus.google.com/share?url='+encodeURIComponent(location.href), 'googleWindow', 'width=500,height=500');
	},
	delicious: 	function(){
		window.open( 'http://delicious.com/save?url='+encodeURIComponent(location.href)+'?title='+jQuery(".project-title-nav h2").text(), 'deliciousWindow', 'width=550,height=550, resizable=1');
	},
	linkedin: 	function(){
		window.open( 'http://www.linkedin.com/shareArticle?mini=true&url='+encodeURIComponent(location.href)+'$title='+jQuery(".project-title-nav h2").text(), 'linkedinWindow', 'width=650,height=450, resizable=1');
	}
}

jQuery(function($){
	if(typeof RainyDay == 'function'){
		var elements = $('img.raindrop');
		if(elements.length > 0){
			elements.each(function(){
				var engine = new RainyDay({
                       image: this,
					   parentElement: document.getElementsByClassName('rainy-image-wrap')[0]
                });
                engine.rain([ [1, 2, 8000] ]);
                engine.rain([ [3, 3, 0.88], [5, 5, 0.9], [6, 2, 1] ], 100);
			});
		}
	}
});