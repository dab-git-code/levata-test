<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Data;

use Levata\ImmutableQuote\Api\Data\QuoteLockRequestInterface;
use Magento\Framework\DataObject;

class QuoteLockRequest extends DataObject implements QuoteLockRequestInterface
{
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
    public function setInternalReference(?string $reference): QuoteLockRequestInterface
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
    public function setNotes(?string $notes): QuoteLockRequestInterface
    {
        return $this->setData(self::NOTES, $notes);
    }
}
