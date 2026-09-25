<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\Model;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Quote\Model\Quote;

class PreventMerge
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param Quote $subject
     * @param Quote $quote
     * @return void
     */
    public function beforeMerge(Quote $subject, Quote $quote): void
    {
        if ($subject->getId()) {
            $this->guard->assertMutable((int) $subject->getId(), 'merge_cart');
        }
        if ($quote->getId()) {
            $this->guard->assertMutable((int) $quote->getId(), 'merge_cart_source');
        }
    }
}
