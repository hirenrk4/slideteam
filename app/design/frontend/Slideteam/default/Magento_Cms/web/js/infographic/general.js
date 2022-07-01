require(['jquery', 'owl_carausel'], function ($) {
    $(document).ready(function () {
        //Prevent Page Reload on all # links
        $("a[href='#']").click(function (e) {
            e.preventDefault();
        });
        
        $("#infographic-slider").owlCarousel({
            autoplay: true,
            navigation: true,
            pagination: false,
            loop: true,
            autoplayTimeout: 2000,
            items: 1,
            itemsCustom: false,
            rewindNav: false,
            lazyLoad: false,
            scrollPerPage: true,
            navigationText: ["", ""],
            navText: ["", ""],
            nav: true,
            navRewind: false,
        });
        $(".owl-prev").css("display", "none");
        $(".owl-next").css("display", "none");
    });
    equalheight = function (container) {
        var currentTallest = 0,
                currentRowStart = 0,
                rowDivs = new Array(),
                $el,
                topPosition = 0;
        $(container).each(function () {
            $el = $(this);
            $($el).height('auto')
            topPostion = $el.position().top;
            if (currentRowStart != topPostion) {
                for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                    rowDivs[currentDiv].height(currentTallest);
                }
                rowDivs.length = 0;
                currentRowStart = topPostion;
                currentTallest = $el.height();
                rowDivs.push($el);
            } else {
                rowDivs.push($el);
                currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
            }
            for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
                rowDivs[currentDiv].height(currentTallest);
            }
        });
    }
    function customEqualH() {
        equalheight('.request-infographic-yellow .eq-block');
        equalheight('.infographic-pricing-block .eq-block');
    }
    $(document).ready(function () {
        customEqualH();
        $(".owl-item img").css("display", "block");
        $(".owl-prev").css("display", "block");
        $(".owl-next").css("display", "block");
    });
    $(window).resize(function () {
        customEqualH();
    });
});
require(['jquery', 'magnific_popup'], function ($) {

    $(document).ready(function(){
        $('a.grouped_sample_image').magnificPopup({
            type: 'image',
            mainClass: 'mfp-img-mobile',
            tLoading: '',
            gallery: {enabled: true},
            callbacks: {
                buildControls: function () {
                    // re-appends controls inside the main container
                    this.contentContainer.append(this.arrowLeft.add(this.arrowRight));
                },
                resize:changeImgSize,
                imageLoadComplete:changeImgSize,
                change:changeImgSize,
            },
            image:{
                markup: '<div class="mfp-figure">'+
                    '<div class="mfp-close"></div>'+
                    '<div class="mfp-img"></div>'+
                    '</div>', // Popup HTML markup. `.mfp-img` div will be replaced with img tag, `.mfp-close` by close button
            },
            closeMarkup:'<button title="Close (Esc)" type="button" class="mfp-close"></button>',
        })
    });
    function changeImgSize(){
            var img = this.content.find('img');
            img.css('max-height', '');
        }
});
