<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model\Webapi;

use Levata\ImmutableQuote\Model\Config;
use Levata\ImmutableQuote\Model\Webapi\RateLimiter;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    public function testSkippedWhenDisabled(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isRateLimitEnabled')->willReturn(false);

        $limiter = new RateLimiter(
            $config,
            $this->createMock(ResourceConnection::class),
            $this->createMock(UserContextInterface::class),
            $this->createMock(DateTime::class)
        );

        $limiter->assertAllowed();
        $this->addToAssertionCount(1);
    }

    public function testThrowsWhenLimitReached(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isRateLimitEnabled')->willReturn(true);
        $config->method('getRateLimitPerHour')->willReturn(100);

        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('forUpdate')->willReturnSelf();

        $connection->method('select')->willReturn($select);
        $connection->method('fetchRow')->willReturn([
            'entity_id' => 1,
            'request_count' => 100,
        ]);
        $connection->expects($this->once())->method('beginTransaction');
        $connection->expects($this->once())->method('rollBack');

        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturn('levata_immutable_quote_api_rate');

        $userContext = $this->createMock(UserContextInterface::class);
        $userContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $userContext->method('getUserId')->willReturn(1);

        $dateTime = $this->createMock(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-09-23-10');

        $limiter = new RateLimiter($config, $resource, $userContext, $dateTime);

        $this->expectException(AuthorizationException::class);
        $limiter->assertAllowed();
    }
}
