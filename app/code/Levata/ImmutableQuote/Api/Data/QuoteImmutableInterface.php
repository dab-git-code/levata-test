<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api\Data;

/**
 * Immutable quote metadata row (1:1 with Magento quote).
 */
interface QuoteImmutableInterface
{
    public const QUOTE_ID = 'quote_id';
    public const IS_LOCKED = 'is_locked';
    public const LOCKED_AT = 'locked_at';
    public const UNLOCKED_AT = 'unlocked_at';
    public const CREATED_BY_ADMIN_ID = 'created_by_admin_id';
    public const INTERNAL_REFERENCE = 'internal_reference';
    public const NOTES = 'notes';

    /**
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId(int $quoteId): self;

    /**
     * @return bool
     */
    public function getIsLocked(): bool;

    /**
     * @param bool $isLocked
     * @return $this
     */
    public function setIsLocked(bool $isLocked): self;

    /**
     * @return string|null
     */
    public function getLockedAt(): ?string;

    /**
     * @param string $lockedAt
     * @return $this
     */
    public function setLockedAt(string $lockedAt): self;

    /**
     * @return string|null
     */
    public function getUnlockedAt(): ?string;

    /**
     * @param string|null $unlockedAt
     * @return $this
     */
    public function setUnlockedAt(?string $unlockedAt): self;

    /**
     * @return int|null
     */
    public function getCreatedByAdminId(): ?int;

    /**
     * @param int|null $adminId
     * @return $this
     */
    public function setCreatedByAdminId(?int $adminId): self;

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
}
