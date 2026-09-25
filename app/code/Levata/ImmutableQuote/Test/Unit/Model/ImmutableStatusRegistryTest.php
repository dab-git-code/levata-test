<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model;

use Levata\ImmutableQuote\Model\ImmutableStatusRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImmutableStatusRegistryTest extends TestCase
{
    private AdapterInterface|MockObject $connection;
    private ImmutableStatusRegistry $registry;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AdapterInterface::class);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($this->connection);
        $resource->method('getTableName')->willReturn('quote_inmutable');

        $this->registry = new ImmutableStatusRegistry($resource);
    }

    public function testIsLockedUsesDatabaseOnceThenCache(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('limit')->willReturnSelf();

        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $this->connection->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['quote_id' => 5, 'is_locked' => 1, 'locked_at' => '2026-01-01 00:00:00']);

        $this->assertTrue($this->registry->isLocked(5));
        $this->assertTrue($this->registry->isLocked(5));
    }

    public function testRegisterOverridesCacheWithoutQuery(): void
    {
        $this->connection->expects($this->never())->method('select');
        $this->registry->register(9, true, ['is_locked' => 1]);
        $this->assertTrue($this->registry->isLocked(9));
    }

    public function testMissingRowMeansNotLocked(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('limit')->willReturnSelf();
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchRow')->willReturn(false);

        $this->assertFalse($this->registry->isLocked(3));
        $this->assertNull($this->registry->getMetadata(3));
    }
}
