define([
    'jquery'
], function ($) {
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
                    $('body').addClass('levata-immutable-quote');
                    this.isAddressDetailsVisible(true);
                    this.isAddressFormVisible(false);
                }

                return this;
            },

            /**
             * Prevent editing billing address on locked quotes.
             */
            editAddress: function () {
                if (this.levataIsImmutable) {
                    return;
                }
                this._super();
            },

            /**
             * Prevent cancelling into edit mode for locked quotes.
             */
            cancelAddressEdit: function () {
                if (this.levataIsImmutable) {
                    this.isAddressDetailsVisible(true);
                    return;
                }
                this._super();
            }
        });
    };
});
