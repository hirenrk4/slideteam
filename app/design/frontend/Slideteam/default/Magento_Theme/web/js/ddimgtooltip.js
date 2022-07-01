var ddimgtooltip={

	tiparray:function(){
		var tooltips=[]
		//define each tooltip below: tooltip[inc]=['path_to_image', 'optional desc', optional_CSS_object]
		//For desc parameter, backslash any special characters inside your text such as apotrophes ('). Example: "I\'m the king of the world"
		//For CSS object, follow the syntax: {property1:"cssvalue1", property2:"cssvalue2", etc}

		return tooltips //do not remove/change this line
	}(),

	tooltipoffsets: [20, 0], //additional x and y offset from mouse cursor for tooltips

	//***** NO NEED TO EDIT BEYOND HERE

	tipprefix: 'imgtip', //tooltip ID prefixes

	createtip:function($, tipid, tipinfo){

    if(tipid == "loading_icon")
    {
      /*return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="popup_middlecolumn loading_icon"><img src="'+skin_url+'images/ajax-loader.gif" width="auto" height="auto"/></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)*/

    }
    var myArray = tipid.split('imgtip');
    var id = myArray[1];
    if ($('#'+tipid).length==0){ //if this tooltip doesn't exist yet
    var remaining = $(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining");
    var emarsys = $(".small_image_hover[rel='imgtip["+id+"]']").attr("emarsys");
    var custompage = $(".small_image_hover[rel='imgtip["+id+"]']").attr("custompage");
    var brochurepage = $(".small_image_hover[rel='imgtip["+id+"]']").attr("brochurepage");
    var documentpage = $(".small_image_hover[rel='imgtip["+id+"]']").attr("documentpage");
    var letterhead = $(".small_image_hover[rel='imgtip["+id+"]']").attr("letterhead");
    var a4productpage = $(".small_image_hover[rel='imgtip["+id+"]']").attr("a4productpage");

    var slia4popup = false; 
    if ($(".small_image_hover[rel='imgtip[" + id + "]']").parents('.a4_product').length) {
        slia4popup = true;
    }
    var video = ''
    if(video!="")
    {
        return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><div id="'+id+'">Loading the player...</div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body);
    }
	else
    {
            
            host = window.location.hostname;
            if(slia4popup){
                let remainingImages = $(".small_image_hover[rel='imgtip["+id+"]']").attr("total_images");
                remainingImages = remainingImages - 1 ;
                if( remainingImages > 0){
                return $('<div id="' + tipid + '" class="ddimgtooltip a4-product-popup" />').html(
                      '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+remainingImages+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                } else {
                    return $('<div id="' + tipid + '" class="ddimgtooltip a4-product-popup" />').html(
                           '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)    
                }
            }
            else if(brochurepage == 1)
            {
                if(remaining > 0)
                {
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more page(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }    
            }
            else if(documentpage == 1)
            {
                if(remaining > 0)
                {
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more page(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }   
            }
            else if(letterhead == 1)
            {
                if(remaining > 0)
                {
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more page(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }   
            }
            else if(a4productpage == 1){
                if(remaining > 0){
                    return $('<div id="' + tipid + '" class="ddimgtooltip a4-product-popup" />').html(
                        '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                } else {
                    return $('<div id="' + tipid + '" class="ddimgtooltip a4-product-popup" />').html(
                        '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)    
                }
            }
            else if(custompage == 1)
            {
                if(remaining > 0)
                {
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }    
            }
            else
            {
                if(remaining > 0 && remaining <= 3)
                {
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }
                if(remaining > 3 && host != "search.slideteam.net")
                //if(remaining > 3 && host != "www.slideteamlocal.com")
                {   
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class="top_subheader">This download gives you '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("total_images")+' slides</div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc4")+'></div><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc5")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc6")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span class="updated_bottom">and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("new_remaining")+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }
                else if(remaining > 3 && host == "search.slideteam.net"){
                //else if(remaining > 3 && host == "www.slideteamlocal.com"){
                    return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and '+$(".small_image_hover[rel='imgtip["+id+"]']").attr("remaining")+' more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
                }
            }

            if(remaining ==0)
            {
                return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
            '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div  style="display:none;" class="popup_middlecolumn loading_icon">Loading.... </div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+' ></div><div class="bottom_poupimg_blog clearfix"><div class="small_bottom_imgblog"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc2")+'></div><div class="small_bottom_imgblog no-margin"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc3")+'></div></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
            }

            if(remaining == -1)
            {
            return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
            '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div><div class="bottom_gryrow"><div class="bottom_grycolumn"><span>and 1  more slide(s)...</span></div></div></div><div class="popup_bottomcolumn"></div></div></div></div>').appendTo(document.body)
            }

            if(remaining <= -2)
            {
            return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
            '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".small_image_hover[rel='imgtip["+id+"]']").attr("product_name") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".small_image_hover[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
            }
        }

       }
		return null
	},

	positiontooltip:function($, $tooltip, e){
		var x=e.pageX+this.tooltipoffsets[0], y=e.pageY+this.tooltipoffsets[1]
		var tipw=$tooltip.outerWidth(), tiph=$tooltip.outerHeight(),
		x=(x+tipw>$(document).scrollLeft()+$(window).width())? x-tipw-(ddimgtooltip.tooltipoffsets[0]*2) : x
		y=(y+tiph>$(document).scrollTop()+$(window).height())? $(document).scrollTop()+$(window).height()-tiph-10 : y
		$tooltip.css({left:x, top:y})
	},

	showbox:function($,$tooltip,tipid){
        var myArray = tipid.split('imgtip');
        var id = myArray[1];
        //var video = $(".small_image_hover[rel='imgtip["+id+"]']").attr("video");
        //alert(video);
        //alert(tipid);
        $tooltip.show();

        //jwplayer(id).setup({file:video,height: 349,autostart: true,image: "testing.jpg",width: 446});

        //this.positiontooltip($, $tooltip, e)
	},

	hidebox:function($, $tooltip){
	    $tooltip.hide()
	},


	init:function(targetselector){
		jQuery(document).ready(function($){
            var ddimgscrnwidth = screen.width;
            if(ddimgscrnwidth < 1024)
            {
                return false;
            }
			var tiparray=ddimgtooltip.tiparray
			var $targets=$(targetselector)
			if ($targets.length==0)
				return
			var tipids=[]

            $tooltip_loading=ddimgtooltip.createtip($, "loading_icon", null);
			$targets.each(function(){
				var $target=$(this)
				$target.attr('rel').match(/\[(\d+)\]/) //match d of attribute rel="imgtip[d]"
				var tipsuffix=parseInt(RegExp.$1) //get d as integer
				var tipid=this._tipid=ddimgtooltip.tipprefix+tipsuffix //construct this tip's ID value and remember it
                var t;
                $target.mouseenter(function(e){

                    var $tooltip=ddimgtooltip.createtip($, tipid, tiparray[tipsuffix])
                    var $tooltip=$("#"+this._tipid);
                    var $tooltip_loading_div=$("#loading_icon");

                   /* t = setTimeout(function(){
                       $tooltip.find(".loading_icon").hide();
                       $tooltip.find(".main_area").show();
                       ddimgtooltip.positiontooltip($, $tooltip, e);
                    },2000);

                    $tooltip.find(".main_area").hide();
                    $tooltip.find(".loading_icon").show();
                    */

                    t = setTimeout(function(){
                    $tooltip_loading_div.hide();
                    ddimgtooltip.showbox($,$tooltip,tipid);
                    },500);

                    //ddimgtooltip.showbox($tooltip_loading_div);

				})
				$target.mouseleave(function(e){
                    clearTimeout(t);
                    $("#"+this._tipid).hide();
                    $("#loading_icon").hide();
               })
				$target.mousemove(function(e){
				    var $tooltip=$("#"+this._tipid)
					ddimgtooltip.positiontooltip($, $tooltip, e)

                    var $tooltip_loading_div=$("#loading_icon");
                    ddimgtooltip.positiontooltip($, $tooltip_loading_div, e)

				})

			})

		})
	}
}

//ddimgtooltip.init("targetElementSelector")
ddimgtooltip.init("*[rel^=imgtip]");
jQuery(window).load(function() {
    ddimgtooltip.init("*[rel^=imgtip]");
});
jQuery(document).ajaxComplete(function(){
    ddimgtooltip.init("*[rel^=imgtip]");
});