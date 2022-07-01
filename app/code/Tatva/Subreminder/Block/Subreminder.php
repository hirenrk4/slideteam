<?php

namespace Tatva\Subreminder\Block;

class Subreminder extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $_subscription;
    protected $_subscriptionProductsCollectionFactory;

    public function __construct
    (
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Tatva\Subscription\Model\Subscription $subscriptionModel,
        array $data = []
    )
    {
        $this->_customerSession = $customerSession;
        $this->_subscription = $subscriptionModel;
        $this->_subscriptionProductsCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    public function needToRemind()
    {
        if(!empty($this->_customerSession->getData('checkSubReminder')))
        {
            return $this->_customerSession->getData('checkSubReminder');
        }
    }

    public function getRenewSubUrl()
    {
        $product_url = $this->getBaseUrl()."checkout/cart/add/product/";
        $subproductid = '';
        $custom_design_param = '';
        $enterprise_param = '';
        $sub_period = '';
        
        $subscription = $this->_subscription->getCustomersCurrentSubscription();
        if($subscription)
        {
            $sub_from_date = $subscription->getFromDate();
            $sub_to_date = $subscription->getToDate();
            $sub_period = $subscription->getSubscriptionPeriod();
            $sub_period_t = strtotime($sub_to_date) - strtotime($sub_from_date); 
            $sub_period_days = round($sub_period_t /(60*60*24)); 
        }
        
        if(!is_null($sub_period))
        {
            $monthly_sub = array('1month','1month-inline','monthly with custom design','Monthly');
            $monthly_sub_days = array('28','29','30','31');
            $semi_annual_sub = array('3months','quarterly with custom design','6months','6months-inline','semi annual with custom design','semi-annual with custom design','Semi-annual');
            $semi_annual_sub_days = array('179','180','181');
            $annual_sub = array('12months','12months-inline','Annual','Annual subscription','Annual renewal');
            $annual_sub_days = array('365','366');
            $annual_w_cd = array('12months-custom-inline','Annual with custom design','Annual + Custom Design');
            $team_sub = array('4-user-enterprise-license','team-license-inline','Annual enterprise license renewal',' enterprise license renewal','4 user enterprise license','Annual Enterprise license','4 user enterprise license (User 4)','4 user enterprise plan','4 user site license');
            
            switch ($sub_period) 
            {
                case in_array($sub_period,$monthly_sub):
                $subproductid = $this->getSubscriptonProductId('1month');
                break;

                case in_array($sub_period,$semi_annual_sub):
                $subproductid = $this->getSubscriptonProductId('6months');
                break;
                
                case in_array($sub_period, $annual_sub):
                $subproductid = $this->getSubscriptonProductId('12months');
                break;

                case in_array($sub_period,$annual_w_cd):
                $subproductid = $this->getSubscriptonProductId('Annual with custom design');
                break;

                case in_array($sub_period,$team_sub):
                $subproductid = $this->getSubscriptonProductId('4-user-enterprise-license');
                break;

                default:
                switch ($sub_period_days) 
                {
                    case in_array($sub_period_days,$monthly_sub_days):
                    $subproductid = $this->getSubscriptonProductId('1month');
                    break;

                    case in_array($sub_period_days,$semi_annual_sub_days):
                    $subproductid = $this->getSubscriptonProductId('6months');
                    break;

                    case in_array($sub_period_days, $annual_sub_days):
                    $subproductid = $this->getSubscriptonProductId('12months');
                    break;

                    default:
                    $subproductid = $this->getSubscriptonProductId('12months');
                    break;
                }
                break;
            }

            $product_url = $product_url.$subproductid;
            
            return $product_url;
        }
    }

    protected function getSubscriptonProductId($sku)
    {
        $collection = $this->_subscription->getAllSubscriptionProductsCollection()->addAttributeToFilter('sku',array('eq' => $sku));
        
        $id = $collection->getAllIds();
        
        return $id[0];
    }

    public function getCustomerName()
    {
        return $this->_customerSession->getCustomer()->getName();
    }

    public function resetCheckSubReminder()
    {
        $this->_customerSession->setData('checkSubReminder',null);
    }
}
