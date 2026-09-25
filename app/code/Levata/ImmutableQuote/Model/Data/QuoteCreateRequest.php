<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Data;

use Levata\ImmutableQuote\Api\Data\QuoteCreateRequestInterface;
use Magento\Framework\DataObject;

class QuoteCreateRequest extends DataObject implements QuoteCreateRequestInterface
{
    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): QuoteCreateRequestInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getSourceQuoteId(): ?int
    {
        $value = $this->getData(self::SOURCE_QUOTE_ID);
        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setSourceQuoteId(?int $sourceQuoteId): QuoteCreateRequestInterface
    {
        return $this->setData(self::SOURCE_QUOTE_ID, $sourceQuoteId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        $value = $this->getData(self::STORE_ID);
        return $value !== null && $value !== '' ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setStoreId(?int $storeId): QuoteCreateRequestInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getLock(): bool
    {
        return (bool) $this->getData(self::LOCK);
    }

    /**
     * @inheritdoc
     */
    public function setLock(bool $lock): QuoteCreateRequestInterface
    {
        return $this->setData(self::LOCK, $lock);
    }

    /**
     * @inheritdoc
     */
    public function getEnable(): bool
    {
        return (bool) $this->getData(self::ENABLE);
    }

    /**
     * @inheritdoc
     */
    public function setEnable(bool $enable): QuoteCreateRequestInterface
    {
        return $this->setData(self::ENABLE, $enable);
    }

    /**
     * @inheritdoc
     */
    public function getInternalReference(): ?string
    {
        $value = $this->getData(self::INTERNAL_REFERENCE);
        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setInternalReference(?string $reference): QuoteCreateRequestInterface
    {
        return $this->setData(self::INTERNAL_REFERENCE, $reference);
    }

    /**
     * @inheritdoc
     */
    public function getNotes(): ?string
    {
        $value = $this->getData(self::NOTES);
        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setNotes(?string $notes): QuoteCreateRequestInterface
    {
        return $this->setData(self::NOTES, $notes);
    }

    /**
     * @inheritdoc
     */
    public function getItems(): ?array
    {
        $items = $this->getData(self::ITEMS);
        return is_array($items) ? $items : null;
    }

    /**
     * @inheritdoc
     */
    public function setItems(?array $items): QuoteCreateRequestInterface
    {
        return $this->setData(self::ITEMS, $items);
    }
}
