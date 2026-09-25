<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Model\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Creates inactive customer quotes and clones existing ones without touching Magento merge plugins.
 */
class QuoteCloner
{
    public function __construct(
        private readonly QuoteFactory $quoteFactory,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function createEmpty(int $customerId, ?int $storeId = null): CartInterface
    {
        $customer = $this->customerRepository->getById($customerId);
        $resolvedStoreId = $storeId ?? (int) $this->storeManager->getStore()->getId();

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($resolvedStoreId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        $quote->setIsActive(0);
        $quote->setIsMultiShipping(0);

        try {
            $this->cartRepository->save($quote);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not create quote for customer.'),
                $exception
            );
        }

        return $this->cartRepository->get((int) $quote->getId());
    }

    /**
     * Clone source quote into a new inactive quote (items + addresses + coupon).
     * Source quote is never modified.
     *
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function cloneFrom(CartInterface $source, int $customerId, ?int $storeId = null): CartInterface
    {
        if ((int) $source->getCustomerId() !== $customerId) {
            throw new LocalizedException(__('Source quote does not belong to this customer.'));
        }

        /** @var Quote $sourceQuote */
        $sourceQuote = $source;
        $targetStoreId = $storeId ?? (int) $sourceQuote->getStoreId();
        /** @var Quote $target */
        $target = $this->createEmpty($customerId, $targetStoreId);

        foreach ($sourceQuote->getAllVisibleItems() as $item) {
            $newItem = clone $item;
            $newItem->setId(null);
            $target->addItem($newItem);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $newChild = clone $child;
                    $newChild->setId(null);
                    $newChild->setParentItem($newItem);
                    $target->addItem($newChild);
                }
            }
        }

        $this->copyAddress($sourceQuote->getBillingAddress(), $target->getBillingAddress());
        if (!$sourceQuote->isVirtual()) {
            $this->copyAddress($sourceQuote->getShippingAddress(), $target->getShippingAddress());
        }

        if ($sourceQuote->getCouponCode()) {
            $target->setCouponCode($sourceQuote->getCouponCode());
        }

        $target->setIsActive(0);
        $target->collectTotals();

        try {
            $this->cartRepository->save($target);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not clone quote #%1.', $sourceQuote->getId()),
                $exception
            );
        }

        return $this->cartRepository->get((int) $target->getId());
    }

    private function copyAddress(Quote\Address $source, Quote\Address $target): void
    {
        $data = $source->getData();
        unset(
            $data['address_id'],
            $data['quote_id'],
            $data['entity_id'],
            $data['customer_address_id']
        );
        $target->addData($data);
        $target->setCustomerAddressId(null);
    }
}
