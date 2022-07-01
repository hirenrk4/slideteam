<?php
namespace Tatva\Contacts\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $_customerSession;
    
    public function __construct(\Magento\Framework\App\Helper\Context $context,
    \Magento\Customer\Model\Session $customerSession) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function getUserName() {
        if(!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        $customer = $this->_customerSession->getCustomer();
        return trim($customer->getName());
    }
    
    public function getUserEmail() {
        if(!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        $customer = $this->_customerSession->getCustomer();
        return trim($customer->getEmail());
    }
    
}