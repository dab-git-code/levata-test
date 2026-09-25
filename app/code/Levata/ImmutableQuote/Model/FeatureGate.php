<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Throws when the admin master switch is off.
 */
class FeatureGate
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @throws LocalizedException
     */
    public function assertEnabled(): void
    {
        if (!$this->config->isEnabled()) {
            throw new LocalizedException(
                __('Immutable Quote functionality is disabled. Enable it under Stores → Configuration → Sales → Immutable Quotes.')
            );
        }
    }
}
