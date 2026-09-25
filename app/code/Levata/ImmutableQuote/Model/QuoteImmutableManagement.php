<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model;

use Levata\ImmutableQuote\Api\Data\QuoteCreateRequestInterface;
use Levata\ImmutableQuote\Api\Data\QuoteImmutableInterface;
use Levata\ImmutableQuote\Api\Data\QuoteLockRequestInterface;
use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Levata\ImmutableQuote\Api\QuoteImmutableManagementInterface;
use Levata\ImmutableQuote\Api\QuoteImmutableRepositoryInterface;
use Levata\ImmutableQuote\Api\QuoteItemManagementInterface;
use Levata\ImmutableQuote\Model\Audit\AuditLogger;
use Levata\ImmutableQuote\Model\Data\QuoteLockRequestFactory;
use Levata\ImmutableQuote\Model\ResourceModel\QuoteImmutable as QuoteImmutableResource;
use Levata\ImmutableQuote\Model\Service\CartExtensionHydrator;
use Levata\ImmutableQuote\Model\Service\QuoteCloner;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

class QuoteImmutableManagement implements QuoteImmutableManagementInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly QuoteImmutableRepositoryInterface $immutableRepository,
        private readonly QuoteImmutableFactory $immutableFactory,
        private readonly QuoteImmutableResource $immutableResource,
        private readonly QuoteCollectionFactory $quoteCollectionFactory,
        private readonly QuoteCloner $quoteCloner,
        private readonly QuoteItemManagementInterface $quoteItemManagement,
        private readonly QuoteLockRequestFactory $lockRequestFactory,
        private readonly QuoteImmutabilityGuardInterface $guard,
        private readonly ImmutableStatusRegistry $statusRegistry,
        private readonly CartExtensionHydrator $cartExtensionHydrator,
        private readonly AuditLogger $auditLogger,
        private readonly EventManager $eventManager,
        private readonly UserContextInterface $userContext,
        private readonly DateTime $dateTime,
        private readonly CartSearchResultsInterfaceFactory $cartSearchResultsFactory,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function createQuote(QuoteCreateRequestInterface $request): CartInterface
    {
        $customerId = $request->getCustomerId();
        $this->customerRepository->getById($customerId);
        $storeId = $request->getStoreId();
        $sourceQuoteId = $request->getSourceQuoteId();

        if ($sourceQuoteId !== null) {
            $source = $this->getQuote($sourceQuoteId);
            $cart = $this->quoteCloner->cloneFrom($source, $customerId, $storeId);
            $this->auditLogger->quoteCreated((int) $cart->getId(), $customerId);
            $this->eventManager->dispatch(
                'levata_immutable_quote_created',
                [
                    'quote' => $cart,
                    'customer_id' => $customerId,
                    'source_quote_id' => $sourceQuoteId,
                ]
            );
        } else {
            $cart = $this->quoteCloner->createEmpty($customerId, $storeId);
            $this->auditLogger->quoteCreated((int) $cart->getId(), $customerId);
            $this->eventManager->dispatch(
                'levata_immutable_quote_created',
                ['quote' => $cart, 'customer_id' => $customerId, 'source_quote_id' => null]
            );
        }

        $quoteId = (int) $cart->getId();
        $this->applyItems($quoteId, $request->getItems());

        if ($request->getLock()) {
            $lockRequest = $this->lockRequestFactory->create();
            $lockRequest->setInternalReference($request->getInternalReference());
            $lockRequest->setNotes($request->getNotes());
            $this->lockQuote($quoteId, $lockRequest);
        }

        if ($request->getEnable()) {
            $cart = $this->enableQuote($quoteId);
        } else {
            $cart = $this->getQuote($quoteId);
        }

        $this->cartExtensionHydrator->hydrate($cart);
        return $cart;
    }

    /**
     * @param CartItemInterface[]|null $items
     */
    private function applyItems(int $quoteId, ?array $items): void
    {
        if ($items === null || $items === []) {
            return;
        }

        foreach ($items as $item) {
            if (!$item instanceof CartItemInterface) {
                throw new LocalizedException(__('Invalid cart item payload.'));
            }
            $this->quoteItemManagement->saveItem($quoteId, $item);
        }
    }

    /**
     * @inheritdoc
     */
    public function lockQuote(int $quoteId, ?QuoteLockRequestInterface $lockRequest = null): QuoteImmutableInterface
    {
        $cart = $this->getQuote($quoteId);
        $this->guard->assertMutableCart($cart, 'lock');

        try {
            $existing = $this->immutableRepository->getByQuoteId($quoteId);
            if ($existing->getIsLocked()) {
                throw new LocalizedException(__('Quote is already immutable.'));
            }
            $entity = $existing;
        } catch (NoSuchEntityException) {
            $entity = $this->immutableFactory->create();
            $entity->isObjectNew(true);
            $entity->setQuoteId($quoteId);
        }

        $entity->setIsLocked(true);
        $entity->setLockedAt($this->dateTime->gmtDate());
        $entity->setUnlockedAt(null);

        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN) {
            $entity->setCreatedByAdminId((int) $this->userContext->getUserId());
        }

        if ($lockRequest !== null) {
            $entity->setInternalReference($lockRequest->getInternalReference());
            $entity->setNotes($lockRequest->getNotes());
        }

        $saved = $this->immutableRepository->save($entity);
        $this->statusRegistry->register(
            $quoteId,
            true,
            [
                'is_locked' => 1,
                'locked_at' => $saved->getLockedAt(),
                'internal_reference' => $saved->getInternalReference(),
            ]
        );

        $customerId = (int) $cart->getCustomerId();
        $this->auditLogger->quoteLocked($quoteId, $customerId);
        $this->eventManager->dispatch(
            'levata_immutable_quote_locked',
            ['quote_id' => $quoteId, 'customer_id' => $customerId, 'immutable' => $saved]
        );

        return $saved;
    }

    /**
     * @inheritdoc
     */
    public function unlockQuote(int $quoteId): QuoteImmutableInterface
    {
        $cart = $this->getQuote($quoteId);
        $entity = $this->immutableRepository->getByQuoteId($quoteId);

        if (!$entity->getIsLocked()) {
            throw new LocalizedException(__('Quote is not immutable.'));
        }

        $entity->setIsLocked(false);
        $entity->setUnlockedAt($this->dateTime->gmtDate());
        $saved = $this->immutableRepository->save($entity);

        $this->statusRegistry->register($quoteId, false, [
            'is_locked' => 0,
            'unlocked_at' => $saved->getUnlockedAt(),
        ]);

        $customerId = (int) $cart->getCustomerId();
        $this->auditLogger->quoteUnlocked($quoteId, $customerId);
        $this->eventManager->dispatch(
            'levata_immutable_quote_unlocked',
            ['quote_id' => $quoteId, 'customer_id' => $customerId, 'immutable' => $saved]
        );

        return $saved;
    }

    /**
     * @inheritdoc
     */
    public function enableQuote(int $quoteId): CartInterface
    {
        $cart = $this->getQuote($quoteId);
        $customerId = (int) $cart->getCustomerId();
        if (!$customerId) {
            throw new LocalizedException(__('Quote must belong to a registered customer.'));
        }

        $this->immutableResource->deactivateOtherQuotes($customerId, $quoteId);
        $cart->setIsActive(true);
        $this->cartRepository->save($cart);

        $this->auditLogger->quoteEnabled($quoteId, $customerId);
        $this->eventManager->dispatch(
            'levata_immutable_quote_enabled',
            ['quote_id' => $quoteId, 'customer_id' => $customerId, 'quote' => $cart]
        );

        $this->cartExtensionHydrator->hydrate($cart);
        return $cart;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerQuotes(int $customerId): CartSearchResultsInterface
    {
        $this->customerRepository->getById($customerId);
        $collection = $this->quoteCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->setOrder('updated_at', 'DESC');

        $quotes = [];
        foreach ($collection as $quote) {
            /** @var CartInterface $quote */
            $this->cartExtensionHydrator->hydrate($quote);
            $quotes[] = $quote;
        }

        $searchResults = $this->cartSearchResultsFactory->create();
        $searchResults->setItems($quotes);
        $searchResults->setTotalCount(count($quotes));
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getQuote(int $quoteId): CartInterface
    {
        $cart = $this->cartRepository->get($quoteId);
        $this->cartExtensionHydrator->hydrate($cart);
        return $cart;
    }

    /**
     * @inheritdoc
     */
    public function deleteQuote(int $quoteId): bool
    {
        $cart = $this->getQuote($quoteId);
        $customerId = (int) $cart->getCustomerId();
        $wasImmutable = $this->guard->isImmutable($quoteId);

        try {
            $this->cartRepository->delete($cart);
        } catch (\Exception $exception) {
            throw new LocalizedException(
                __('Could not delete quote #%1.', $quoteId),
                $exception
            );
        }

        $this->statusRegistry->clear($quoteId);
        $this->auditLogger->quoteDeleted($quoteId, $customerId, $wasImmutable);
        $this->eventManager->dispatch(
            'levata_immutable_quote_deleted',
            [
                'quote_id' => $quoteId,
                'customer_id' => $customerId,
                'was_immutable' => $wasImmutable,
            ]
        );

        return true;
    }
}
