<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api;

use Levata\ImmutableQuote\Api\Data\QuoteCreateRequestInterface;
use Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface;
use Levata\ImmutableQuote\Api\Data\QuoteLockRequestInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;

interface QuoteImmutableManagementInterface
{
    /**
     * Create inactive customer quote (empty or clone), optionally add items, lock and/or enable in one request.
     * Order of operations: create/clone → items → lock → enable.
     *
     * @param QuoteCreateRequestInterface $request
     * @return CartInterface
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function createQuote(QuoteCreateRequestInterface $request): CartInterface;

    /**
     * Mark quote as immutable (locked). Quote must have no existing active lock.
     *
     * @param int $quoteId
     * @param QuoteLockRequestInterface|null $lockRequest
     * @return QuoteImmutableInterface
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function lockQuote(int $quoteId, ?QuoteLockRequestInterface $lockRequest = null): QuoteImmutableInterface;

    /**
     * Remove immutability (quote becomes mutable again). Row kept for audit.
     *
     * @param int $quoteId
     * @return QuoteImmutableInterface
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function unlockQuote(int $quoteId): QuoteImmutableInterface;

    /**
     * Set quote as the single active cart for its customer (Magento is_active semantics).
     *
     * @param int $quoteId
     * @return CartInterface
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function enableQuote(int $quoteId): CartInterface;

    /**
     * Customer quotes (active and inactive) with extension attributes hydrated.
     *
     * @param int $customerId
     * @return CartSearchResultsInterface
     * @throws LocalizedException
     */
    public function getCustomerQuotes(int $customerId): CartSearchResultsInterface;

    /**
     * @param int $quoteId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    public function getQuote(int $quoteId): CartInterface;

    /**
     * Hard-delete Magento quote. Immutable metadata is removed via FK CASCADE.
     *
     * @param int $quoteId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteQuote(int $quoteId): bool;
}
