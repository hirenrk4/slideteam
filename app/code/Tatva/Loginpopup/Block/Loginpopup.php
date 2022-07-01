<?php

namespace Tatva\Loginpopup\Block;

class Loginpopup extends \Magento\Framework\View\Element\Template
{
    protected $_customer;
    protected $_customerSession;
    protected $subscriptionModel;
    protected $customeradditionalModel;

    public function __construct
    (
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Subscription\Model\Subscription $subscriptionModel,
        \Tatva\Loginpopup\Model\CustomerAdditionlData $customeradditionalModel,
        array $data = [])
    {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer->load($this->_customerSession->getCustomer()->getId());
        $this->subscriptionModel = $subscriptionModel;
        $this->customeradditionalModel = $customeradditionalModel;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function check_user($customer_id){
        $data = $this->customeradditionalModel->getCollection()
            ->addFieldToFilter('customer_id',$customer_id);
        return $data;
    }
    public function check_user_type($customer_id) {
        $customer_type_flag = false;
        $customer_type_array = $this->subscriptionModel->getCustomerType($customer_id);

        if(is_array($customer_type_array)) {
            $customerSubscription = $customer_type_array['customerType'];
        }
        else {
            $customerSubscription = $customer_type_array;
        }

        if($customerSubscription == "Active" || $customerSubscription == "Expired"){
            $customer_type_flag = true;
        }
        
        return $customer_type_flag;
    }
}
