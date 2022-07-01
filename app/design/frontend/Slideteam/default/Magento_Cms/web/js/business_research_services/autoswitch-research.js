require(['jquery', 'imagesloaded', 'jquery_imagefill'], function ($) {
    function changeTab()
    {
        var lasthashdata = window.location.href.substr(window.location.href.lastIndexOf('#') + 1);
        if (lasthashdata == "about-us")
        {
            var clkobj = $("div.tab").find("#about-us-research");
            openResearchTabs(clkobj, 'About Us');
            $(clkobj).addClass("active");
        } else if (lasthashdata == "our-services")
        {
            var clkobj = $("div.tab").find("#our-services-research");
            openResearchTabs(clkobj, 'Our Services');
            $(clkobj).addClass("active");
        } else if (lasthashdata == "case-studies")
        {
            var clkobj = $("div.tab").find("#case-studies-research");
            openResearchTabs(clkobj, 'Case Studies');
            $(clkobj).addClass("active");
        } else if (lasthashdata == "how-we-work")
        {
            var clkobj = $("div.tab").find("#how-we-work-research");
            openResearchTabs(clkobj, 'How We Work');
            $(clkobj).addClass("active");
        } else if (lasthashdata == "pricing")
        {
            var clkobj = $("div.tab").find("#pricing-research");
            openResearchTabs(clkobj, 'Pricing');
            $(clkobj).addClass("active");
        }

    }
    function openResearchTabs(evt, tabsName) {
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
        $(evt).addClass("active");

        HeightColumn();
        eqHeightWork();
    }
    window.onhashchange = function () {
        changeTab();
    }
});