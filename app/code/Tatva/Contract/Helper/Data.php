<?php

namespace Tatva\Contract\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected $customerSession;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Customer\Model\Session $customerSession) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function getUserName(){
        if(!$this->customerSession->isLoggedIn()){
            return '';
        }
        $customer = $this->customerSession->getCustomer();
        return trim($customer->getName());
    }
    
    public function getUserEmail(){
        if(!$this->customerSession->isLoggedIn()){
            return '';
        }
        $customer = $this->customerSession->getCustomer();
        return trim($customer->getEmail());
    }
    
}