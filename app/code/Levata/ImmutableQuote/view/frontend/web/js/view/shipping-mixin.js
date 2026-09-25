define([
    'jquery',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data'
], function (
    $,
    addressList,
    createShippingAddress,
    selectShippingAddress,
    selectShippingMethod,
    checkoutData
) {
    'use strict';

    var isImmutable = !!(window.checkoutConfig && window.checkoutConfig.levataIsImmutable);

    return function (Component) {
        return Component.extend({
            defaults: {
                levataIsImmutable: isImmutable
            },

            /**
             * @inheritdoc
             */
            initialize: function () {
                this._super();

                if (this.levataIsImmutable) {
                    this.applyLockedQuoteAddress();
                    $('body').addClass('levata-immutable-quote');
                    this.isFormPopUpVisible(false);
                    this.isNewAddressAdded(false);
                    this.isFormInline = addressList().length === 0;
                }

                return this;
            },

            /**
             * Force negotiated quote address into the shipping step UI.
             */
            applyLockedQuoteAddress: function () {
                var shippingData = window.checkoutConfig.shippingAddressFromData,
                    method = window.checkoutConfig.selectedShippingMethod,
                    address;

                if (!shippingData) {
                    return;
                }

                checkoutData.setShippingAddressFromData(shippingData);
                addressList.removeAll();
                address = createShippingAddress(shippingData);
                selectShippingAddress(address);
                this.isNewAddressAdded(true);

                if (method && method.carrier_code && method.method_code) {
                    selectShippingMethod(method);
                }
            },

            /**
             * Locked quotes cannot open the new-address popup.
             */
            showFormPopUp: function () {
                if (this.levataIsImmutable) {
                    return;
                }
                this._super();
            },

            /**
             * Locked quotes keep the negotiated shipping method.
             */
            selectShippingMethod: function (shippingMethod) {
                if (this.levataIsImmutable) {
                    return true;
                }
                return this._super(shippingMethod);
            },

            /**
             * @inheritdoc
             */
            validateShippingInformation: function () {
                if (this.levataIsImmutable) {
                    return true;
                }
                return this._super();
            }
        });
    };
});
