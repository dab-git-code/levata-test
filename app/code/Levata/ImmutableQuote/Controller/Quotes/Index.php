<?php
declare(strict_types=1);

namespace Levata\ImmutableQuote\Controller\Quotes;

use Levata\ImmutableQuote\Model\FeatureGate;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractAccount implements HttpGetActionInterface
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly FeatureGate $featureGate,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->featureGate->assertEnabled();
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }

        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->set(__('My Quotes'));
        return $page;
    }
}
