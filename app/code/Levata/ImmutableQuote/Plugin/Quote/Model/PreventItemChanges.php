<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Plugin\Quote\Model;

use Levata\ImmutableQuote\Api\Guard\QuoteImmutabilityGuardInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;

/**
 * Blocks item mutations on Magento Quote model (covers storefront cart controllers).
 */
class PreventItemChanges
{
    public function __construct(
        private readonly QuoteImmutabilityGuardInterface $guard,
    ) {
    }

    /**
     * @param Quote $subject
     * @param Product $product
     * @param float|DataObject|null $request
     * @param string|null $processMode
     * @return void
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ): void {
        $this->assertMutableQuote($subject, 'add_product');
    }

    /**
     * @param Quote $subject
     * @param int $itemId
     * @return void
     */
    public function beforeRemoveItem(Quote $subject, $itemId): void
    {
        $this->assertMutableQuote($subject, 'remove_item');
    }

    /**
     * @param Quote $subject
     * @return void
     */
    public function beforeRemoveAllItems(Quote $subject): void
    {
        $this->assertMutableQuote($subject, 'remove_all_items');
    }

    /**
     * @param Quote $subject
     * @param int $itemId
     * @param DataObject $buyRequest
     * @param null|array|\Magento\Framework\DataObject $params
     * @return void
     */
    public function beforeUpdateItem(Quote $subject, $itemId, $buyRequest, $params = null): void
    {
        $this->assertMutableQuote($subject, 'update_item');
    }

    private function assertMutableQuote(Quote $quote, string $action): void
    {
        $quoteId = (int) $quote->getId();
        if ($quoteId > 0) {
            $this->guard->assertMutable($quoteId, $action);
        }
    }
}
