<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface;
use Levata\ImmutableQuote\Api\Data\QuoteImmutableSearchResultsInterface;
use Levata\ImmutableQuote\Api\QuoteImmutableRepositoryInterface;
use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable as QuoteImmutableResource;
use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteImmutableRepository implements QuoteImmutableRepositoryInterface
{
    public function __construct(
        private readonly QuoteImmutableFactory $quoteImmutableFactory,
        private readonly QuoteImmutableResource $resource,
        private readonly CollectionFactory $collectionFactory,
        private readonly QuoteImmutableSearchResultsFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getByQuoteId(int $quoteId): QuoteImmutableInterface
    {
        return $this->get($quoteId);
    }

    /**
     * @inheritdoc
     */
    public function get(int $quoteId): QuoteImmutableInterface
    {
        $entity = $this->quoteImmutableFactory->create();
        $this->resource->load($entity, $quoteId, QuoteImmutableInterface::QUOTE_ID);
        if (!$entity->getQuoteId()) {
            throw new NoSuchEntityException(
                __('Immutable metadata for quote "%1" does not exist.', $quoteId)
            );
        }
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function save(QuoteImmutableInterface $entity): QuoteImmutableInterface
    {
        try {
            /** @var QuoteImmutable $entity */
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save immutable quote metadata: %1', $exception->getMessage()),
                $exception
            );
        }
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function delete(QuoteImmutableInterface $entity): bool
    {
        try {
            /** @var QuoteImmutable $entity */
            $this->resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete immutable quote metadata: %1', $exception->getMessage()),
                $exception
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByQuoteId(int $quoteId): bool
    {
        $entity = $this->get($quoteId);
        return $this->delete($entity);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): QuoteImmutableSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var QuoteImmutableSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
