/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/core',
	'jquery-ui-modules/widget',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'rating-required', function (value) {
            return value !== undefined;
        }, $.mage.__('Please click stars to select ratings.'));
});
