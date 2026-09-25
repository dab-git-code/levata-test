<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api;

/**
 * Sets payment on a mutable quote (must be done before lock for checkout to succeed).
 */
interface QuotePaymentManagementInterface
{
    /**
     * @param int $quoteId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPaymentMethod(int $quoteId, \Magento\Quote\Api\Data\PaymentInterface $paymentMethod): int;
}
