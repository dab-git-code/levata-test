<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\CouponManagement;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Quote\Api\CouponManagementInterface;

class PreventChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param CouponManagementInterface $subject
     * @param int $cartId
     * @return void
     */
    public function beforeSet(CouponManagementInterface $subject, $cartId): void
    {
        $this->guard->assertMutable((int) $cartId, 'apply_coupon');
    }

    /**
     * @param CouponManagementInterface $subject
     * @param int $cartId
     * @return void
     */
    public function beforeRemove(CouponManagementInterface $subject, $cartId): void
    {
        $this->guard->assertMutable((int) $cartId, 'remove_coupon');
    }
}
