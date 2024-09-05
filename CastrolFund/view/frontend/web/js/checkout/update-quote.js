define([
    'uiComponent',
    'jquery'
], function (Component, $) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            // Your custom function to run when the checkout page is opened
            this.runOnCheckoutPageLoad();
        },

        runOnCheckoutPageLoad: function () {
            // Add your custom logic here
            console.log('Checkout page loaded');
            // var linkUrl = url.build('rest/V1/castrolfunds/apply');
            //     $.ajax({
            //         showLoader: true,
            //         url: linkUrl,
            //         type: "GET",
            //         dataType: 'json',
            //         contentType: "application/json"
            //     }).done(function () {
            //         var deferred = $.Deferred();
            //         getPaymentInformation(deferred);
            //         getTotals([], deferred);
            //     });

            // Example: Check if customer has enough initial balance
            // Perform an AJAX request to check the initial balance or any other custom logic
        }
    });
});