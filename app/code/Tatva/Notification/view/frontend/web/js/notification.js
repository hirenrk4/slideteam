require(['jquery'],function ($) {
    $(document).ready(function(){
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
    });
});