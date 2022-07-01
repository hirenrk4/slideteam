require(['jquery', 'slick_min'], function ($) 
{
    $(document).ready(function () 
    {

        var gallery = $('.design-services-inner-page');
        var thumb = jQuery('.small-slick');
        var slide_count_wrapper = $('.slide-count-wrap');
        /*var slide_count = 40;*/
        var slide_count = $('.item').length;

        gallery.on('init reInit afterChange', function (event, slick, currentSlide, nextSlide) {
            //currentSlide is undefined on init -- set it to 0 in this case (currentSlide is 0 based)
            var i = (currentSlide ? currentSlide : 0) + 1;
            slide_count_wrapper.text('Slide ' + i + ' of ' + slide_count);
        });

        gallery.slick({
            cssEase: 'linear',
            swipe: true,
            swipeToSlide: true,
            touchThreshold: 10,
            slidesToShow: 1,
            slidesToScroll: 1,
            //lazyLoadBuffer:3,
            //lazyLoad: 'progre ssive',
        });

        thumb.slick(
        {
            slidesToShow: 5,
            slidesToScroll: 1,
            dots: false,
            responsive: [
            {
                breakpoint: 1023,
                settings: {
                    arrows: true,
                    slidesToShow: 5
                }
            },
            {
                breakpoint: 600,
                settings: {
                    arrows: true,
                    slidesToShow: 3
                }
            },
            {
                breakpoint: 414,
                settings: {
                    arrows: true,
                    slidesToShow: 2
                }
            }],
            centerMode: true,
            focusOnSelect: true,
            arrows: true,
            asNavFor: '.design-services-inner-page'
        });

    });
});

