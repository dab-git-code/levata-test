<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\CartRepository;

use Levata\ImmutableQuote\Model\Service\CartExtensionHydrator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class HydrateExtensionAttributes
{
    public function __construct(
        private readonly CartExtensionHydrator $cartExtensionHydrator,
    ) {
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $result): CartInterface
    {
        $this->cartExtensionHydrator->hydrate($result);
        return $result;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     */
    public function afterGetActive(CartRepositoryInterface $subject, CartInterface $result): CartInterface
    {
        $this->cartExtensionHydrator->hydrate($result);
        return $result;
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     */
    public function afterGetForCustomer(CartRepositoryInterface $subject, CartInterface $result): CartInterface
    {
        $this->cartExtensionHydrator->hydrate($result);
        return $result;
    }
}
