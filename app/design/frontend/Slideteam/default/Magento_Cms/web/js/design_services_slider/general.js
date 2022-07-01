function eqHeighttext(){
    jQuery(".we-offer-block ul li p").css("height","auto");
    var HeightArray = 0; var i = 0; var max = 0;
    jQuery(".we-offer-block ul li p").each(function(){
            HeightArray=jQuery(this).height();
            max = Math.max(max,HeightArray);
    });
    jQuery(".we-offer-block ul li p").each(function(){
            jQuery(this).height(max);
    });
};

function eqHeighttitle(){
    jQuery(".we-offer-block ul li h4").css("height","auto");
    var HeightArray = 0; var i = 0; var max = 0;
    jQuery(".we-offer-block ul li h4").each(function(){
            HeightArray=jQuery(this).height();
            max = Math.max(max,HeightArray);
    });
    jQuery(".we-offer-block ul li h4").each(function(){
            jQuery(this).height(max);
    });
};

function eqlowertext(){
    jQuery(".we-offer-block ul li p.lower-p").css("height","auto");
    var HeightArray = 0; var i = 0; var max = 0;
    jQuery(".we-offer-block ul li p.lower-p").each(function(){
            HeightArray=jQuery(this).height();
            max = Math.max(max,HeightArray);
    });
    jQuery(".we-offer-block ul li p.lower-p").each(function(){
            jQuery(this).height(max);
    });
};

jQuery(window).load(function() {
	eqHeighttext();
        eqHeighttitle();
        eqlowertext();
});

jQuery(window).resize(function(){
	eqHeighttext();
        eqHeighttitle();
        eqlowertext();
});