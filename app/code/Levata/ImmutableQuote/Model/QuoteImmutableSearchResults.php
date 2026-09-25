<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Data\QuoteImmutableSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class QuoteImmutableSearchResults extends SearchResults implements QuoteImmutableSearchResultsInterface
{
}
