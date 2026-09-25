<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Api\Data;

interface QuoteLockRequestInterface
{
    public const INTERNAL_REFERENCE = 'internal_reference';
    public const NOTES = 'notes';

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
