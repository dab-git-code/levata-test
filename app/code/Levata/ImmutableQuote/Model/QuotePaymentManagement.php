<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Api\QuotePaymentManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

class QuotePaymentManagement implements QuotePaymentManagementInterface
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly PaymentMethodManagementInterface $paymentMethodManagement,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod(int $quoteId, PaymentInterface $paymentMethod): int
    {
        $this->guard->assertMutable($quoteId, 'set_payment_method');
        return (int) $this->paymentMethodManagement->set($quoteId, $paymentMethod);
    }
}
