(function ($) {
    "use strict";

    var venueAddress = "Grand Place, 1000, Brussels", // Venue Address
        eventInfo = ["Brussels, Belgium", "18 December 2014"]; // Event Info
			console.log("launcha");

    var fn = {

        // Launch Functions
        Launch: function () {
			console.log("launsch");
            fn.RegisterForm();
            fn.AddToJimboForm();
            fn.ContactForm();
        },



        // Google Maps
        GoogleMaps: function () {

            $("#map-canvas").gmap3({
                map: {
                    options: {
                        maxZoom: 15,
                        streetViewControl: false,
                        panControl: false,
                        zoomControl: true,
                        scrollwheel: false,
                        mapTypeControl: false

                    }
                },
                marker: {
                    address: venueAddress,
                    options:{ icon: "images/pin.png" }
                }
            },
                "autofit");
        },



        // Align Slider Images
        MainSliderAlign: function () {
            var imageWidth, imageHeight, widthFix, heightFix, image = $('.header img');
            function centerImage() {
                imageWidth = image.width();
                imageHeight = image.height();
                widthFix = imageWidth / 2;
                heightFix = imageHeight / 2;
                image.css({'margin-left' : -widthFix, 'margin-top' : -heightFix});
            }
            $(window).on("load resize", centerImage);
        },



        // Main FlexSlider
        MainSlider: function () {
            $(window).load(function () {
                $('#header-background').flexslider({
                    noCSS: true,
                    touch: false,
                    controlNav: false,
                    directionNav: false,
                    animation: "fade",
                    start: function () {
                        $('#preloader').addClass('ready');
                    }
                });
            });
        },



        // Stellar
        Stellar: function() {
            if(!(navigator.userAgent.match(/iPhone|iPad|iPod|Android|BlackBerry|IEMobile/i))) {
                $.stellar({
                    horizontalScrolling: false,
                    positionProperty: 'transform',
                    hideDistantElements: false
                });
            }
        },



        // One Page Navigation
        Navigation: function () {
            $('#navigation').onePageNav({
                currentClass: 'active',
                scrollSpeed: 1000,
                scrollOffset: 75,
                scrollThreshold: 0.2,
                easing: 'swing'
            });
        },



        // Carousel
        Carousel: function () {
            var owl = $("#carousel");
             
            owl.owlCarousel({
                theme: "carousel",
                navigation: true,
                pagination: false,
                itemsCustom : [
                    [970, 1],
                    [768, 2],
                    [240, 1]
                ],
                slideSpeed: 400,
                autoPlay: 4000,
                mouseDrag: false
            });
        },



        // Slider
        Slider: function () {
            var owl = $("#slider");
             
            owl.owlCarousel({
                theme: "slider",
                navigation : true,
                pagination: false,
                singleItem: true,
                slideSpeed: 400,
                mouseDrag: false
            });
        },



        // Menu
        Menu: function () {
            var menuToggle = $("#menu-toggle");

            menuToggle.click(function () {
                if ($(this).parent().hasClass('expand')) {
                    $(this).parent().removeClass('expand')
                } else {
                    $(this).parent().addClass('expand');
                }
            });

        },



        // Wow
        Wow: function() {
            var wow = new WOW(
                {
                    boxClass: 'wow',
                    offset: 0,
                    mobile: false
                }
            );
            wow.init();
        },



        // Sticky Menu
        StickyMenu: function () {
            var nav = $('#navigation-wrap'), 
                navOffset;

            function reCalc () {
                navOffset = nav.offset().top;
            };

            reCalc();
            $(window).resize(reCalc).scroll(function () {
                var winScroll = $(this).scrollTop();

                if (winScroll > navOffset) {
                    nav.addClass('sticky');
                } else {
                    nav.removeClass('sticky');
                }
            });
        },



        // Registration Form
        RegisterForm: function () {
            $("#reset-password-form").submit(function (e) {
                e.preventDefault();
                var password = $("#password").val(),
                    confirmpassword = $("#confirm").val(),
                    dataString = 'password=' + password + '&confirmpassword=' + confirmpassword;
					
                if (password.valueOf() == confirmpassword.valueOf() && password != "") {
                        $('input#confirm').removeClass('not-valid');
                        $('input#password').removeClass('not-valid');
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: dataString,
                        success: function () {
                            //$('#reset-password-form .button').addClass('success');
                        $('#submit').html('Password Successfully Changed');
                        }
                    });
                } else {
                    if (password == "") {
                        $('input#password').addClass('not-valid');
                    } else {
                        $('input#password').removeClass('not-valid');
                    }
                    if (confirmpassword == "" || password.valueOf() != confirmpassword.valueOf()) {
                        $('input#confirm').addClass('not-valid');
                    } else {
                        $('input#confirm').removeClass('not-valid');
                    }
                }
                return false;
            });
        },
		
		// Registration Form
        ContactForm: function () {
            $("#contact-form").submit(function (e) {
                e.preventDefault();
                var nam = $("#name").val(),
                    email = $("#email").val(),
                    message = $("#message").val(),
                    dataString = 'password=' + password + '&confirmpassword=' + confirmpassword;
					
                if (password.valueOf() == confirmpassword.valueOf() && password != "") {
                        $('input#name').removeClass('not-valid');
                        $('input#message').removeClass('not-valid');
                        $('input#message').removeClass('not-valid');
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: dataString,
                        success: function () {
                            //$('#reset-password-form .button').addClass('success');
                        $('#submit').html('Password Successfully Changed');
                        }
                    });
                } else {
                    if (name == "") {
                        $('input#name').addClass('not-valid');
                    } else {
                        $('input#name').removeClass('not-valid');
                    }
                    if (email == "" || isValidEmail(email) == false) {
                        $('input#email').addClass('not-valid');
                    } else {
                        $('input#email').removeClass('not-valid');
                    }
                    if (message == "") {
                        $('input#message').addClass('not-valid');
                    } else {
                        $('input#message').removeClass('not-valid');
                    }
                }
                return false;
            });
        },
		// Registration Form
        AddToJimboForm: function () {
            $("#add-to-jimbo-form").submit(function (e) {
                e.preventDefault();
				var price = $("#price").val();
				price = Number(price)*Math.pow(10,6);
                var itemurl = $("#itemurl").val(),
                    imageurl = $("#imageurl").val(),
                    title = $("#title").val(),
                    description = $("#description").val(),
                    quantity = $("#quantity").val(),
                    dataString = 'img=' + imageurl + '&url=' + itemurl + '&title=' + title + '&price=' + price + '&description=' + description + "&quantity=" + quantity;
                if (itemurl != "" && imageurl != "" && title != "" && price != "") {
                        $('input#itemurl').removeClass('not-valid');
                        $('input#imageurl').removeClass('not-valid');
                        $('input#title').removeClass('not-valid');
                        $('input#price').removeClass('not-valid');
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: dataString,
                        success: function () {
                            //$('#reset-password-form .button').addClass('success');
                        $('#submit').html('Item Added');
                        }
                    });
                } else {
                    if (itemurl == "") {
                        $('input#itemurl').addClass('not-valid');
                    } else {
                        $('input#itemurl').removeClass('not-valid');
                    }
                    if (imageurl == "") {
                        $('input#imageurl').addClass('not-valid');
                    } else {
                        $('input#imageurl').removeClass('not-valid');
                    }
                    if (title == "") {
				console.log("asdf3");
                        $('input#title').addClass('not-valid');
                    } else {
				console.log("asdf2");
                        $('input#title').removeClass('not-valid');
                    }
                    if (price == "") {
                        $('input#price').addClass('not-valid');
                    } else {
                        $('input#price').removeClass('not-valid');
                    }
                }
                return false;
            });
        },



        // Subscribe Form
        SubscribeForm: function () {
            $("#subscribe-form").submit(function (e) {
                e.preventDefault();
                var smail = $("#smail").val(),
                    sname = $("#sname").val(),
                    dataString = '&smail=' + smail + '&sname=' + sname;
                function isValidEmail(emailAddress) {
                    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                    return pattern.test(emailAddress);
                }

                if (isValidEmail(smail)) {
                    $.ajax({
                        type: "POST",
                        url: "php/process.php",
                        data: dataString,
                        success: function () {
                            $('#subscribe-form .button').addClass('success');
                        }
                    });
                } else {
                    $('input#subscriber').addClass('not-valid');
                }
                return false;
            });
        },




        // Apps
        Apps: function () {
            // Fancy Select
            $('select').fancySelect();

            // Accordion
            $('.accordion').accordion();

            // Placeholders
            $('input, textarea').placeholder();

            // Speakers
            $(function () {
                var speaker = $(".speaker");
                speaker.hover( function () {
                    $(this).toggleClass("active");
                });
            });

            // Typed
            $(function () {
                $(".typed").typed({
                strings: eventInfo,
                typeSpeed: 50,
                backDelay: 2000,
                loop: true
                });
            });
        }

    };

    $(document).ready(function () {
        fn.Launch();
    });

})(jQuery);