/**
 * Alpine_CoopFund
 *
 * @copyright   Copyright (c) 2022 Alpine Consulting, Inc
 */
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'ko',
        'Magento_Catalog/js/price-utils',
        'mage/url',
        'Helm_CastrolFund/js/model/castrol-helper'
    ],
    function ($, Component, quote, ko, priceUtils, url, helper) {
        'use strict'
        return Component.extend({
            defaults: {
                template: 'Helm_CastrolFund/checkout/castrol_fund_message'
            },

            getMessage: function () {
                return "You have applied the castrol funds!";
            },

            isEnabled: function () {
                return true;
            },
            isUsingCastrolFunds: function() {
                return helper.isCustomerUsingCastrolFunds()
            }
        });
    }
);
