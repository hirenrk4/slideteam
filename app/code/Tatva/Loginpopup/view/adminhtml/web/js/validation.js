define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget'
], function ($, alert) {
    'use strict';
    $.widget('mage.surveyformrequire', {
        options: {
            confirmMsg: ('divElement is removed.')
        },
        _create: function () {
            var self = this;
            $(self.options.divElement).remove();
            alert({
                content: self.options.confirmMsg
            });
        }
    });
    return $.mage.surveyformrequire;
});