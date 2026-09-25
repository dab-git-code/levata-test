<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Applies negotiated line prices after Magento adds the catalog-priced item.
 */
class CustomPriceApplier
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly Json $json,
    ) {
    }

    /**
     * Read negotiated price from the API request payload (not from Magento's save() result).
     */
    public function extractCustomPrice(CartItemInterface $cartItem): ?float
    {
        $extension = $cartItem->getExtensionAttributes();
        if ($extension === null) {
            return null;
        }

        $value = null;
        if (method_exists($extension, 'getLevataCustomPrice')) {
            $value = $extension->getLevataCustomPrice();
        } elseif (method_exists($extension, 'getData')) {
            $value = $extension->getData('levata_custom_price');
        }

        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 4);
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyToQuoteItemId(int $quoteId, int $itemId, float $customPrice): CartItemInterface
    {
        if ($customPrice < 0) {
            throw new LocalizedException(__('Custom price cannot be negative.'));
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($quoteId);
        /** @var QuoteItem|false $quoteItem */
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new LocalizedException(__('Unable to apply custom price: cart item not found.'));
        }

        $this->applyToQuoteItem($quoteItem, $customPrice);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->cartRepository->save($quote);

        /** @var Quote $reloaded */
        $reloaded = $this->cartRepository->get($quoteId);
        $item = $reloaded->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__('Unable to reload cart item after custom price apply.'));
        }

        return $item;
    }

    public function applyToQuoteItem(QuoteItem $item, float $customPrice): void
    {
        $product = $item->getProduct();
        if ($product) {
            $product->setIsSuperMode(true);
        }

        $infoBuyRequest = $item->getBuyRequest();
        if ($infoBuyRequest) {
            $infoBuyRequest->setCustomPrice($customPrice);
            $item->addOption([
                'product_id' => $item->getProductId(),
                'code' => 'info_buyRequest',
                'value' => $this->json->serialize($infoBuyRequest->getData()),
            ]);
        }

        $item->setCustomPrice($customPrice);
        $item->setOriginalCustomPrice($customPrice);
        $item->setPrice($customPrice);
        $item->setBasePrice($customPrice);
        $item->setRowTotal($customPrice * (float) $item->getQty());
        $item->setBaseRowTotal($customPrice * (float) $item->getQty());
        // Force recalculation path to use custom price
        $item->unsetData('calculation_price');
        $item->unsetData('base_calculation_price');
    }
}
