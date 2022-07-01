<?php
namespace Tatva\Customerdashboard\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CustomerActivityObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Tatva\Customerdashboard\Model\CustomerdashboardFactory $customerDashboardFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_request = $request;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_customerDashboardFactory = $customerDashboardFactory;
        $this->customerFactory= $customerFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_countryFactory = $countryFactory;
        $this->httpContext = $httpContext;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->getCustomerIsLoggedIn())
        {
            $customerId = $this->getCustomerId();
            $customerEmail = $this->getCustomerEmail();

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
            
                $pageUri = $this->_request->getRequestUri();
                $date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
                $currentDate = $this->converToTz($date,'America/Los_Angeles','GMT');
                
                if (!preg_match('/checkout|pricing/i', $pageUri))
                {
                    $model = $this->_customerDashboardFactory->create();
                    $model->setData('customer_id',$customerId);
                    $model->setData('customer_email',$customerEmail);
                    $model->setData('page_uri',$pageUri);
                    $model->setData('created_time',$currentDate);
                    $model->save();
                }
                
            }
            return $this;
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

    public function getCustomerIsLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }

    public function getCustomerEmail()
    {
        return $this->httpContext->getValue('customer_email');
    }
}