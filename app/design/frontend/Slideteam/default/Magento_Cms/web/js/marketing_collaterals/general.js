require(['jquery', 'imagesloaded', 'owl_carausel', 'jquery_imagefill', 'imagesloaded_pkgd_min'], function ($) {
    function eqHeightCol() {
        $(".request-items ul li").css("height", "auto");
        var HeightArray = 0;
        var i = 0;
        var max = 0;
        $(".request-items ul li").each(function () {
            HeightArray = $(this).height();
            max = Math.max(max, HeightArray);
        });
        $(".request-items ul li").each(function () {
            $(this).height(max);
        });
    }
    ;



    $(document).ready(function () {
        //Prevent Page Reload on all # links
        $("a[href='#']").click(function (e) {
            e.preventDefault();
        });

        $(".banner").imagefill();

        
        $("#owl-demo").owlCarousel({
            navigation: true,
            pagination: false,
            items: 7,
            itemsCustom: false,
            itemsDesktop: [1023, 6],
            itemsDesktopSmall: [900, 5],
            itemsTablet: [600, 3],
            itemsMobile: [479, 2],
            rewindNav: false,
            lazyLoad: false,
            scrollPerPage: true,
            navigationText: ["", ""],
        });
    });


    $(window).load(function () {
        eqHeightCol();
    });

    $(window).resize(function () {
        eqHeightCol();
    });
});