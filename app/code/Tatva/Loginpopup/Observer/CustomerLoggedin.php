<?php
namespace Tatva\Loginpopup\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLoggedin implements ObserverInterface
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
        $myValue =  "Currently Loggedin";
        $this->_customerSession->setCurrentLogin($myValue);
        return $this;
    }
}