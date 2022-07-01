<?php

namespace Tatva\RecurringOrders\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{

    //const ADMIN_RESOURCE = 'Tatva_Formbuild::view';

    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Recurring Orders')));
        return $resultPage;
    }
}

