<?php
namespace Tatva\Deleteaccount\Controller\Index;

class Finalizedelete extends \Magento\Framework\App\Action\Action
{
    protected $_deleteaccount;
    protected $_messageManager;

    public function __construct
    (
    	\Tatva\Deleteaccount\Model\Deleteaccount $deleteaccount,
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->_deleteaccount = $deleteaccount;
        $this->_messageManager = $messageManager;
    }

    public function execute()
    {	
        $params = $this->getRequest()->getParams();        
        $flag = $this->_deleteaccount->deleteCustomerFromDb($params);
        if($flag)
        {
            $this->_messageManager->addSuccess('Your account has been deleted successfully.');
            $this->_redirect("customer/account/login");
        }
        else
        {
            $this->_messageManager->addError('We are unable to delete your account right now , please try later.');
            $this->_redirect("customer/account");
        }
    }
}