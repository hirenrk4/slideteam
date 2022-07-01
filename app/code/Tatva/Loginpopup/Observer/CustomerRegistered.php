<?php
namespace Tatva\Loginpopup\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegistered implements ObserverInterface
{
    protected $_customerSession;

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession
    ) 
    {
        $this->_customerSession = $customerSession;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $myValue =  "Register";
        $this->_customerSession->setRegister($myValue);
        return $this;
    }
}