<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Guard;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Model\Audit\AuditLogger;
use Levata\ImmutableQuote\Model\Config;
use Levata\ImmutableQuote\Model\ImmutableStatusRegistry;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\Data\CartInterface;

class QuoteImmutabilityGuard implements QuoteImmutabilityGuardInterface
{
    public function __construct(
        private readonly ImmutableStatusRegistry $statusRegistry,
        private readonly AuditLogger $auditLogger,
        private readonly EventManager $eventManager,
        private readonly RemoteAddress $remoteAddress,
        private readonly Config $config,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isImmutable(int $quoteId): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return $this->statusRegistry->isLocked($quoteId);
    }

    /**
     * @inheritdoc
     */
    public function isImmutableCart(CartInterface $cart): bool
    {
        return $this->isImmutable((int) $cart->getId());
    }

    /**
     * @inheritdoc
     */
    public function assertMutable(int $quoteId, string $action = 'modify'): void
    {
        if (!$this->isImmutable($quoteId)) {
            return;
        }

        $this->auditLogger->modificationBlocked($quoteId, $action, $this->remoteAddress->getRemoteAddress());
        $this->eventManager->dispatch(
            'levata_immutable_quote_modification_blocked',
            ['quote_id' => $quoteId, 'action' => $action]
        );

        throw new LocalizedException(
            __(
                'This quote is locked by your sales representative and cannot be changed. '
                . 'You can activate another quote from My Account or contact support to request changes.'
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function assertMutableCart(CartInterface $cart, string $action = 'modify'): void
    {
        $this->assertMutable((int) $cart->getId(), $action);
    }
}
