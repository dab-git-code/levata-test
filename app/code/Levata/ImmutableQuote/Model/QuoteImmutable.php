<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface;
use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable as QuoteImmutableResource;
use Magento\Framework\Model\AbstractModel;

class QuoteImmutable extends AbstractModel implements QuoteImmutableInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(QuoteImmutableResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId(): int
    {
        return (int) $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritdoc
     *
     * quote_id is the primary key (non-autoincrement). Setting it before the first
     * persist must keep the model marked as new; otherwise Magento issues UPDATE
     * against a missing row and the lock is silently lost.
     */
    public function setQuoteId(int $quoteId): QuoteImmutableInterface
    {
        if ($this->getOrigData(self::QUOTE_ID) === null) {
            $this->isObjectNew(true);
        }

        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritdoc
     */
    public function getIsLocked(): bool
    {
        return (bool) $this->getData(self::IS_LOCKED);
    }

    /**
     * @inheritdoc
     */
    public function setIsLocked(bool $isLocked): QuoteImmutableInterface
    {
        return $this->setData(self::IS_LOCKED, $isLocked ? 1 : 0);
    }

    /**
     * @inheritdoc
     */
    public function getLockedAt(): ?string
    {
        $value = $this->getData(self::LOCKED_AT);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setLockedAt(string $lockedAt): QuoteImmutableInterface
    {
        return $this->setData(self::LOCKED_AT, $lockedAt);
    }

    /**
     * @inheritdoc
     */
    public function getUnlockedAt(): ?string
    {
        $value = $this->getData(self::UNLOCKED_AT);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setUnlockedAt(?string $unlockedAt): QuoteImmutableInterface
    {
        return $this->setData(self::UNLOCKED_AT, $unlockedAt);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedByAdminId(): ?int
    {
        $value = $this->getData(self::CREATED_BY_ADMIN_ID);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedByAdminId(?int $adminId): QuoteImmutableInterface
    {
        return $this->setData(self::CREATED_BY_ADMIN_ID, $adminId);
    }

    /**
     * @inheritdoc
     */
    public function getInternalReference(): ?string
    {
        $value = $this->getData(self::INTERNAL_REFERENCE);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setInternalReference(?string $reference): QuoteImmutableInterface
    {
        return $this->setData(self::INTERNAL_REFERENCE, $reference);
    }

    /**
     * @inheritdoc
     */
    public function getNotes(): ?string
    {
        $value = $this->getData(self::NOTES);
        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setNotes(?string $notes): QuoteImmutableInterface
    {
        return $this->setData(self::NOTES, $notes);
    }
}
