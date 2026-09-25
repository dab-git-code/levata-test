<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api\Guard;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;

interface QuoteImmutabilityGuardInterface
{
    public function isImmutable(int $quoteId): bool;

    public function isImmutableCart(CartInterface $cart): bool;

    /**
     * @throws LocalizedException
     */
    public function assertMutable(int $quoteId, string $action = 'modify'): void;

    /**
     * @throws LocalizedException
     */
    public function assertMutableCart(CartInterface $cart, string $action = 'modify'): void;
}
