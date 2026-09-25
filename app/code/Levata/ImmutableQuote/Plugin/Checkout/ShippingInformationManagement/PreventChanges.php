<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Checkout\ShippingInformationManagement;

use Levata\ImmutableQuote\Model\Service\LockedCheckoutSnapshot;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

/**
 * Locked quotes: ignore inbound address/method changes but still allow checkout to continue.
 */
class PreventChanges
{
    public function __construct(
        private readonly LockedCheckoutSnapshot $lockedCheckoutSnapshot,
    ) {
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param callable $proceed
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetailsInterface
     */
    public function aroundSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        callable $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $cartId = (int) $cartId;
        if (!$this->lockedCheckoutSnapshot->isLockedCart($cartId)) {
            return $proceed($cartId, $addressInformation);
        }

        return $proceed($cartId, $this->lockedCheckoutSnapshot->shippingInformationFromQuote($cartId));
    }
}
