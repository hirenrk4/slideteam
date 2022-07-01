jQuery.noConflict();
var clik_portfolio = 0;
function eqHeightCol(){
	jQuery(".request-items ul li").css("height","auto");
	var HeightArray = 0; var i = 0; var max = 0;
	jQuery(".request-items ul li").each(function(){
		HeightArray=jQuery(this).height();
		max = Math.max(max,HeightArray);
	});
	jQuery(".request-items ul li").each(function(){
		jQuery(this).height(max);
	});
};


function eqHeightPrice(){
	if(jQuery(window).width() <= 767) {
		jQuery(".custom-pricing .eq-height").height("auto");
	} else {
		var HeightArray = 0; var i = 0; var max = 0;
		jQuery(".custom-pricing .eq-height").each(function(){
			HeightArray=jQuery(this).height();
			max = Math.max(max,HeightArray);
		});
		jQuery(".custom-pricing .eq-height").each(function(){
			jQuery(this).height(max);
		});
	}
	
};



function equalHeight() {
	jQuery.fn.extend({
		equalHeights: function() {
			var top = 0;
			var row = [];
			var classname = ('equalHeights' + Math.random()).replace('.', '');
			jQuery(this).each(function() {
				var thistop = jQuery(this).offset().top;
				if (thistop > top) {
					jQuery('.' + classname).removeClass(classname);
					top = thistop;
				}
				jQuery(this).addClass(classname);
				jQuery(this).height('auto');
				var h = (Math.max.apply(null, jQuery('.' + classname).map(function() {
					return jQuery(this).outerHeight();
				}).get()));
				jQuery('.' + classname).height(h);
			}).removeClass(classname);
		}
	});
	if(jQuery(window).width() < 767) {
		jQuery('.category-column .test-content').height("auto");
	}
	else {
		jQuery('.category-column .test-content').equalHeights();
	}
	jQuery('.category-column .tab-para').equalHeights();
}


require(['jquery'],function($) {
	$(document).ready(function() {

	//Prevent Page Reload on all # links
	$("a[href='#']").click(function(e) {
		e.preventDefault();
	});

	// $("#our-service-tab-content").show();
   
	$("a.what-we-do").on("click",function(){
		$(".tab_content").hide();
    	var activeTab = $(this).attr("class");		
    	$("."+activeTab).fadeIn();		
    	
    	$("ul.tabs li").removeClass("active");
    	$(".our-service-tab").addClass("active");
		
    	$(".tab_drawer_heading").removeClass("d_active");
    	$(".tab_container #our-service-tab").addClass("d_active");
    	$("#our-service-tab-content").show();
    	
		equalHeight();
		
		if($(window).width() < 770){
			$('html, body').animate({
				scrollTop: $(".tab_container").offset().top-100
			}, 1000);	
		}
		else
		{
	   		$('html, body').animate({
				scrollTop: $(".tab_container").offset().top-270
			}, 1000);
		}
	});
	
    $("ul.tabs li").click(function() {
		
    	$(".tab_content").hide();
		$("ul.tabs li").removeClass("active");
    	var activeTab = $(this).attr("class");
		$(".tab_drawer_heading").removeClass("d_active");
		if(activeTab !== "our-portfolio-tab")
		{
	    	$("."+activeTab).addClass("active");		    	
	    	$(".tab_container #"+activeTab).addClass("d_active");
	    	$("#"+activeTab+"-content").show();
	    	equalHeight();
		}
    });
    
    if($(window).width() < 770){
    	$(".tab_drawer_heading").click(function(){
    		var d_active_hasclass = $(this).hasClass("d_active");
			if(!d_active_hasclass)
			{		
				$(document).find(".tab_drawer_heading").each(function() {				
					$(this).removeClass("d_active");		
					var CurrentElement = $(this).attr("id");
					$("li."+CurrentElement).removeClass("active");			
					$(this).next(".tab_content").hide();
				});			
				$(this).addClass("d_active");	
				var CurrentElement = $(this).attr("id");
				$("li."+CurrentElement).addClass("active");
	    		$(this).next(".tab_content").show();
			   
				$('html, body').animate({
			        scrollTop: $(".tab_container").offset().top-100
			    }, 1000);			  
			}
		});
    }
	equalHeight();
	});
})


jQuery(window).load(function() {
	eqHeightCol();	
	eqHeightPrice();
	equalHeight();
});

jQuery(window).resize(function(){
	eqHeightCol();
	eqHeightPrice();
	equalHeight();
});