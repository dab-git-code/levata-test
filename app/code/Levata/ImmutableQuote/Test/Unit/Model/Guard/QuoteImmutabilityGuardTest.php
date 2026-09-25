<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model\Guard;

use Levata\ImmutableQuote\Model\Audit\AuditLogger;
use Levata\ImmutableQuote\Model\Config;
use Levata\ImmutableQuote\Model\Guard\QuoteImmutabilityGuard;
use Levata\ImmutableQuote\Model\ImmutableStatusRegistry;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteImmutabilityGuardTest extends TestCase
{
    private ImmutableStatusRegistry|MockObject $statusRegistry;
    private AuditLogger|MockObject $auditLogger;
    private EventManager|MockObject $eventManager;
    private RemoteAddress|MockObject $remoteAddress;
    private Config|MockObject $config;
    private QuoteImmutabilityGuard $guard;

    protected function setUp(): void
    {
        $this->statusRegistry = $this->createMock(ImmutableStatusRegistry::class);
        $this->auditLogger = $this->createMock(AuditLogger::class);
        $this->eventManager = $this->createMock(EventManager::class);
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $this->config = $this->createMock(Config::class);
        $this->config->method('isEnabled')->willReturn(true);

        $this->guard = new QuoteImmutabilityGuard(
            $this->statusRegistry,
            $this->auditLogger,
            $this->eventManager,
            $this->remoteAddress,
            $this->config
        );
    }

    public function testIsImmutableFalseWhenFeatureDisabled(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn(false);
        $statusRegistry = $this->createMock(ImmutableStatusRegistry::class);
        $statusRegistry->expects($this->never())->method('isLocked');

        $guard = new QuoteImmutabilityGuard(
            $statusRegistry,
            $this->auditLogger,
            $this->eventManager,
            $this->remoteAddress,
            $config
        );

        $this->assertFalse($guard->isImmutable(42));
    }

    public function testIsImmutableDelegatesToRegistry(): void
    {
        $this->statusRegistry->expects($this->once())
            ->method('isLocked')
            ->with(42)
            ->willReturn(true);

        $this->assertTrue($this->guard->isImmutable(42));
    }

    public function testAssertMutableAllowsWhenNotLocked(): void
    {
        $this->statusRegistry->method('isLocked')->with(10)->willReturn(false);
        $this->auditLogger->expects($this->never())->method('modificationBlocked');

        $this->guard->assertMutable(10, 'add_product');
        $this->addToAssertionCount(1);
    }

    public function testAssertMutableThrowsAndAuditsWhenLocked(): void
    {
        $this->statusRegistry->method('isLocked')->with(10)->willReturn(true);
        $this->remoteAddress->method('getRemoteAddress')->willReturn('127.0.0.1');

        $this->auditLogger->expects($this->once())
            ->method('modificationBlocked')
            ->with(10, 'add_product', '127.0.0.1');

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'levata_immutable_quote_modification_blocked',
                ['quote_id' => 10, 'action' => 'add_product']
            );

        $this->expectException(LocalizedException::class);
        $this->guard->assertMutable(10, 'add_product');
    }

    public function testAssertMutableCartUsesCartId(): void
    {
        $cart = $this->createMock(CartInterface::class);
        $cart->method('getId')->willReturn(55);
        $this->statusRegistry->method('isLocked')->with(55)->willReturn(false);

        $this->guard->assertMutableCart($cart, 'update_item');
        $this->addToAssertionCount(1);
    }
}
