<?php


namespace Tatva\Deleteaccount\Controller\Adminhtml\index;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Tatva_Deleteaccount::index';
    
    protected $resultPageFactory = false;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Deleted Customers')));

        return $resultPage;
    }

}
