define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_SalesRule/js/model/coupon',
        'Magento_Customer/js/customer-data',
        'Helm_CastrolFund/js/model/castrol-helper'
    ],

    function (ko, $, Component, url, quote, totals, getTotals, getPaymentInformation, coupon, customerData, helper) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Helm_CastrolFund/checkout/castrol_fund'
            },

            initObservable: function () {
                this._super()
                .observe({
                    checked: false,
                    disabled: false
                });
                quote.totals.subscribe(function (data) {
    
                    let customerInitialBalance = customerData.get('castrolfundsdata')().initial_balance;
                   
                    if(data.grand_total > customerInitialBalance ){
                        helper.checkCastrolFund().disabled = true;
                        } else {
                         helper.checkCastrolFund().disabled = false;
                    }
                    this.disabled(helper.checkCastrolFund().disabled);
                }, this);

;                return this;
            },

            click: function () {
                var method = this.checked() ? this.payByCastrolFunds() : this.cancelCastrolFunds();
                return true;
            },

            payByCastrolFunds: function() {
                var linkUrl = url.build('rest/V1/castrolfunds/apply');
                $.ajax({
                    url: linkUrl,
                    type: "GET",
                    dataType: 'json',
                    contentType: "application/json"
                }).done(function () {
                    var deferred = $.Deferred();
                    getPaymentInformation(deferred);
                    getTotals([], deferred);
                });
                helper.isCustomerUsingCastrolFunds(true);
            },

            cancelCastrolFunds: function() {
                var linkUrl = url.build('rest/V1/castrolfunds/cancel');
                $.ajax({
                    url: linkUrl,
                    type: "GET",
                    dataType: 'json',
                    contentType: "application/json"
                }).done(function () {
                    var deferred = $.Deferred();
                    getPaymentInformation(deferred);
                    getTotals([], deferred);
                });

               helper.isCustomerUsingCastrolFunds(false);
            },
            isLogin : function() {
                let customer = customerData.get('customer')();
                return !$.isEmptyObject(customer);
            },
            getTitle:function(){
                let castrolFundsTitle = customerData.get('castrolfundsdata')().castrolFundsLabel;

                return castrolFundsTitle ? castrolFundsTitle : 'Castrol Funds';
            },
            isEnabled: function () {
                return customerData.get('castrolfundsdata')().castrolFundsEnable;
            },
            isAllowedForGroup:function(){
                return customerData.get('castrolfundsdata')().castrolFundsAllowed;
            }
            
        });
    }
);
