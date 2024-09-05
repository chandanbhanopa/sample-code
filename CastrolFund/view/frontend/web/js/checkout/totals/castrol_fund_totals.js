/**
 * Helm_CastrolFund
 *
 * @copyright   Copyright (c) 2021 Alpine Consulting, Inc
 */
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/customer-data',
        'Helm_CastrolFund/js/model/castrol-helper'
    ],
    function ($, Component, totals, priceUtils, customerData, helper) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Helm_CastrolFund/checkout/totals/coop_fund_totals'
            },

            isCastrolFundEnabled: function () {
                return true;
                /*
                return window.checkoutConfig.coopFundEnabled &&
                    window.checkoutConfig.coopFundAllowed
                    */
            },

            isCastrolFund: function () {
                var price = customerData
                console.log("Castrol Funds Price ", price);
                return !!price;

            },

            getCastrolFund: function () {
                if(totals.getSegment('castrol_fund_totals')){
                    var castrolFundPrice = totals.getSegment('castrol_fund_totals').value;
                    return priceUtils.formatPrice(castrolFundPrice);
                } else {
                    return priceUtils.formatPrice(0);
                }
                
            },

            getTitle: function () {
                return checkoutConfig.castrolFundLabel;
            },
            isUsingCastrolFunds: function() {
                //console.log("Castrol Funds : ",helper.isCustomerUsingCastrolFunds());
                return helper.isCustomerUsingCastrolFunds();
            }
        });
    }
);
