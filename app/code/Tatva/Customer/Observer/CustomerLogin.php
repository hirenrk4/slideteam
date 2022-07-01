<?php
namespace Tatva\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{

    public function __construct(
        \Magento\Customer\Model\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Tatva\Subscription\Model\Subscription $subscription,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->sessionManager = $sessionManager;
        $this->subscription = $subscription;
        $this->_customerUrl = $customerUrl;
        $this->customerRepository = $customerRepository;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->customer = $customer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $disable_multilogin = $this->_scopeConfig->getValue('customer/startup/customer_multilogin_disable');
        $controller = $observer->getControllerAction();
        $loginParams = $controller->getRequest()->getPost('login');
        $login = (is_array($loginParams) && array_key_exists('username', $loginParams))
        ? $loginParams['username']
        : null;
        $websiteId = 1;
        $customer = $this->customer;
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($loginParams['username']);
        if ($customer->getId()) {
            $customer_id = $customer->getId();
            $customerType = $this->subscription->getCustomerType($customer_id);
            $subscriptionPeriod = '';
            if(isset($customerType['customerType']) && $customerType['customerType'] == 'Active')
            {
                $customerSubscription = $this->subscription->getCustomersCurrentSubscription($customer_id);
                $subscriptionPeriod = $customerSubscription->getSubscriptionPeriod();
            }
            $log = $this->logger->get($customer_id);
            $currentDate = strtotime($this->_dateTimeFactory->create()->gmtDate('Y-m-d H:i:s'));
            $loginTime = strtotime($log->getLastLoginAt());
            $logoutTime = strtotime($log->getLastLogoutAt());
            $cookieTimeOut = $this->_scopeConfig->getValue('web/cookie/cookie_lifetime');
            $multilogin = 0 ;
            $customerObject = $this->customerRepository->getById($customer_id);
            if($customerObject->getCustomAttribute('enable_multilogin'))
            {
                $multilogin = $customerObject->getCustomAttribute('enable_multilogin')->getValue();
            }
            if($disable_multilogin) 
            {
                if($loginTime > $logoutTime && $loginTime >= ($currentDate - $cookieTimeOut) && $multilogin != 1  && isset($customerType['customerType']) &&  $customerType['customerType'] == 'Active')
                {
                    $this->sessionManager->setLogin($loginParams);
                    $this->sessionManager->setUsername($loginParams['username']);
                    $this->sessionManager->setPassword($loginParams['password']);
                    $beforeUrl = $this->sessionManager->getBeforeAuthUrl();
                    $url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();

                    header("Location: ".$url);
                    exit;
                }
            }
        } else{
            return false;
        }
    }
}