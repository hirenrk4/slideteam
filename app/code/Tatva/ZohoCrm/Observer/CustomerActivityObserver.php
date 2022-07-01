<?php
namespace Tatva\ZohoCrm\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CustomerActivityObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Tatva\ZohoCrm\Model\ZohoCustomerTrackingFactory
     */
    private $zohoCustomerTrackingFactory;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Tatva\ZohoCrm\Model\ZohoCustomerTrackingFactory $zohoCustomerTrackingFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->_request = $request;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->zohoCustomerTrackingFactory = $zohoCustomerTrackingFactory;
        $this->httpContext = $httpContext;
        $this->cookieManager = $cookieManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->getCustomerIsLoggedIn() && $this->cookieManager->getCookie('zoho_customer_data'))
        {
            $customerId = $this->getCustomerId();
            
            $pageUri = $this->_request->getRequestUri();
            $date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
            $currentDate = $this->converToTz($date,'America/Los_Angeles','GMT');
            
            $model = $this->zohoCustomerTrackingFactory->create();
            $model->setData('customer_id',$customerId);
            $model->setData('page_uri',$pageUri);
            $model->setData('created_time',$currentDate);
            $model->save();
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

    public function getCustomerIsLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }
}