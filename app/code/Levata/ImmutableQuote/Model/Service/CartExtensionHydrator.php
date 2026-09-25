<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Service;

use Levata\ImmutableQuote\Model\Config;
use Levata\ImmutableQuote\Model\ImmutableStatusRegistry;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

class CartExtensionHydrator
{
    public function __construct(
        private readonly ImmutableStatusRegistry $statusRegistry,
        private readonly CartExtensionFactory $cartExtensionFactory,
        private readonly Config $config,
    ) {
    }

    public function hydrate(CartInterface $cart): void
    {
        $quoteId = (int) $cart->getId();
        if (!$quoteId) {
            return;
        }

        $extensionAttributes = $cart->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->cartExtensionFactory->create();
        }

        if (!$this->config->isEnabled()) {
            $extensionAttributes->setLevataIsImmutable(false);
            $extensionAttributes->setLevataImmutableLockedAt(null);
            $extensionAttributes->setLevataImmutableInternalReference(null);
            $cart->setExtensionAttributes($extensionAttributes);
            return;
        }

        $metadata = $this->statusRegistry->getMetadata($quoteId);
        $isLocked = $metadata !== null && (int) ($metadata['is_locked'] ?? 0) === 1;

        $extensionAttributes->setLevataIsImmutable($isLocked);
        $extensionAttributes->setLevataImmutableLockedAt(
            $isLocked && isset($metadata['locked_at']) ? (string) $metadata['locked_at'] : null
        );
        $extensionAttributes->setLevataImmutableInternalReference(
            $metadata['internal_reference'] ?? null
        );
        $cart->setExtensionAttributes($extensionAttributes);
    }
}
