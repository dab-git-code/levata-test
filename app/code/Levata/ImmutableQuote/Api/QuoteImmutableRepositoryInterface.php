<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api;

use Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface;
use Levata\ImmutableQuote\Api\Data\QuoteImmutableSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface QuoteImmutableRepositoryInterface
{
    /**
     * @param int $quoteId
     * @return QuoteImmutableInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): QuoteImmutableInterface;

    /**
     * @param int $quoteId
     * @return QuoteImmutableInterface
     * @throws NoSuchEntityException
     */
    public function get(int $quoteId): QuoteImmutableInterface;

    /**
     * @param QuoteImmutableInterface $entity
     * @return QuoteImmutableInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteImmutableInterface $entity): QuoteImmutableInterface;

    /**
     * @param QuoteImmutableInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuoteImmutableInterface $entity): bool;

    /**
     * @param int $quoteId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteByQuoteId(int $quoteId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return QuoteImmutableSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): QuoteImmutableSearchResultsInterface;
}
