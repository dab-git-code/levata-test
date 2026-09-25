define([
    'jquery',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data'
], function (
    $,
    addressList,
    quote,
    createShippingAddress,
    selectShippingAddress,
    createBillingAddress,
    selectBillingAddress,
    selectShippingMethod,
    checkoutData
) {
    'use strict';

    var isImmutable = !!(window.checkoutConfig && window.checkoutConfig.levataIsImmutable);

    /**
     * Magento prefers the customer address book over quote addresses without
     * customer_address_id. Locked quotes must force the negotiated quote address.
     */
    function applyLockedAddresses() {
        var shippingData = window.checkoutConfig.shippingAddressFromData,
            billingData = window.checkoutConfig.billingAddressFromData || shippingData,
            shippingAddress,
            billingAddress,
            method = window.checkoutConfig.selectedShippingMethod;

        if (!shippingData) {
            return false;
        }

        checkoutData.setShippingAddressFromData(shippingData);
        addressList.removeAll();
        shippingAddress = createShippingAddress(shippingData);
        selectShippingAddress(shippingAddress);

        if (billingData) {
            checkoutData.setBillingAddressFromData(billingData);
            billingAddress = createBillingAddress(billingData);
            selectBillingAddress(billingAddress);
        }

        if (method && method.carrier_code && method.method_code) {
            selectShippingMethod(method);
            checkoutData.setSelectedShippingRate(
                method.carrier_code + '_' + method.method_code
            );
        }

        $('body').addClass('levata-immutable-quote');

        return true;
    }

    return function (resolver) {
        if (!isImmutable) {
            return resolver;
        }

        var originalResolveBilling = resolver.resolveBillingAddress.bind(resolver);

        resolver.resolveShippingAddress = function () {
            if (!applyLockedAddresses()) {
                return;
            }
        };

        resolver.applyShippingAddress = function () {
            applyLockedAddresses();
        };

        resolver.resolveBillingAddress = function () {
            var billingData = window.checkoutConfig.billingAddressFromData
                || window.checkoutConfig.shippingAddressFromData;

            if (billingData) {
                selectBillingAddress(createBillingAddress(billingData));

                return;
            }

            originalResolveBilling();
        };

        resolver.getShippingAddressFromCustomerAddressList = function () {
            return quote.shippingAddress();
        };

        return resolver;
    };
});
