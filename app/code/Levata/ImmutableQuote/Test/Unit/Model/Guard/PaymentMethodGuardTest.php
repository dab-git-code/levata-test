<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Test\Unit\Model\Guard;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Model\Guard\PaymentMethodGuard;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodGuardTest extends TestCase
{
    private QuoteImmutabilityGuardInterface|MockObject $immutabilityGuard;
    private CartRepositoryInterface|MockObject $cartRepository;
    private PaymentMethodGuard $guard;

    protected function setUp(): void
    {
        $this->immutabilityGuard = $this->createMock(QuoteImmutabilityGuardInterface::class);
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->guard = new PaymentMethodGuard($this->immutabilityGuard, $this->cartRepository);
    }

    public function testAssertCanAssignAllowsMutableQuote(): void
    {
        $this->immutabilityGuard->method('isImmutable')->with(1)->willReturn(false);
        $this->immutabilityGuard->expects($this->never())->method('assertMutable');

        $this->guard->assertCanAssign(1, 'checkmo');
        $this->addToAssertionCount(1);
    }

    public function testAssertCanAssignAllowsSameMethodOnLockedQuote(): void
    {
        $this->immutabilityGuard->method('isImmutable')->with(1)->willReturn(true);
        $this->stubPaymentMethod(1, 'checkmo');
        $this->immutabilityGuard->expects($this->never())->method('assertMutable');

        $this->guard->assertCanAssign(1, 'checkmo');
        $this->addToAssertionCount(1);
    }

    public function testAssertCanAssignBlocksDifferentMethodOnLockedQuote(): void
    {
        $this->immutabilityGuard->method('isImmutable')->with(1)->willReturn(true);
        $this->stubPaymentMethod(1, 'checkmo');
        $this->immutabilityGuard->expects($this->once())
            ->method('assertMutable')
            ->with(1, 'change_payment_method')
            ->willThrowException(new LocalizedException(__('locked')));

        $this->expectException(LocalizedException::class);
        $this->guard->assertCanAssign(1, 'paypal_express');
    }

    public function testAssertCanAssignFailsWhenLockedWithoutPayment(): void
    {
        $this->immutabilityGuard->method('isImmutable')->with(1)->willReturn(true);
        $this->stubPaymentMethod(1, null);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('no payment method');
        $this->guard->assertCanAssign(1, 'checkmo');
    }

    public function testAssertPlaceOrderPaymentForcesLockedMethod(): void
    {
        $this->immutabilityGuard->method('isImmutable')->with(1)->willReturn(true);
        $this->stubPaymentMethod(1, 'checkmo');

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn('checkmo');
        $payment->expects($this->once())->method('setMethod')->with('checkmo');

        $this->guard->assertPlaceOrderPayment(1, $payment);
    }

    private function stubPaymentMethod(int $quoteId, ?string $method): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getMethod')->willReturn($method);

        $quote = $this->createMock(Quote::class);
        $quote->method('getPayment')->willReturn($payment);

        $this->cartRepository->method('get')->with($quoteId)->willReturn($quote);
    }
}
