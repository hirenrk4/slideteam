<?php


namespace Tatva\Deleteaccount\Controller\Adminhtml\index;

class Subsciptions extends \Magento\Backend\App\Action
{

    protected $resultPageFactory = false;
    protected $_deletedcustomerbkp;

    public function __construct
    (
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Tatva\Deleteaccount\Model\DeletedcustomerbkpFactory $deletedcustomerbkp
    )
    {
        parent::__construct($context);
        $this->_deletedcustomerbkp = $deletedcustomerbkp;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $del_customer_id = $this->getRequest()->getParam('del_customer_id');

        $customer = $this->_deletedcustomerbkp->create();
        $customerData = $customer->getCollection()->getItemById($del_customer_id);
        $customerName = $customerData->getFirstname() . " " . $customerData->getLastname();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Subsciptions List of '.$customerName)));

        return $resultPage;
    }

}
