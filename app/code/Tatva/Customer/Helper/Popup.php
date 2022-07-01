<?php
namespace Tatva\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Popup extends AbstractHelper
{
    protected $_customerSession;
    
    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
    	\Magento\Customer\Model\Session $customerSession,
    	\Tatva\Subscription\Model\Subscription $subscriptionmodel
    ) {
        $this->customerSession = $customerSession;
    	$this->subscriptionmodel = $subscriptionmodel;
        parent::__construct($context);
    }

    public function customerType() {
		$flag = false;
		if(!($this->customerSession->isLoggedIn()))
		{
			$flag = true;
		} elseif ($this->customerSession->isLoggedIn()) {
			$customer_id = $this->customerSession->getCustomerId();
			$customerType = $this->subscriptionmodel->getCustomerType($customer_id);
			if($customerType == 'Free' || $customerType['customerType'] != 'Active')
	        {
	        	$flag = true;
	        }
		}
        return $flag;
	}
    
}