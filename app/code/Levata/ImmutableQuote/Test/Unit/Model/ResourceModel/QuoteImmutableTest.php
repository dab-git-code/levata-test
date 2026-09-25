<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model\ResourceModel;

use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class QuoteImmutableTest extends TestCase
{
    public function testPrimaryKeyIsNotAutoIncrement(): void
    {
        $resource = (new ReflectionClass(QuoteImmutable::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(QuoteImmutable::class))->getProperty('_isPkAutoIncrement');
        $property->setAccessible(true);

        $this->assertFalse(
            $property->getValue($resource),
            'quote_id must be included in INSERT or lock rows never persist'
        );
    }
}
