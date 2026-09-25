<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Api\QuoteItemManagementInterface;
use Levata\ImmutableQuote\Model\Service\CustomPriceApplier;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Adds items to mutable quotes, including inactive ones created for multi-quote flows.
 *
 * Magento\Quote\Plugin\Webapi\ValidateProductWebsiteAssignment calls CartRepository::getActive(),
 * which fails on inactive quotes and surfaces a misleading "product not available" error.
 */
class QuoteItemManagement implements QuoteItemManagementInterface
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CustomPriceApplier $customPriceApplier,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function saveItem(int $quoteId, CartItemInterface $cartItem): CartItemInterface
    {
        $this->guard->assertMutable($quoteId, 'add_or_update_item');
        $cartItem->setQuoteId($quoteId);

        // Must read from the request payload: Magento's save() result drops extension attributes.
        $customPrice = $this->customPriceApplier->extractCustomPrice($cartItem);

        $quote = $this->cartRepository->get($quoteId);
        $wasActive = (bool) $quote->getIsActive();

        if (!$wasActive) {
            $quote->setIsActive(true);
            $this->cartRepository->save($quote);
        }

        try {
            $saved = $this->cartItemRepository->save($cartItem);
            if ($customPrice === null) {
                return $saved;
            }

            return $this->customPriceApplier->applyToQuoteItemId(
                $quoteId,
                (int) $saved->getItemId(),
                $customPrice
            );
        } finally {
            if (!$wasActive) {
                $quote = $this->cartRepository->get($quoteId);
                $quote->setIsActive(false);
                // Re-apply super mode on items with custom price so collectTotals on save keeps them.
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getCustomPrice() !== null && $item->getProduct()) {
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
                $this->cartRepository->save($quote);
            }
        }
    }
}
