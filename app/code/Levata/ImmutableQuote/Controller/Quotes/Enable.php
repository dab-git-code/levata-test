<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Controller\Quotes;

use Levata\ImmutableQuote\Api\QuoteImmutableManagementInterface;
use Levata\ImmutableQuote\Model\FeatureGate;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;

class Enable extends AbstractAccount implements HttpPostActionInterface
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        private readonly QuoteImmutableManagementInterface $quoteManagement,
        private readonly CustomerSession $customerSession,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly FeatureGate $featureGate,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create()->setPath('immutablequote/quotes/index');

        try {
            $this->featureGate->assertEnabled();
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page.'));
            return $redirect;
        }

        $quoteId = (int) $this->getRequest()->getParam('quote_id');
        $customerId = (int) $this->customerSession->getCustomerId();

        try {
            $cart = $this->quoteManagement->getQuote($quoteId);
            if ((int) $cart->getCustomerId() !== $customerId) {
                throw new LocalizedException(__('You are not allowed to activate this quote.'));
            }
            $this->quoteManagement->enableQuote($quoteId);
            $this->messageManager->addSuccessMessage(__('Quote #%1 is now your active cart.', $quoteId));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Throwable) {
            $this->messageManager->addErrorMessage(__('Unable to activate quote. Please try again.'));
        }

        return $redirect;
    }
}
