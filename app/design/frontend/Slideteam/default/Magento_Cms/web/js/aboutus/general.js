require(['jquery'], function ($) {
   $(window).load(function () {
      setTimeout(function () {
         $("img,.portfolio-hero-image img.default").css({
            opacity: 1
         });
      }, 1000);
   });


   $(document).ready(function () {


      /* about page slider */
      //Prevent Page Reload on all # links
      $("a[href='#']").click(function (e) {
         e.preventDefault();
      });

      $('.work-listing li').hover(function () {
         $('body').addClass('hover');
      }, function () {
         $('body').removeClass('hover');
      })

      //Device
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
         $(".work-listing li a i").css({
            "display": "block"
         });
         $(".work-listing li").click(function () {
            var $this = $(this);
            if ($this.hasClass("hovered")) {
               $this.removeClass("hovered");
               $(".portfolio-hero-image img").css({
                  opacity: 0
               });
               $(".portfolio-hero-image .default").css({
                  opacity: 1
               });
            } else {
               $this.siblings("li").removeClass("hovered");
               $(".work-listing li").removeAttr("style");
               $this.addClass("hovered");
               $(".portfolio-hero-image img").each(function () {
                  var $coverImage = $(this);
                  if ($this.attr("data-list") == $coverImage.attr("data-content")) {
                     $coverImage.css({
                        opacity: 1
                     });
                  } else {
                     $coverImage.css({
                        opacity: 0
                     });
                  }
               });
            }
         });
         $('.work-listing li a').click(function (e) {
            if (!$(this).parent().hasClass('hovered')) {
               e.preventDefault();
            } else {
               if ($(this).parents("li").hasClass("pink-theme")) {
                  $(this).parents("li").css("background", "#07a9fd ")
               } else if ($(this).parents("li").hasClass("blue-theme")) {

                  $(this).parents("li").css("background", "#3e37b1 ")
               } else {
                  $(this).parents("li").css("background", "#f5007a")
               }
               return true;
            }
         });
      };



      if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

         $(".work-listing li").hover(function () {
            var $this = $(this);
            if ($this.hasClass("hovered")) {
               $this.removeClass("hovered");
               $(".portfolio-hero-image img").css({
                  opacity: 0
               });
               $(".portfolio-hero-image .default").css({
                  opacity: 1
               });
            } else {
               $this.addClass("hovered");
               $(".portfolio-hero-image img").each(function () {
                  var $coverImage = $(this);
                  if ($this.attr("data-list") == $coverImage.attr("data-content")) {
                     $coverImage.css({
                        opacity: 1
                     });
                  } else {
                     $coverImage.css({
                        opacity: 0
                     });
                  }
               });
            }
         });
      }
      /* about page slider */

      /* about page video */
      $('.aboutus-banner .about-right .video .playbutton,.aboutus-banner .about-right .video img').click(function () {
         var video = '<div class="video-container"><iframe src="' + $('.aboutus-banner .about-right .video img').attr('data-video') + '"></iframe></div>';
         $('.aboutus-banner .about-right .video').hide();
         $('.aboutus-banner .about-right .tube').html(video);
         //$('.aboutus-banner .about-right .close').show();
      });
      $('.aboutus-banner .about-right .close').click(function () {
         $('.aboutus-banner .about-right .video').show();
         $('.aboutus-banner .about-right .tube').empty();
         //$('.aboutus-banner .about-right .close').hide();
      });


   });

})
