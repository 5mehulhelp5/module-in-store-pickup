<?php

declare(strict_types=1);

namespace ETechFlow\InStorePickup\Controller\Adminhtml\Holiday;

use ETechFlow\InStorePickup\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'ETechFlow_InStorePickup::holidays';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        if (!$this->licenseValidator->isValid()) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('etechflow_isp/license/gate');
        }
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ETechFlow_InStorePickup::holidays');
        $resultPage->getConfig()->getTitle()->prepend(__('In-Store Pickup — Holidays'));
        return $resultPage;
    }
}