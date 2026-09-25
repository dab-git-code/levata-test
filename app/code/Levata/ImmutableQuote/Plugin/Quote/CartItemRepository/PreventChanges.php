<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\CartItemRepository;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class PreventChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param CartItemRepositoryInterface $subject
     * @param CartItemInterface $cartItem
     * @return void
     */
    public function beforeSave(CartItemRepositoryInterface $subject, CartItemInterface $cartItem): void
    {
        $quoteId = (int) $cartItem->getQuoteId();
        if ($quoteId) {
            $this->guard->assertMutable($quoteId, 'save_cart_item');
        }
    }

    /**
     * @param CartItemRepositoryInterface $subject
     * @param int $cartId
     * @param int|string $itemId
     * @return void
     */
    public function beforeDeleteById(CartItemRepositoryInterface $subject, $cartId, $itemId): void
    {
        $this->guard->assertMutable((int) $cartId, 'delete_cart_item');
    }
}
