<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Checkout;

use Levata\ImmutableQuote\ViewModel\ActiveQuoteStatus;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly ActiveQuoteStatus $activeQuoteStatus,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return [
            'levataIsImmutable' => $this->activeQuoteStatus->isActiveQuoteImmutable(),
        ];
    }
}
