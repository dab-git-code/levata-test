<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'levata_immutable_quote/general/enabled';
    private const XML_PATH_RATE_LIMIT_ENABLED = 'levata_immutable_quote/api/rate_limit_enabled';
    private const XML_PATH_RATE_LIMIT_PER_HOUR = 'levata_immutable_quote/api/rate_limit_per_hour';
    private const XML_PATH_AUDIT_ENABLED = 'levata_immutable_quote/audit/enabled';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Master switch: when false, locks are ignored and feature APIs/UI are inactive.
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isRateLimitEnabled(): bool
    {
        return $this->isEnabled()
            && $this->scopeConfig->isSetFlag(self::XML_PATH_RATE_LIMIT_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getRateLimitPerHour(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_RATE_LIMIT_PER_HOUR, ScopeInterface::SCOPE_STORE);
    }

    public function isAuditEnabled(): bool
    {
        return $this->isEnabled()
            && $this->scopeConfig->isSetFlag(self::XML_PATH_AUDIT_ENABLED, ScopeInterface::SCOPE_STORE);
    }
}
