<?php

namespace Tatva\PaidCustomerPopup\Controller\Adminhtml\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    const ADMIN_RESOURCE = 'Tatva_PaidCustomerPopup::index';
    
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ){

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Tatva_PaidCustomerPopup::viewData');
        $resultPage->getConfig()->getTitle()->prepend(__('Paid Customers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Paid Customers'), __('Paid Customers'));
        $resultPage->addBreadcrumb(__('Manage Paid Customers'), __('Manage Paid Customers'));

        return $resultPage;
    }

}
