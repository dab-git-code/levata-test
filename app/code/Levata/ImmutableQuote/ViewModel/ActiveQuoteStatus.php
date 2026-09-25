<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\ViewModel;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ActiveQuoteStatus implements ArgumentInterface
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly Config $config,
    ) {
    }

    public function isFeatureEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function isActiveQuoteImmutable(): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        try {
            $quoteId = (int) $this->checkoutSession->getQuoteId();
            return $quoteId > 0 && $this->guard->isImmutable($quoteId);
        } catch (\Throwable) {
            return false;
        }
    }

    public function getActiveQuoteId(): int
    {
        return (int) $this->checkoutSession->getQuoteId();
    }
}
