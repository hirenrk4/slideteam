<?php


namespace Tatva\Deleteaccount\Controller\Adminhtml\index;

class Downloads extends \Magento\Backend\App\Action
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
        $resultPage = $this->resultPageFactory->create();
        $customer_id = $this->getRequest()->getParam('customer_id');

        if($customer_id != 'x'){
            $customer = $this->_deletedcustomerbkp->create();
            $customerData = $customer->getCollection()->getItemById($customer_id);
            $customerName = $customerData->getFirstname() . " " . $customerData->getLastname();
            $resultPage->getConfig()->getTitle()->prepend((__('Downloads List of '.$customerName)));
        }else{
            $resultPage->getConfig()->getTitle()->prepend((__('Downloads List')));
        }

        return $resultPage;
    }

}
