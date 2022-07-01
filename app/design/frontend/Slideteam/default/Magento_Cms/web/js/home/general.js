require(['jquery','domReady!', 'dropkick', 'icheck','jquery-ui-modules/core', 'jquery-ui-modules/widget','jquery/validate','mage/translate'], function ($) {      

    'use strict';

    var scr_width = screen.width;
    if(scr_width > 768)
    {
        $('.header_panel_wrapper ul.header').removeClass("def-hidden");
    }
    else
    {
        $(".skip-account").click(function(){
            $('.header_panel_wrapper ul.header').removeClass("def-hidden");
        });
    }


    $(document).ready(function () {

        $("input[data-type='customCheck']").each(function() {
            var jQuerythis = $(this);
            jQuerythis.iCheck();
        });

        //Notification Js
        $(".dropnitifctnbtn").click(function() {            
            $('.notiftionDropdown').toggleClass('show');
        });
        $(".notifctn_count").click(function() {
            $('.notiftionDropdown').toggleClass('count_contentshow');
        });
        $(document).on("click", function(event){
            if (!event.target.matches('.dropnitifctnbtn, .dropnitifctnbtn .fa')) {
               $('.notiftionDropdown').removeClass('show'); 
            }
            if (!event.target.matches('.notifctn_count')) {
               $('.notiftionDropdown').removeClass('count_contentshow'); 
            }
        });

        if ($(window).width() > 1024) {
            $(".has-sub-class").on('mouseenter',function() {
                jQuery(this).children('div.sub-menu-header').show(300);
                jQuery(this).children('div.sub-menu-header').addClass('active');
            })
            $(".has-sub-class").on('mouseleave',function() {
                jQuery(this).children('div.sub-menu-header').removeClass('active');
                jQuery(this).children('div.sub-menu-header').stop(true).hide(300);
            });
        }



        $('.sub-nav ul li a').prop('title', function(){
            return this.text;
        });

        $('#mega-3 li a').prop('title',function(){
            return this.text;
        });

        $(".sub-nav").height($("#left-section").height());
        $("#left-section #mega-3").height($("#left-section").height());

        //Prevent Page Reload on all # links
        $("a[href='#']").click(function (e) {
            e.preventDefault();
        });

        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || $(window).width() < 1024) {
            $(document).on('click','.scroll-nav a',function (e) {
                if ($(this).closest('li').find('.sub-nav').length > 0 && !$(this).hasClass('clicked')) {
                    e.preventDefault();
                    $("a").removeClass('clicked');
                    $(this).addClass('clicked');
                }
            })
        }
        
        if ($(window).width() < 1024) {
            $(".sub-nav").height("auto");
            $("#mega-3").height("auto");
            $(".has-sub-class").on('click',function() {
                $(this).children('div.sub-menu-header').toggle();
            })
        }

        $(".toggle-show").click(function () {
            var jQuerythis = $(this);
            jQuerythis.closest(".awesome-powerpoint-step").find(".toggle-hide").stop(true, true).slideToggle()
            jQuerythis.closest(".awesome-powerpoint-step").find(".toggle-show").toggleClass("minus-icon")
        });

        
        // Navigation
        var html = $('html, body'),
                navContainer = $('.nav-container'),
                navToggle = $('.nav-toggle');

        // Nav toggle
        navToggle.on('click', function (e) {
            var jQuerythis = $(this);
            e.preventDefault();
            jQuerythis.toggleClass('is-active');
            navContainer.toggleClass('is-visible');
            html.toggleClass('nav-open');
        });


        //scroll

        $('.scroll').click(function () {
            var target;
            $(".emarsys-currently-trending").each(function (i, element) {
                target = $(element).offset().top;
                if (target - 10 > $(document).scrollTop()) {
                    return false; // break
                }
            });
            $("html, body").animate({
                scrollTop: target - $("header").height() + 25
            }, 800);
        });

        commonLayout();


        //Left Navigation

        /*      $("#left-section > ul").hover(function () {
         $(this).find("p").stop(true, true).show(250);
         });
         
         $("#left-section > li").hover(function () {
         
         $(this).find(".sub-nav").stop(true, true).show(500);
         }, function () {
         $(this).find(".sub-nav").stop(true, true).hide(500);
         });
         
         $("#left-section li .sub-nav").hover(function () {
         $(this).stop(true, true).show();
         }, function () {
         $(this).stop(true, true).hide(250);
         });*/

        if ($(window).width() > 1023) {
            $(".scroll-nav #new-menu li #mega-3 li").hover(function () {
                $(this).find(".sub-nav").stop(true, true).show(500).css('display','table');
            }, function () {
                $(this).find(".sub-nav").stop(true, true).hide(500);
            });
        }

        if ($(window).width() <= 1023) {
            $(".scroll-nav #new-menu #ppt_templates a").click(function () {
                $(this).closest("li").find("#mega-3").toggleClass("clicked");
                $(this).removeClass("clicked");
                $(this).toggleClass("open");
            });

            $(".scroll-nav #new-menu li #mega-3 li a").click(function () {
                var $this = $(this).closest("li");
                $this.find(".sub-nav").toggleClass("clicked");
                $this.toggleClass("open-li");
            });
        }
        HeightColumn();
    });

    jQuery(".custom-dropdown").dropkick({
        mobile: true
    });

    $(window).resize(function () {
        commonLayout();
        HeightColumn();
        if ($(window).width() < 1024) {
            $(".sub-nav").height("auto");
            $("#mega-3").height("auto");
        }

        $(".sub-nav").height($("#left-section").height());
            $("#left-section #mega-3").height($("#left-section").height()); 
        });

        var resize_width = screen.width;
        if(resize_width <= 768)
        {
            $('.header_panel_wrapper ul.header').removeClass("def-hidden");    
        }
        else
        {
            $('.header_panel_wrapper ul.header').removeClass("def-hidden");    
        }


    $(window).load(function() {
        HeightColumn();
        commonLayout();
        if ($(window).width() < 1024) {
            $(".sub-nav").height("auto");
            $("#mega-3").height("auto");
        }

    })

    function commonLayout() {
        //inputbox

        $(".field").each(function () {
            var jQuerythis = $(this);
            jQuerythis.find(".input-text").css({"padding-left": jQuerythis.find("> label").innerWidth() + 12});
        });
    }
    function HeightColumn(){
        $(".research-case-studies li").css("height","auto");
        var HeightArray = 0; var i = 0; var max = 0;
        $(".research-case-studies li").each(function(){
            HeightArray=$(this).height();
            max = Math.max(max,HeightArray);
        });
        $(".research-case-studies li").each(function(){
            if(max != 0){
                $(this).height(max);
            }           
        });
    };
    

    $.validator.addMethod(
        "telephoneValidation",
        function (v) {
            return $.mage.isEmptyNoTrim(v) || /^[\+]{0,1}((\d+)(\-){0,1})*\d+$/.test(v);
        },
        $.mage.__("Allowed digits[0-9] and - followed by + e.g. +91-1234567890 OR 1234567890 ")
    );
})


// Start 932 - Left hand navigation bar overlaps with the footers after successful loading
require(['jquery','domReady!'], function ($) {      

    var wind_width = $(window).width();
    if(wind_width >= 1024)
    {
        var leftNavnHeight = $("#left-section").height();
        var mainContentHeight = $("#maincontent").height();
        if (leftNavnHeight > mainContentHeight) {
            $('.page-main').css('min-height',leftNavnHeight);
        }
    }   
    
});
// End  932 - Left hand navigation bar overlaps with the footers after successful loading 

require(["jquery"], function ($) {
  $(document).ready(function () {
    setTimeout(function() {
      $(".sli_rich:not(:last)").remove();
    }, 4000);
    $(document).on("click", function (event) {
      if (
        !event.target.matches("#sli_autocomplete") &&
        !event.target.matches("#search")
      ) {
        $("#sli_autocomplete").hide();
      }
    });
  });
});