<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Webapi;

use Levata\ImmutableQuote\Model\Webapi\RateLimiter;

/**
 * Applies configurable hourly rate limits to Immutable Quote Web API entry points.
 */
class RateLimitManagement
{
    public function __construct(
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    public function beforeCreateQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeLockQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeUnlockQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeEnableQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeGetCustomerQuotes(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeGetQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeDeleteQuote(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeSaveItem(): void
    {
        $this->rateLimiter->assertAllowed();
    }

    public function beforeSetPaymentMethod(): void
    {
        $this->rateLimiter->assertAllowed();
    }
}
