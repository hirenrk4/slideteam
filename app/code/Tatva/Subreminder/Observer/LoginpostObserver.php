<?php

namespace Tatva\Subreminder\Observer;

use Magento\Framework\Event\ObserverInterface;

class LoginpostObserver implements ObserverInterface
{
	protected $_subscription;
	protected $_customerSession;
	protected $_dateAccTimezone;
	protected $_dateFactory;
	
	public function __construct
	(
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\DateTime\Timezone $dateAccTimezone,
		\Tatva\Subscription\Model\Subscription $subscription,
		\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory
	)
	{
		$this->_dateAccTimezone = $dateAccTimezone;       
		$this->_customerSession = $customerSession;
		$this->_subscription = $subscription;
		$this->_dateFactory = $dateTimeDateTimeFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $Observer)
	{	
		$flag = false;
		if($this->_customerSession->isLoggedIn())
		{
			if($this->_subscription->getCustomersCurrentSubscription())
			{
				//$current_date = $this->_dateAccTimezone->date()->format('Y-m-d');
				$current_date = $this->_dateFactory->create()->gmtDate('Y-m-d');
				$sub_last_date = $this->_subscription->getCustomersCurrentSubscription()->getData('to_date');
				$isPeriodExpired = strtotime($current_date) > strtotime($sub_last_date);
				$isUnsubscribbed = $this->_subscription->getCustomersCurrentSubscription()->getData('status_success') == "Unsubscribed";
				$downloadLimit = $this->_subscription->getCustomersCurrentSubscription()->getData('download_limit');
				$adminModified = $this->_subscription->getCustomersCurrentSubscription()->getData('admin_modified');
				$adminUnsubscribbed = ($downloadLimit == '0' && $adminModified == '1');
				$remind_condition = ($isPeriodExpired && $isUnsubscribbed) || $adminUnsubscribbed;

				if($remind_condition)
				{        
					$flag = true;
				}
			}	

			$this->_customerSession->setData('checkSubReminder',$flag);
		}
	}
}