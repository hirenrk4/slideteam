<?php
namespace Tatva\Customerdashboard\Controller\Index;


class CustomerActivity extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Tatva\Customerdashboard\Model\CustomerdashboardFactory $customerDashboardFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository
    ) {
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_customerDashboardFactory = $customerDashboardFactory;
        $this->customerFactory= $customerFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_countryFactory = $countryFactory;
        $this->_customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productrepository = $productrepository;
        parent::__construct($context);
    }

    public function execute()
    {
        if($this->_customerSession->isLoggedIn())
        {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $customerEmail = $this->_customerSession->getCustomer()->getEmail();
            $customerCollection = $this->customerFactory->create()->getCollection();
            $customerCollection->addAttributeToSelect("*")->addAttributeToFilter('entity_id',$customerId)->addAttributeToFilter("contact_number", array("neq" => null));

            $countryCode = $this->getTopDestinations();
            $ORCondition = array();
            if(!empty($countryCode)) 
            {
                foreach ($countryCode as $value) {
                $country = $this->_countryFactory->create()->loadByCode($value);
                $isd_code = '+'.$country->getIsdCode();
                    array_push($ORCondition,array('like' => $isd_code.' %'));
                }
                $customerCollection->addAttributeToFilter('contact_number',$ORCondition);    
            }

            if(!empty($customerCollection->getData()))
            {
                $params = $this->getRequest()->getParams();
                $event = $params['event'];
                $pageUri = $params['pageUri'];
                
                $date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
                $currentDate = $this->converToTz($date,'America/Los_Angeles','GMT');
                
                if (!preg_match('/checkout|pricing/i', $pageUri))
                {
                    $model = $this->_customerDashboardFactory->create();
                    $model->setData('customer_id',$customerId);
                    $model->setData('page_uri',$pageUri);
                    $model->setData('event',$event);
                    $model->setData('created_time',$currentDate);
                    $model->save();
                }
                
                $resultJson = $this->resultJsonFactory->create();
                $response = ['message' => 'success'];
                $resultJson->setData($response);
                return $resultJson;
            }
        } 
    }
    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d h:i:s a');
        return $dateTime;
    }

    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'zohocrm/general/countries',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

}