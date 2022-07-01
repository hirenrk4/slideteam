require(['jquery'], function ($) {
    
    $(document).ready(changeLayout);
    $(window).on('resize',changeLayout);
    
    function changeLayout(){
        var isActive = false;
        if( (/iPhone|iPod|iPad|BlackBerry|BB10|Pre|Palm|Googlebot-Mobile|mobi|Safari Mobile|Windows Mobile|Android|Opera Mini|mobile/i.test(navigator.
            userAgent.toLowerCase())) && $(window).width() < 1024 ) {
            isActive = true;
        }
        else {
            isActive = false;
        }
        if (isActive == true) {
            $('.block-content-blog-subscribe').hide();
             $('.block-content-blog-search').hide();
             $('.block-content-blog-categories').hide();
            $('.block-blog-subscribe div.block-title').on('click', function () {
                $('.block-content-blog-subscribe').toggle();
            })
             $('.block-blog-search div.block-title').on('click', function () {
                $('.block-content-blog-search').toggle();
            })
              $('.block-blog-categories div.block-title').on('click', function () {
                $('.block-content-blog-categories').toggle();
            })
        } else {
            $('.block-content-blog-subscribe').show();
             $('.block-content-blog-search').show();
             $('.block-content-blog-categories').show();
        }
    }
    
})