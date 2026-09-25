<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Block\Checkout;

use Levata\ImmutableQuote\ViewModel\ActiveQuoteStatus;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ImmutableNotice extends Template
{
    public function __construct(
        Context $context,
        private readonly ActiveQuoteStatus $activeQuoteStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        if ($this->activeQuoteStatus->isActiveQuoteImmutable()) {
            $this->pageConfig->addBodyClass('levata-immutable-quote');
        }
        return parent::_prepareLayout();
    }

    public function isImmutable(): bool
    {
        return $this->activeQuoteStatus->isActiveQuoteImmutable();
    }

    public function getQuotesUrl(): string
    {
        return $this->getUrl('immutablequote/quotes/index');
    }
}
