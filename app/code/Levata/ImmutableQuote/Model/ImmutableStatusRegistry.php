<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable as QuoteImmutableResource;
use Magento\Framework\App\ResourceConnection;

/**
 * Request-scoped cache for immutable status to avoid repeated DB reads.
 */
class ImmutableStatusRegistry
{
    /** @var array<int, bool|null> null = unknown */
    private array $lockedByQuoteId = [];

    /** @var array<int, array<string, mixed>|null> */
    private array $metadataByQuoteId = [];

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    public function isLocked(int $quoteId): bool
    {
        if (array_key_exists($quoteId, $this->lockedByQuoteId)) {
            return (bool) $this->lockedByQuoteId[$quoteId];
        }

        $metadata = $this->loadMetadata($quoteId);
        $this->lockedByQuoteId[$quoteId] = $metadata !== null && (int) ($metadata['is_locked'] ?? 0) === 1;
        return $this->lockedByQuoteId[$quoteId];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMetadata(int $quoteId): ?array
    {
        if (!array_key_exists($quoteId, $this->metadataByQuoteId)) {
            $this->loadMetadata($quoteId);
        }
        return $this->metadataByQuoteId[$quoteId];
    }

    public function register(int $quoteId, bool $isLocked, ?array $metadata = null): void
    {
        $this->lockedByQuoteId[$quoteId] = $isLocked;
        $this->metadataByQuoteId[$quoteId] = $metadata;
    }

    public function clear(int $quoteId): void
    {
        unset($this->lockedByQuoteId[$quoteId], $this->metadataByQuoteId[$quoteId]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadMetadata(int $quoteId): ?array
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(QuoteImmutableResource::TABLE_NAME);
        $row = $connection->fetchRow(
            $connection->select()
                ->from($table)
                ->where('quote_id = ?', $quoteId)
                ->limit(1)
        );

        $this->metadataByQuoteId[$quoteId] = $row ?: null;
        if ($row) {
            $this->lockedByQuoteId[$quoteId] = (int) ($row['is_locked'] ?? 0) === 1;
        } else {
            $this->lockedByQuoteId[$quoteId] = false;
        }

        return $this->metadataByQuoteId[$quoteId];
    }
}
