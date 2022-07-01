define(['jquery', 'uiComponent', 'ko'], function ($, Component, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Tatva_Checkout/summary'
            },
            initialize: function () {
                $(function() {
                    $('body').on("click", '#place-order-trigger', function () {
                        $(".payment-method._active").find('.action.primary.checkout').trigger( 'click' );
                    });
                });
                var self = this;
                this._super();
            }
        });
    }
);