<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\ShippingAddressManagement;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Quote\Api\ShippingAddressManagementInterface;

class PreventChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param ShippingAddressManagementInterface $subject
     * @param int $cartId
     * @return void
     */
    public function beforeAssign(ShippingAddressManagementInterface $subject, $cartId): void
    {
        $this->guard->assertMutable((int) $cartId, 'change_shipping_address');
    }
}
