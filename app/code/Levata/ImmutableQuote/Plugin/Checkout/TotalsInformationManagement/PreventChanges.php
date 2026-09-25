<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Checkout\TotalsInformationManagement;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

/**
 * Locked quotes: do not recalculate totals from a different address payload.
 */
class PreventChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly CartTotalRepositoryInterface $cartTotalRepository,
    ) {
    }

    /**
     * @param TotalsInformationManagementInterface $subject
     * @param callable $proceed
     * @param int $cartId
     * @param TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function aroundCalculate(
        TotalsInformationManagementInterface $subject,
        callable $proceed,
        $cartId,
        TotalsInformationInterface $addressInformation
    ) {
        if (!$this->guard->isImmutable((int) $cartId)) {
            return $proceed($cartId, $addressInformation);
        }

        return $this->cartTotalRepository->get((int) $cartId);
    }
}
