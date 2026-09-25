<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;

interface QuoteItemManagementInterface
{
    /**
     * Add or update cart line (only while quote is mutable).
     * Optional extension attribute levata_custom_price applies a negotiated unit price.
     *
     * @param int $quoteId
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     * @throws LocalizedException
     */
    public function saveItem(int $quoteId, CartItemInterface $cartItem): CartItemInterface;
}
