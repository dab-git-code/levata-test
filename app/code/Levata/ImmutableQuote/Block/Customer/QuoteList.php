<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Block\Customer;

use Levata\ImmutableQuote\Api\QuoteImmutableManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\Data\CartInterface;

class QuoteList extends Template
{
    public function __construct(
        Context $context,
        private readonly QuoteImmutableManagementInterface $quoteManagement,
        private readonly CustomerSession $customerSession,
        private readonly PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return CartInterface[]
     */
    public function getQuotes(): array
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        if ($customerId <= 0) {
            return [];
        }

        return $this->quoteManagement->getCustomerQuotes($customerId)->getItems() ?: [];
    }

    public function isImmutable(CartInterface $cart): bool
    {
        $extension = $cart->getExtensionAttributes();
        return $extension !== null && (bool) $extension->getLevataIsImmutable();
    }

    public function getInternalReference(CartInterface $cart): ?string
    {
        $extension = $cart->getExtensionAttributes();
        if ($extension === null) {
            return null;
        }
        $reference = $extension->getLevataImmutableInternalReference();
        return $reference !== null && $reference !== '' ? (string) $reference : null;
    }

    public function getEnableUrl(int $quoteId): string
    {
        return $this->getUrl('immutablequote/quotes/enable', ['quote_id' => $quoteId]);
    }

    public function formatQuoteTotal(CartInterface $cart): string
    {
        return $this->priceCurrency->format(
            (float) $cart->getGrandTotal(),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            (int) $cart->getStoreId()
        );
    }
}
