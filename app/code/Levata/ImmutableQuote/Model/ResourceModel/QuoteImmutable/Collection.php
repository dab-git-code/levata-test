<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable;

use Levata\ImmutableQuote\Model\QuoteImmutable;
use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable as QuoteImmutableResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(QuoteImmutable::class, QuoteImmutableResource::class);
    }
}
