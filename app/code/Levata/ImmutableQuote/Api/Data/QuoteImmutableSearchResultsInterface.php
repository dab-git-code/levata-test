<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QuoteImmutableSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface[]
     */
    public function getItems();

    /**
     * @param \Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
