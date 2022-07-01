define(['jquery'], function ($) {
    var testShowHide = {
        showHideAttr: function () {
            var action = $('[name="product[pricing_type]"]').val();
            if (action!='') {
                switch (action) {
                    case '0':
                        this.hideFields('div[data-index="pricing_product_type"]');
                        break;
                    case '1':
                        this.showFields('div[data-index="pricing_product_type"]');
                        break;
                    case '2':
                        this.showFields('div[data-index="pricing_product_type"]');
                        break;
                }
            } else {
                this.hideFields('div[data-index="pricing_product_type"]');
            }
        },

        hideFields: function (names) {
            $(names).toggle(false);
        },

        showFields: function (names) {
            $(names).toggle(true);
        }
    };
    return testShowHide;
});