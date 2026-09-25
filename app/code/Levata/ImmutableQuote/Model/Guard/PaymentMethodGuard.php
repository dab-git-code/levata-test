<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Guard;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Locked quotes keep a fixed payment method (must be set before lock).
 */
class PaymentMethodGuard
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $immutabilityGuard,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    /**
     * Allow only if mutable, or if re-applying the same locked payment method (place-order path).
     *
     * @throws LocalizedException
     */
    public function assertCanAssign(int $cartId, ?string $requestedMethod = null): void
    {
        if (!$this->immutabilityGuard->isImmutable($cartId)) {
            return;
        }

        $currentMethod = $this->getCurrentMethod($cartId);
        if ($currentMethod !== null
            && $currentMethod !== ''
            && $requestedMethod !== null
            && $requestedMethod !== ''
            && $requestedMethod === $currentMethod
        ) {
            return;
        }

        if ($currentMethod === null || $currentMethod === '') {
            throw new LocalizedException(
                __(
                    'This locked quote has no payment method. '
                    . 'A sales representative must set payment before locking, or unlock the quote.'
                )
            );
        }

        $this->immutabilityGuard->assertMutable($cartId, 'change_payment_method');
    }

    /**
     * @throws LocalizedException
     */
    public function assertPlaceOrderPayment(int $cartId, PaymentInterface $paymentMethod): void
    {
        if (!$this->immutabilityGuard->isImmutable($cartId)) {
            return;
        }

        $currentMethod = $this->getCurrentMethod($cartId);
        if ($currentMethod === null || $currentMethod === '') {
            throw new LocalizedException(
                __(
                    'This locked quote has no payment method. '
                    . 'A sales representative must set payment before locking, or unlock the quote.'
                )
            );
        }

        $requested = (string) $paymentMethod->getMethod();
        if ($requested !== '' && $requested !== $currentMethod) {
            $this->immutabilityGuard->assertMutable($cartId, 'change_payment_method');
        }

        $paymentMethod->setMethod($currentMethod);
    }

    private function getCurrentMethod(int $cartId): ?string
    {
        $quote = $this->cartRepository->get($cartId);
        $payment = $quote->getPayment();
        if (!$payment || !$payment->getMethod()) {
            return null;
        }
        return (string) $payment->getMethod();
    }
}
