<?php

namespace Tatva\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Tatva\Subscription\Model\Subscription;

class LoginObserver implements ObserverInterface{

	/**
     * [$_customerSession ]
     * @var [\Magento\Customer\Model\Session]
     */
    protected $_customerSession;

    protected $_customerRedirectionFlow;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Customer\Model\CustomerRedirectionFlow $customerRedirectionFlow
        ) {
        $this->_customerSession = $customerSession;
        $this->_customerRedirectionFlow = $customerRedirectionFlow;
    }

	/**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $Observer)
    {
    	$redirectUrlForCustomer = $this->_customerRedirectionFlow->getRedirectUrlForCustomer();
    	$this->_customerSession->setBeforeAuthUrl($redirectUrlForCustomer);
        $this->_customerSession->setAfterAuthUrl($redirectUrlForCustomer);
    }
}