var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Levata_ImmutableQuote/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Levata_ImmutableQuote/js/view/billing-address-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Levata_ImmutableQuote/js/model/checkout-data-resolver-mixin': true
            }
        }
    }
};
