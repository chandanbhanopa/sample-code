define([
    'uiComponent',
    'jquery',
    'ko',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Helm_CastrolFund/js/model/castrol-helper'
], function (Component, $, ko, customerData, url, helper) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Helm_CastrolFund/customer/castrol-funds',
            initialBalance:ko.observable(0),
            fundPageTitle : ko.observable(''),
            track: {
                fundPageTitle :true,
                initialBalance : true
            },
        },

        initialize: function (config) {
            let self = this;
            this.updateInitialFundsInQuote();
            this.getFunds();    
            
            //this.headerTitle(config.hederTitle);
            this._super();
        },
        updateInitialFundsInQuote: function() {
            if(this.isLogin()){
                var linkUrl = url.build('rest/V1/castrolfunds/update-initial-balance');
                $.ajax({
                    showLoader: true,
                    url: linkUrl,
                    type: "GET",
                    dataType: 'json',
                    contentType: "application/json"
                }).done(function () {
                    console.log("Request Done");
                });
            }
        },

        /**
         * Get Castrol Initial Funds
         *
         * @returns {*[]}
         */
        getFunds: function () {
                let self = this;
                if(this.isLogin()){
                    var linkUrl = url.build('rest/V1/castrolfunds/get-balance');
                    $.ajax({
                        showLoader: true,
                        url: linkUrl,
                        type: "GET",
                        dataType: 'json',
                        contentType: "application/json"
                    }).done(function (response) {
                        
                        if(response){
                            self.initialBalance(response);
                            helper.checkCastrolFund({
                                disabled:false,
                                checked:false
                            });
                            
                        } else {
                            console.log("Else condition: "+response);
                            helper.checkCastrolFund({
                                disabled:true,
                                checked:false
                            });
                           
                            if($("#castrol_fund").length){
                                if(response.trim().length < 1) {
                                    document.getElementById("castrol_fund").disabled = true;
                                }
                                
                            }
                            
                        }
                        
                    });
                }

           
            
        },
        isLogin : function() {
            let customer = customerData.get('customer')();
            return !$.isEmptyObject(customer);
        },
        getCastrolFundLabel:function(){
            return customerData.get('castrolfundsdata')().castrolFundsLabel;
        },
        isEnabled: function () {
            return customerData.get('castrolfundsdata')().castrolFundsEnable;
        },
        isAllowedForGroup:function(){
            return customerData.get('castrolfundsdata')().castrolFundsAllowed;
        },
        getHeaderTitle: function(){
            return customerData.get('castrolfundsdata')().castrolFundsHeaderTitle;
        }
        
    });
});