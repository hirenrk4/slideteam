require(['jquery', 'imagesloaded', 'jquery_imagefill', 'imagesloaded_pkgd_min', 'domReady!'], function(jQuery) {
	var clsexist = jQuery(".page-wrapper").find(".fullscreen-banner").length;
    if(clsexist == 1)
    {
    	jQuery('.fullscreen-banner').imagefill();
    	jQuery(window).resize(function() {
        	jQuery('.fullscreen-banner').imagefill();
    	});
    }
});require(['jquery','owl_carausel','domReady!'], function (jQuery){
        
        jQuery(document).ready(function() {
              jQuery('#custom-logos').owlCarousel({
                loop: true,
                margin: 10,
                onInitialized: showSlider,
                //dots: true,
                responsiveClass: true,
                responsive: {
                  0: {
                    items: 6,
                    nav: true
                  },
                  600: {
                    items: 9,
                    nav: false,
                    margin: 18
                  },
                   992: {
                    items: 10,
                    nav: false
                  },
                  1365: {
                    items: 12,
                    nav: true,
                    loop: false,
                    margin: 20
                  }
                }
              });
    	});
      function showSlider(e) {
        jQuery(".logos-init-slider").hide();
        jQuery(".logos-original-slider").show();
      }
});