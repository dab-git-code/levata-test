<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Plugin\Quote\Model;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Plugin\Quote\Model\PreventItemChanges;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PreventItemChangesTest extends TestCase
{
    private QuoteImmutabilityGuardInterface|MockObject $guard;
    private PreventItemChanges $plugin;

    protected function setUp(): void
    {
        $this->guard = $this->createMock(QuoteImmutabilityGuardInterface::class);
        $this->plugin = new PreventItemChanges($this->guard);
    }

    public function testBeforeAddProductAssertsMutable(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getId')->willReturn(7);
        $product = $this->createMock(Product::class);

        $this->guard->expects($this->once())->method('assertMutable')->with(7, 'add_product');

        $this->plugin->beforeAddProduct($quote, $product);
    }

    public function testSkipsWhenQuoteHasNoId(): void
    {
        $quote = $this->createMock(Quote::class);
        $quote->method('getId')->willReturn(null);
        $this->guard->expects($this->never())->method('assertMutable');

        $this->plugin->beforeRemoveItem($quote, 1);
    }
}
