require(['jquery'], function ($) {
    $.fn.extend({
        equalHeights: function () {
            var top = 0;
            var row = [];
            var classname = ('equalHeights' + Math.random()).replace('.', '');
            $(this).each(function () {
                var thistop = $(this).offset().top;
                if (thistop > top) {
                    $('.' + classname).removeClass(classname);
                    top = thistop;
                }
                $(this).addClass(classname);
                $(this).height('auto');
                var h = (Math.max.apply(null, $('.' + classname).map(function () {
                    return $(this).outerHeight();
                }).get()));
                $('.' + classname).height(h);
            }).removeClass(classname);
        }
    });

    $.fn.extend({
        equalHeightsWork: function () {
            var top = 0;
            var row = [];
            var classname = ('equalHeightsWork' + Math.random()).replace('.', '');
            $(this).each(function () {
                var thistop = $(this).offset().top;
                if (thistop > top) {
                    $('.' + classname).removeClass(classname);
                    top = thistop;
                }
                $(this).addClass(classname);
                $(this).height('auto');
                var h = (Math.max.apply(null, $('.' + classname).map(function () {
                    return $(this).outerHeight();
                }).get()));
                $('.' + classname).height(h);
            }).removeClass(classname);
        }
    });

    $.fn.extend({
        equalHeightsItem: function () {
            var top = 0;
            var row = [];
            var classname = ('equalHeightsItem' + Math.random()).replace('.', '');
            $(this).each(function () {
                var thistop = $(this).offset().top;
                if (thistop > top) {
                    $('.' + classname).removeClass(classname);
                    top = thistop;
                }
                $(this).addClass(classname);
                $(this).height('auto');
                var h = (Math.max.apply(null, $('.' + classname).map(function () {
                    return $(this).outerHeight();
                }).get()));
                $('.' + classname).height(h);
            }).removeClass(classname);
        }
    });

    function logoEqualheight() {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            if ($(window).width() <= 991) {
                $('.equal-height').height("auto");
            } else {
                $('.equal-height').equalHeights();
            }
        } else {
            $('.equal-height').equalHeights();
        }
    }

    $(window).load(function () {
        logoEqualheight();
    });

    $(window).resize(function () {
        logoEqualheight();
    });
});
require(['jquery', 'owl_carausel', 'imagesloaded_pkgd_min'], function ($) {
    $(document).ready(function () {
        //Prevent Page Reload on all # links
        $("a[href='#']").click(function (e) {
            e.preventDefault();
        });
        $('.slider-content .owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            responsiveClass: true,
            items: 1,
            autoplay: true,
            navigationText: ["", ""],
            nav: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true

                    // responsive:{
                    // 	0:{
                    // 		items:1,
                    // 		nav:true
                    // 	},
                    // 	600:{
                    // 		items:3,
                    // 		nav:false
                    // 	},
                    // 	1000:{
                    // 		items:5,
                    // 		nav:true,
                    // 		loop:false
                    // 	}
                    // }
        });
        $('.slider-content .owl-carousel').on('mouseover', function (e) {
            $(this).trigger('stop.owl.autoplay');
        });
        $('.slider-content .owl-carousel').on('mouseleave', function (e) {
            $(this).trigger('play.owl.autoplay');
        });

    });
});

require(['jquery', 'magnific_popup'], function ($) {
    $(document).ready(function () {
        $('a.grouped_elements').magnificPopup({
            type: 'image',
            mainClass: 'logo-and-brand-wrap',
            tLoading: '',
            fixedContentPos: false,
            gallery: {
                enabled: true,
                tCounter: '%curr% / %total%',
                navigateByImgClick: true,
            },
            callbacks: {
                buildControls: function () {
                    // re-appends controls inside the main container
                    this.contentContainer.append(this.arrowLeft.add(this.arrowRight));
                },
                resize: changeImgSize,
                imageLoadComplete: changeImgSize,
                change: changeImgSize,
            },
            image: {
                verticalFit: true,
                markup: '<div class="mfp-figure">' +
                        '<div class="mfp-close"></div>' +
                        '<div class="mfp-img"></div>' +
                        '<div class="mfp-bottom-bar">' +
                        '<div class="mfp-counter"></div>' +
                        '</div>' +
                        '</div>', // Popup HTML markup. `.mfp-img` div will be replaced with img tag, `.mfp-close` by close button

            },
            closeMarkup: '<button title="Close (Esc)" type="button" class="mfp-close"></button>',
        });
    });
    function changeImgSize() {
        var appVersion = navigator.appVersion;
        var wrapper = $(".mfp-wrap");

        var img = this.content.find('img');
        var popupH;

        if ((/iphone|ipad|ipod/gi).test(appVersion) || $(window).height() < 500) {
            wrapper.css("height", $(window).height() / 3);
            popupH = $(window).height() * 0.65;
        } else {
            popupH = $(window).height() * 0.75;
        }
        img.css("max-height", popupH);
    }
});
