<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\CustomerData\Cart;

use Levata\ImmutableQuote\Model\Config;
use Levata\ImmutableQuote\Model\ImmutableStatusRegistry;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;

class AppendImmutableFlag
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly ImmutableStatusRegistry $statusRegistry,
        private readonly Config $config,
    ) {
    }

    /**
     * @param Cart $subject
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function afterGetSectionData(Cart $subject, array $result): array
    {
        if (!$this->config->isEnabled()) {
            $result['levata_is_immutable'] = false;
            return $result;
        }

        $quoteId = (int) $this->checkoutSession->getQuoteId();
        $result['levata_is_immutable'] = $quoteId > 0 && $this->statusRegistry->isLocked($quoteId);
        return $result;
    }
}
