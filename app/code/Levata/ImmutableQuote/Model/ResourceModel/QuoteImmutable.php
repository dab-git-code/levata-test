<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuoteImmutable extends AbstractDb
{
    public const TABLE_NAME = 'quote_inmutable';

    /**
     * quote_id is assigned explicitly (1:1 with quote.entity_id), not autoincrement.
     * Without this Magento strips quote_id from INSERT and the lock never persists.
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'quote_id');
    }

    /**
     * Deactivate all other quotes for customer except the given quote (batch update).
     */
    public function deactivateOtherQuotes(int $customerId, int $activeQuoteId): void
    {
        $connection = $this->getConnection();
        $quoteTable = $this->getTable('quote');
        $connection->update(
            $quoteTable,
            ['is_active' => 0],
            [
                'customer_id = ?' => $customerId,
                'entity_id <> ?' => $activeQuoteId,
                'is_active = ?' => 1,
            ]
        );
    }
}
