/* Author:

*/


// Global Variables

var isMobile = /Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent);

(function($) {
	$(document).ready(function() {
		$('input, textarea').placeholder();
		$('.icheck').iCheck({
	 		checkboxClass: 'icheckbox',
	        radioClass: 'iradio'
		});
		$(function() {
		    FastClick.attach(document.body);
		});



		$('.slideshow .slider').slick({
            dots: true,
            arrows: false,
            speed: 400,
            draggable: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            customPaging: function(slick,index) {
			    return '<a>' + index + '</a>';
			},
            responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    vertical: false
                }
            }, {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false
                }
            }, {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: false
                }
            }]
        });
		
	});
	
})(jQuery);