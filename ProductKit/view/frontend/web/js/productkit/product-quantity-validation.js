/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'catalogAddToCart'


], function ($, alert) {
    'use strict';

    $.widget('mage.productValidate', {
        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested',
            addToCartButtonSelector: '.action.tocart'
        },

        /**
         * Uses Magento's validation widget for the form object.
         * @private
         */
        _create: function () {
            let self = this;
            var bindSubmit = this.options.bindSubmit;

            this.element.validation({
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                /**
                 * Uses catalogAddToCart widget as submit handler.
                 * @param {Object} form
                 * @returns {Boolean}
                 */
                submitHandler: function (form) {
                     let $productKit = $(".child-products-kit");
                     if($productKit.length) {
                        let childQtyArray = [];
                        $( ".child_qty" ).each(function( index ) {
                           childQtyArray[index] = parseInt($( this ).text());
                        });
                        let requestedQty = $("#qty").val();
                        let minQty = Math.min(...childQtyArray);
                        if(requestedQty >  minQty) {
                              alert({
                                 title: $.mage.__('Warning'),
                                 content: $.mage.__('Parent Product quantity should not be more than child product quantity'),
                                 actions: {
                                    always: function(){}
                                 }
                              });

                           return false;
                        } 
                     }
                    var jqForm = $(form).catalogAddToCart({
                        bindSubmit: bindSubmit
                    });

                    jqForm.catalogAddToCart('submitForm', jqForm);

                    return false;
                }
            });
            $(this.options.addToCartButtonSelector).attr('disabled', false);
        }
        
    });

    return $.mage.productValidate;
});