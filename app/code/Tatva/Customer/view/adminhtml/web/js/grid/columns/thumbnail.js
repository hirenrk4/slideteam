/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/template',
    'Ddimgtooltip',
    'text!Magento_Ui/templates/grid/cells/thumbnail/preview.html',
    'underscore',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Column, $, mageTemplate, ddtooltip,thumbnailPreviewTemplate, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Tatva_Customer/grid/cells/thumbnail',
            fieldClass: {
                'data-grid-thumbnail-cell': true
            }
        },

        
        /**
         * Get image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getSrc: function (row) {
            return row[this.index + '_src'];
        },

        /**
         * Get original image source data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getOrigSrc: function (row) {
            return row[this.index + '_orig_src'];
        },

        /**
         * Get link data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getLink: function (row) {
            return row[this.index + '_link'];
        },

        /**
         * Get alternative text data per row.
         *
         * @param {Object} row
         * @returns {String}
         */
        getAlt: function (row) {
            return _.escape(row[this.index + '_alt']);
        },

        getRelAttr: function (row) {
            return _.escape(row[this.index + '_rel']);
        },

        /**
         * Check if preview available.
         *
         * @returns {Boolean}
         */
        isPreviewAvailable: function () {
            return this['has_preview'] || false;
        },

        /**
         * Build preview.
         *
         * @param {Object} row
         */
        preview: function (row) {
            
            ddimgtooltip.init("*[rel^=imgtip]");
            
        },

        clearimg: function (row) {
            
            ddimgtooltip.rmimage("*[rel^=imgtip]");
            
        },

        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        },

        msenter: function (row) {
            
            if (this.isPreviewAvailable()) {
                return this.preview.bind(this, row);
            }
        },

        msleave: function (row) {
            
            if (this.isPreviewAvailable()) {
                return this.clearimg.bind(this, row);
            }
        }
    });
});
