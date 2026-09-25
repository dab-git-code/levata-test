<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Webapi;

use Levata\ImmutableQuote\Model\Config;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class RateLimiter
{
    private const TABLE = 'levata_immutable_quote_api_rate';

    public function __construct(
        private readonly Config $config,
        private readonly ResourceConnection $resourceConnection,
        private readonly UserContextInterface $userContext,
        private readonly DateTime $dateTime,
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    public function assertAllowed(): void
    {
        if (!$this->config->isRateLimitEnabled()) {
            return;
        }

        $consumerKey = $this->resolveConsumerKey();
        $windowKey = $this->dateTime->gmtDate('Y-m-d-H');
        $limit = $this->config->getRateLimitPerHour();

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(self::TABLE);

        $connection->beginTransaction();
        try {
            $select = $connection->select()
                ->from($table)
                ->where('consumer_key = ?', $consumerKey)
                ->where('window_key = ?', $windowKey)
                ->forUpdate(true);

            $row = $connection->fetchRow($select);
            $count = $row ? (int) $row['request_count'] : 0;

            if ($count >= $limit) {
                throw new AuthorizationException(
                    __('Rate limit exceeded. Maximum %1 requests per hour.', $limit)
                );
            }

            if ($row) {
                $connection->update(
                    $table,
                    ['request_count' => $count + 1],
                    ['entity_id = ?' => (int) $row['entity_id']]
                );
            } else {
                $connection->insert($table, [
                    'consumer_key' => $consumerKey,
                    'window_key' => $windowKey,
                    'request_count' => 1,
                ]);
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }
    }

    private function resolveConsumerKey(): string
    {
        $userType = (string) $this->userContext->getUserType();
        $userId = (string) $this->userContext->getUserId();
        return hash('sha256', $userType . ':' . $userId);
    }
}
