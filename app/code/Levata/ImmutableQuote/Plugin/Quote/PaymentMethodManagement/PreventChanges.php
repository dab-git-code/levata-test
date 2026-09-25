<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\PaymentMethodManagement;

use Levata\ImmutableQuote\Model\Guard\PaymentMethodGuard;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class PreventChanges
{
    public function __construct(
        private readonly PaymentMethodGuard $paymentMethodGuard,
    ) {
    }

    /**
     * @param PaymentMethodManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $method
     * @return void
     */
    public function beforeSet(
        PaymentMethodManagementInterface $subject,
        $cartId,
        PaymentInterface $method
    ): void {
        $this->paymentMethodGuard->assertCanAssign((int) $cartId, (string) $method->getMethod());
    }
}
