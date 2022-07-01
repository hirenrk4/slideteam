<?php
namespace Tatva\Theme\Block\Html;

use Magento\Framework\View\Element\Template\Context;

class SpecialCoupon extends \Magento\Framework\View\Element\Template
{

	protected $salesruleModel;
	protected $subscriptionModel;

     /**
     * @param Context $context
     * @param array $data
     */
    public function __construct
    (
    	Context $context,
    	\Magento\SalesRule\Model\Rule $salesruleModel, 
    	\Tatva\Subscription\Model\Subscription $subscriptionModel,
    	array $data = []
    )
    {
    	$this->salesruleModel = $salesruleModel;
    	$this->subscriptionModel = $subscriptionModel;
    	parent::__construct($context, $data);
    }

    public function getSpecialCouponCode() {
    	$special_coupons = $this->salesruleModel->getCollection()
    						->addFieldToFilter("is_active","1")
    						->addFieldToFilter("name","Special Coupon Code");

    	$coupon_code = null;

    	if($special_coupons->getSize() == "1") {
    		foreach ($special_coupons as $special_coupon) {
				$coupon_code = $special_coupon; 
			}
    	}

    	return $coupon_code;
    }

    public function getCustomerData($customer_id){
    	return $this->subscriptionModel->getCustomerType($customer_id);
    }
            
}