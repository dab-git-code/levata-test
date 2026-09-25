<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Service;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Keeps locked quote addresses/shipping method when Magento checkout tries to overwrite them.
 */
class LockedCheckoutSnapshot
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ShippingInformationInterfaceFactory $shippingInformationFactory,
    ) {
    }

    public function isLockedCart(int $cartId): bool
    {
        return $this->guard->isImmutable($cartId);
    }

    /**
     * Rebuild shipping information from the locked quote so checkout can advance without mutations.
     */
    public function shippingInformationFromQuote(int $cartId): ShippingInformationInterface
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $info = $this->shippingInformationFactory->create();
        $info->setShippingAddress($quote->getShippingAddress());
        $info->setBillingAddress($quote->getBillingAddress());

        $method = (string) $quote->getShippingAddress()->getShippingMethod();
        if ($method !== '' && str_contains($method, '_')) {
            [$carrier, $methodCode] = explode('_', $method, 2);
            $info->setShippingCarrierCode($carrier);
            $info->setShippingMethodCode($methodCode);
        }

        return $info;
    }

    public function getQuote(int $cartId): CartInterface
    {
        return $this->cartRepository->get($cartId);
    }
}
