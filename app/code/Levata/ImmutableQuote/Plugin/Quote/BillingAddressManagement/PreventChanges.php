<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\BillingAddressManagement;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;

class PreventChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param BillingAddressManagementInterface $subject
     * @param int $cartId
     * @return void
     */
    public function beforeAssign(BillingAddressManagementInterface $subject, $cartId): void
    {
        $this->guard->assertMutable((int) $cartId, 'change_billing');
    }
}
