<?php

namespace Tatva\CustomAttribute\Plugin;

class CheckoutCouponApply{

	protected $_couponCustomer;
	protected $customerSession;

	public function __construct(\Tatva\CustomAttribute\Model\CustomAttribute $couponCustomer,\Magento\Customer\Model\Session $customerSession)
	{
	    $this->_couponCustomer = $couponCustomer;
	    $this->customerSession = $customerSession;
	}

    public function beforeSet(\Magento\Quote\Model\CouponManagement $subject, $cartId, $couponCode)
    {
    	$customerid = $this->customerSession->getCustomer()->getId();
    	$collection = $this->_couponCustomer->getCollection()->addFieldToFilter("coupon_code",array($couponCode));
    	if($collection->getSize())
    	{
    		$collection = $this->_couponCustomer->getCollection()->addFieldToFilter("coupon_code",array($couponCode))->addFieldToFilter('customer_id',array($customerid));
    		if($collection->getSize())
    		{
    			return [$cartId, $couponCode];
    		}
    		else
    		{
    			return [$cartId, 'notValidCoupon'];
    		}
    	}
        else
        {
        	return [$cartId, $couponCode];
        }
    }
}