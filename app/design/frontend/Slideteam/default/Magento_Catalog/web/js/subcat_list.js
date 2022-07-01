require(['jquery'], function ($) {
    $(document).ready(function () {

        //$(".cat-col1 li:gt(6),.cat-col2 li:gt(6),.cat-col3 li:gt(6),.cat-col4 li:gt(6),.show-less-link").css('display', 'none');

        /*$(".list li:gt(6):not(:last-child)").css('display','none');*/

        $(".show-more-link1").click(function () {
            $(".cat-col1 li:gt(6),.cat-col2 li:gt(6),.cat-col3 li:gt(6),.cat-col4 li:gt(6)").fadeIn(1000);
            $(".show-less-link").css('display', 'inline');
            $(this).hide();
        });
        $(".show-less-link").click(function () {
            $(".show-more-link1").css('display', 'inline');
            $(".cat-col1 li:gt(6),.cat-col2 li:gt(6),.cat-col3 li:gt(6),.cat-col4 li:gt(6),.show-less-link").fadeOut(100);
            $(this).hide();
        });
    });
});