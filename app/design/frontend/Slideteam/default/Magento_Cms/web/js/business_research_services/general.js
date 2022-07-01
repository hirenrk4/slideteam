require(['jquery', 'imagesloaded', 'jquery_imagefill'], function (jQuery) {
    function eqHeightCol() {
        jQuery(".request-items ul li").css("height", "auto");
        var HeightArray = 0;
        var i = 0;
        var max = 0;
        jQuery(".request-items ul li").each(function () {
            HeightArray = jQuery(this).height();
            max = Math.max(max, HeightArray);
        });
        jQuery(".request-items ul li").each(function () {
            jQuery(this).height(max);
        });
    }
    ;


    function eqHeightWork() {
        jQuery(".research-sample-work .work-title").css("height", "auto");
        var HeightArray = 0;
        var i = 0;
        var max = 0;
        jQuery(".research-sample-work .work-title").each(function () {
            HeightArray = jQuery(this).height();
            max = Math.max(max, HeightArray);
        });
        jQuery(".research-sample-work .work-title").each(function () {
            if (max != 0) {
                jQuery(this).height(max);
            }
        });
    }
    ;


    function openTab(evt, tabName) {

        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(tabName).style.display = "block";
        // evt.currentTarget.className += " active";

        HeightColumn();
        eqHeightWork();

    }

    jQuery(document).ready(function (evt) {

        //openTab(evt, 'About Us');

        //Prevent Page Reload on all # links
        jQuery("a[href='#']").click(function (e) {
            e.preventDefault();
        });


        jQuery('.business-research-banner .item').show();
        jQuery('.business-banner-content').css("display", "block");
        jQuery(".business-research-banner").imagefill();

        jQuery('.business-research-banner .item').show();
        jQuery('.business-banner-content').css("display", "block");
        jQuery(".business-research-banner").imagefill();
        jQuery(".fancy-thumb-img").imagefill();
        //jQuery(".cx-fancybox").parent().parent().parent().addClass("research-coupen-code");
        eqHeightCol();
        eqHeightWork();
        changeTab()
    });




    jQuery(window).load(function () {
        eqHeightCol();
        eqHeightWork();
        jQuery(".cx-coupon-box").click(function () {
            setTimeout(function () {
                jQuery(".cx-fancybox").parent().parent().parent().addClass("research-coupen-code");
                jQuery(".cx-fancybox").parent().parent().parent().parent().addClass("research-coupen-code-wrap");
            }, 100);

        });

    });

    jQuery(window).resize(function () {
        eqHeightCol();
        eqHeightWork();
    });
    window.onhashchange = function () {
        eqHeightWork();
        changeTab();
    }

});

function changeTab() {
    var lasthashdata = window.location.href.substr(window.location.href.lastIndexOf('#') + 1);
    if (lasthashdata == "about-us")
    {
        var clkobj = jQuery("div.tab").find("#about-us-research");
        openTabs(clkobj, 'About Us');
        jQuery(clkobj).addClass("active");
    } else if (lasthashdata == "our-services")
    {
        var clkobj = jQuery("div.tab").find("#our-services-research");
        openTabs(clkobj, 'Our Services');
        jQuery(clkobj).addClass("active");
    } else if (lasthashdata == "case-studies")
    {
        var clkobj = jQuery("div.tab").find("#case-studies-research");
        openTabs(clkobj, 'Case Studies');
        jQuery(clkobj).addClass("active");
    } else if (lasthashdata == "how-we-work")
    {
        var clkobj = jQuery("div.tab").find("#how-we-work-research");
        openTabs(clkobj, 'How We Work');
        jQuery(clkobj).addClass("active");
    } else if (lasthashdata == "pricing")
    {
        var clkobj = jQuery("div.tab").find("#pricing-research");
        openTabs(clkobj, 'Pricing');
        jQuery(clkobj).addClass("active");
    }
}


function openTabs(evt, tabsName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabsName).style.display = "block";
    //evt.currentTarget.className += " active";
    jQuery(evt.currentTarget).addClass("active");

}
function openTabsForBlog(evt, tabsName, className) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName(className);
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks-custom");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabsName).style.display = "block";
    //evt.currentTarget.className += " active";
    jQuery(evt.currentTarget).addClass("active");

}