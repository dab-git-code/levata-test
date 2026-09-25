<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Feature;

use Levata\ImmutableQuote\Model\FeatureGate;

/**
 * Blocks Immutable Quote management APIs when the admin master switch is off.
 */
class DisableManagementApi
{
    public function __construct(
        private readonly FeatureGate $featureGate,
    ) {
    }

    public function beforeCreateQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeLockQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeUnlockQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeEnableQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeGetCustomerQuotes(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeGetQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeDeleteQuote(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeSaveItem(): void
    {
        $this->featureGate->assertEnabled();
    }

    public function beforeSetPaymentMethod(): void
    {
        $this->featureGate->assertEnabled();
    }
}
