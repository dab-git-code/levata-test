<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model\Service;

use Levata\ImmutableQuote\Model\Service\CustomPriceApplier;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomPriceApplierTest extends TestCase
{
    private CartRepositoryInterface|MockObject $cartRepository;
    private CustomPriceApplier $applier;

    protected function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->applier = new CustomPriceApplier($this->cartRepository, new Json());
    }

    public function testExtractReturnsNullWhenNoExtensionAttributes(): void
    {
        $item = $this->createMock(CartItemInterface::class);
        $item->method('getExtensionAttributes')->willReturn(null);

        $this->assertNull($this->applier->extractCustomPrice($item));
    }

    public function testExtractReadsLevataCustomPriceFromExtension(): void
    {
        $extension = new class {
            public function getLevataCustomPrice(): float
            {
                return 5.0;
            }
        };

        $item = $this->createMock(CartItemInterface::class);
        $item->method('getExtensionAttributes')->willReturn($extension);

        $this->assertSame(5.0, $this->applier->extractCustomPrice($item));
    }

    public function testExtractReadsFromGetDataFallback(): void
    {
        $extension = new class {
            public function getData(string $key)
            {
                return $key === 'levata_custom_price' ? '9.95' : null;
            }
        };

        $item = $this->createMock(CartItemInterface::class);
        $item->method('getExtensionAttributes')->willReturn($extension);

        $this->assertSame(9.95, $this->applier->extractCustomPrice($item));
    }
}
