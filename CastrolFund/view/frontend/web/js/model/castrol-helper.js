define([
    'ko',
], function (ko ) {
    'use strict';
    var castrolModel = {
        isCustomerUsingCastrolFunds : ko.observable(false),
        checkCastrolFund:ko.observable({
            checked: false,
            disabled: false
        })
    }
    return castrolModel;
});