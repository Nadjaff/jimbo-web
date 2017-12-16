(function ($) {
    "use strict";

    var venueAddress = "Grand Place, 1000, Brussels", // Venue Address
        eventInfo = ["Brussels, Belgium", "18 December 2014"]; // Event Info

    var fn = {

        // Launch Functions
        Launch: function () {
            fn.RegisterForm();
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
                    confirmpassword = $("#confirmpassword").val(),
                    dataString = 'password=' + password + '&confirmpassword=' + confirmpassword;
					alert(password);
                if (password == confirmpassword && password != "") {
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: dataString,
                        success: function () {
                            $('#register-form .button').addClass('success');
                        }
                    });
                } else {
                    if (password == "") {
                        $('input#password').addClass('not-valid');
                    } else {
                        $('input#password').removeClass('not-valid');
                    }
                    if (confirmpassword == "" || password != confirmpassword) {
                        $('input#confirmpassword').addClass('not-valid');
                    } else {
                        $('input#confirmpassword').removeClass('not-valid');
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