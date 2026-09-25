<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Checkout\PaymentInformationManagement;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Model\Guard\PaymentMethodGuard;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Locked quotes: keep negotiated payment method and discard billing address overwrites.
 */
class PreventChanges
{
    public function __construct(
        private readonly PaymentMethodGuard $paymentMethodGuard,
        private readonly QuoteImmutabilityGuardInterface $immutabilityGuard,
    ) {
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array{0: int|string, 1: PaymentInterface, 2: AddressInterface|null}
     */
    public function beforeSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->paymentMethodGuard->assertPlaceOrderPayment((int) $cartId, $paymentMethod);
        if ($this->immutabilityGuard->isImmutable((int) $cartId)) {
            $billingAddress = null;
        }
        return [$cartId, $paymentMethod, $billingAddress];
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array{0: int|string, 1: PaymentInterface, 2: AddressInterface|null}
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        ?AddressInterface $billingAddress = null
    ): array {
        $this->paymentMethodGuard->assertPlaceOrderPayment((int) $cartId, $paymentMethod);
        if ($this->immutabilityGuard->isImmutable((int) $cartId)) {
            $billingAddress = null;
        }
        return [$cartId, $paymentMethod, $billingAddress];
    }
}
