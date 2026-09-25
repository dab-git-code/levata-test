<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api\Data;

/**
 * Payload for creating or cloning a customer quote via API.
 * Optional items are applied while the quote is still mutable; optional lock freezes it in the same request.
 */
interface QuoteCreateRequestInterface
{
    public const CUSTOMER_ID = 'customer_id';
    public const SOURCE_QUOTE_ID = 'source_quote_id';
    public const STORE_ID = 'store_id';
    public const LOCK = 'lock';
    public const ENABLE = 'enable';
    public const INTERNAL_REFERENCE = 'internal_reference';
    public const NOTES = 'notes';
    public const ITEMS = 'items';

    /**
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * @return int|null
     */
    public function getSourceQuoteId(): ?int;

    /**
     * @param int|null $sourceQuoteId
     * @return $this
     */
    public function setSourceQuoteId(?int $sourceQuoteId): self;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId(?int $storeId): self;

    /**
     * When true, quote is locked (immutable) after items are applied.
     *
     * @return bool
     */
    public function getLock(): bool;

    /**
     * @param bool $lock
     * @return $this
     */
    public function setLock(bool $lock): self;

    /**
     * When true, quote becomes the customer's single active cart after create/lock.
     *
     * @return bool
     */
    public function getEnable(): bool;

    /**
     * @param bool $enable
     * @return $this
     */
    public function setEnable(bool $enable): self;

    /**
     * @return string|null
     */
    public function getInternalReference(): ?string;

    /**
     * @param string|null $reference
     * @return $this
     */
    public function setInternalReference(?string $reference): self;

    /**
     * @return string|null
     */
    public function getNotes(): ?string;

    /**
     * @param string|null $notes
     * @return $this
     */
    public function setNotes(?string $notes): self;

    /**
     * Cart lines to add before locking.
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null
     */
    public function getItems(): ?array;

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[]|null $items
     * @return $this
     */
    public function setItems(?array $items): self;
}
