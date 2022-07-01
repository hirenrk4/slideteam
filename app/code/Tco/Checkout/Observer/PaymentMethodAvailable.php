<?php

namespace Tco\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;


class PaymentMethodAvailable implements ObserverInterface
{
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $methodCode = $observer->getEvent()->getMethodInstance()->getCode();
        if($methodCode=="tco_checkout")
        {
        	$checkResult = $observer->getEvent()->getResult(); 
            $checkResult->setData('is_available', false);
        }
    }
}