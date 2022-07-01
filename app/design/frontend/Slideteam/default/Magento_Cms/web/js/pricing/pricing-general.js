require(['jquery', 'tipso', 'smk_accordion'], function ($) {

    $(document).ready(function () {

        // Faq Accordion
        $(".faq-accordion").smk_Accordion({
            closeAble: true, //boolean
        });
    });
    $(window).load(function () {
        $(".tip").tipso({position: 'left',size: 'large'});
    });
});


