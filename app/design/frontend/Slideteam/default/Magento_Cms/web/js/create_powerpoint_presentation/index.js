require(['jquery', 'magnific_popup'], function ($) {
    $(".popup-img").magnificPopup({
        type: 'image',
        delegate: 'a',

        gallery: {enabled: true},
        callbacks: {

            buildControls: function () {
                // re-appends controls inside the main container
                this.contentContainer.append(this.arrowLeft.add(this.arrowRight));
            }

        }
    })
})