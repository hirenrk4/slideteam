require(['jquery','banner_rotator'], function($) {
	$(document).ready(function(){
		window.lazySizesConfig = window.lazySizesConfig || {};

		//page is optimized for fast onload event
		lazySizesConfig.loadMode = 1;
		
		screeen_width = $( window ).width();
	  
		slideperview_modified = 3;
		if(screeen_width > 1280){
			slideperview_modified = 3;
		}
		if(screeen_width < 751){
			slideperview_modified = 3; 
		}
			
		if($(window).width() <= 767){
			setTimeout(function(){			
				$(".tab_content").hide();       
				$(".our-service-tab").fadeIn();		
				
				$("ul.tabs li").removeClass("active");
				$(".our-service-tab").addClass("active");  	
				
				$("#our-service-tab-content").show();
				equalHeight();			
			}, 100);
		}	
		$("#our-portfolio-tab-content").show();		
		bannerRotatorInitial();
		$(".our-portfolio-tab,#our-portfolio-tab").click(function(){
			$("#our-portfolio-tab-content").show();
			$(".our-portfolio-tab").addClass("active");
			// if(clik_portfolio == 0)
			// {
			// 	clik_portfolio = 1;			
			// 	bannerRotatorInitial();
			// }
		});	
	});
})

require(['jquery','owl_carausel'], function($) {
	$(document).ready(function(){
		$("#owl-demo-testimonial").owlCarousel({
			items:1,
			loop:true,
			autoplay:true,
			autoplaySpeed:2000
		})
	});
});
function bannerRotatorInitial() {
	jQuery('#myRotator').bannerRotator({
        // width:919,
        // height:241,
        // cpanelPosition:'center bottom',
        navButtons:'outside',
        navThumbs:false,
        tooltip:'none',
        depth:'auto',
        thumbnails:'none',
		timer:'none',
        playButton:false,
        nextText:'',
        prevText:''
        // kbEffect:'random',
		// pauseOnHover:'false'		                
    });
}