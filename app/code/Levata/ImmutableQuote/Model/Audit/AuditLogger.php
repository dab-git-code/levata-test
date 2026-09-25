<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Audit;

use Levata\ImmutableQuote\Model\Config;
use Magento\Authorization\Model\UserContextInterface;
use Psr\Log\LoggerInterface;

class AuditLogger
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Config $config,
        private readonly UserContextInterface $userContext,
    ) {
    }

    public function quoteCreated(int $quoteId, int $customerId): void
    {
        $this->write('quote_created', [
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
        ]);
    }

    public function quoteLocked(int $quoteId, int $customerId): void
    {
        $this->write('quote_locked', [
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
        ]);
    }

    public function quoteUnlocked(int $quoteId, int $customerId): void
    {
        $this->write('quote_unlocked', [
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
        ]);
    }

    public function quoteEnabled(int $quoteId, int $customerId): void
    {
        $this->write('quote_enabled', [
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
        ]);
    }

    public function quoteDeleted(int $quoteId, int $customerId, bool $wasImmutable): void
    {
        $this->write('quote_deleted', [
            'quote_id' => $quoteId,
            'customer_id' => $customerId,
            'was_immutable' => $wasImmutable,
        ]);
    }

    public function modificationBlocked(int $quoteId, string $action, ?string $remoteIp): void
    {
        $this->write('modification_blocked', [
            'quote_id' => $quoteId,
            'action' => $action,
            'remote_ip' => $remoteIp,
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function write(string $event, array $context): void
    {
        if (!$this->config->isAuditEnabled()) {
            return;
        }

        $context['event'] = $event;
        $context['user_type'] = $this->userContext->getUserType();
        $context['user_id'] = $this->userContext->getUserId();

        $this->logger->info('[levata_immutable_quote] ' . $event, $context);
    }
}
