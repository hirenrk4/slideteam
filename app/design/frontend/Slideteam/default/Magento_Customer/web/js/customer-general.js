require(['jquery'], function ($) {
    
    //Task - 147 : My Account | My account and Delete account tabs are expandable
    // $(document).ready(changeLayout);
    // $(window).on('resize',changeLayout);
    
    function changeLayout(){
        var isActive = false;
        if ($(window).width() < 1024) {
            isActive = true;
        } else {
            isActive = false;
        }
        if (isActive == true) {
            $('.account-nav-content').hide();
            $('.account-nav-title').on('click', function () {
                $(this).nextAll('.account-nav-content:first').toggle();
            })
        } else {
            $('.account-nav-content').show();
        }
    }
    
});