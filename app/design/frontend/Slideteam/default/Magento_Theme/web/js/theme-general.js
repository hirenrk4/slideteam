require(['jquery','jQdotdot'], function ($) {

    $(document).ready(function () {

        $('.content-trim').dotdotdot({
            callback: function (isTruncated) {},
            ellipsis: "\u2026 ",
            height: 64,
            keep: null,
            tolerance: 0,
            truncate: "word",
            watch: "window",
        })
    });
});


