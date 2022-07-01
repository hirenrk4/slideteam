require(['jquery'], function ($) {
    $(document).ready(function () {
        $('.thumbnail-port:gt(9)').hide().last().after(
            $('.custom-button-block').attr('href', '#').click(function () {
                var a = this;
                $('.thumbnail-port:not(:visible):lt(5)').fadeIn(function () {
                    if ($('.thumbnail-port:not(:visible)').length == 0)
                        $(a).remove();
                });
                return false;
            })
        );
    });
});