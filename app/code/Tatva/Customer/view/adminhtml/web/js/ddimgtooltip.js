var ddimgtooltip={

    tiparray:function(){
        var tooltips=[]
        //define each tooltip below: tooltip[inc]=['path_to_image', 'optional desc', optional_CSS_object]
        //For desc parameter, backslash any special characters inside your text such as apotrophes ('). Example: "I\'m the king of the world"
        //For CSS object, follow the syntax: {property1:"cssvalue1", property2:"cssvalue2", etc}

        return tooltips;
    }(),

    tooltipoffsets: [20, 0], //additional x and y offset from mouse cursor for tooltips

    //***** NO NEED TO EDIT BEYOND HERE

    tipprefix: 'imgtip', //tooltip ID prefixes

    createtip:function($, tipid, tipinfo){

        var myArray = tipid.split('imgtip');
        var id = myArray[1];
        if ($('#'+tipid).length==0){ 

            host = window.location.hostname;

            return $('<div id="' + tipid + '" class="ddimgtooltip" />').html(
                    '<div style="text-align:center"><div class="popup_diarams_column" id="popup"><div class="popup_topcolumn"></div><div class="main_area popup_middlecolumn"><h2>' + $(".admin__control-thumbnail[rel='imgtip["+id+"]']").attr("alt") +'</h2><div class = "top_bigimage"><img class = "lazyload" src ='+$(".admin__control-thumbnail[rel='imgtip["+id+"]']").attr("zoomsrc")+'></div></div><div class="popup_bottomcolumn"></div></div></div>').appendTo(document.body)
        }
        return null;
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
        
        $tooltip.show();
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
                    t = setTimeout(function(){
                    $tooltip_loading_div.hide();
                    ddimgtooltip.showbox($,$tooltip,tipid);
                    },500); 
                });
                $target.mouseleave(function(e){
                    clearTimeout(t);
                    $("#"+this._tipid).hide();
                    $("#loading_icon").hide();
               });
                $target.mousemove(function(e){
                    var $tooltip=$("#"+this._tipid)
                    ddimgtooltip.positiontooltip($, $tooltip, e)

                    var $tooltip_loading_div=$("#loading_icon");
                    ddimgtooltip.positiontooltip($, $tooltip_loading_div, e)

                });
            });
        });
    },

    rmimage:function(targetselector){        
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
                    t = setTimeout(function(){
                    $tooltip_loading_div.hide();
                    ddimgtooltip.showbox($,$tooltip,tipid);
                    },500); 
                });
                $target.mouseleave(function(e){
                    clearTimeout(t);
                    $("#"+this._tipid).hide();
                    $("#loading_icon").hide();
               });
                $target.mousemove(function(e){
                    var $tooltip=$("#"+this._tipid)
                    ddimgtooltip.positiontooltip($, $tooltip, e)

                    var $tooltip_loading_div=$("#loading_icon");
                    ddimgtooltip.positiontooltip($, $tooltip_loading_div, e)

                });
            });
        });
    }
}


//ddimgtooltip.init("targetElementSelector")
ddimgtooltip.init("*[rel^=imgtip]");
jQuery(window).load(function() {
    ddimgtooltip.init("*[rel^=imgtip]");
});
jQuery(document).ajaxComplete(function(){
    setTimeout(function(){
        ddimgtooltip.init("*[rel^=imgtip]");
    },1000);
});