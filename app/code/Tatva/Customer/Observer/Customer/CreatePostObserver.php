<?php

namespace Tatva\Customer\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

class CreatePostObserver implements ObserverInterface{

	/**
	 * [$_customerSession ]
	 * @var [\Magento\Customer\Model\Session]
	 */
	protected $_customerSession;
	protected $_coreSession;    

	public function __construct(		
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
	)
	{		    
        $this->_customerSession = $customerSession;
        $this->_coreSession = $coreSession;
	}

	/**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $Observer)
    {
    	$this->_coreSession->start();
    	$this->_coreSession->setCustomerCreate(true);
    }
}