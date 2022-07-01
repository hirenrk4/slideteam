/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            return this.tatva_customer_data = customerData.get('tatva_customer_data');          
        }
    });
});
