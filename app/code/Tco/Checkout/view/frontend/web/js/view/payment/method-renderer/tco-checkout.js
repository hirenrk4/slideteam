define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-billing-address',
        'Tco_Checkout/js/action/set-payment-method',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function ($, Component, setBillingAddressAction, setPaymentMethodAction, selectPaymentMethodAction, additionalValidators,
              fullScreenLoader,getPaymentInformationAction) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Tco_Checkout/payment/tco'
            },

            continueTo2Checkout: function () {
                if (this.validate() && additionalValidators.validate()) {
                    this.selectPaymentMethod();
                    // To skip the billing address in checkout
                    // var setBillingInfo = setBillingAddressAction();
                    // setBillingInfo.done(function() {
                    //     setPaymentMethodAction();
                    // });
                    fullScreenLoader.startLoader();
                    var deferred = $.Deferred();

                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                    });
                    setPaymentMethodAction();
                    return false;
                }
            }
        });
    }
);