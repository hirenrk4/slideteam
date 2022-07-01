<?php
namespace Tatva\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\ResultFactory;

class SocialCustomerLogin implements ObserverInterface
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
        \Mageplaza\SocialLogin\Model\Social $apiObject,
        \Mageplaza\SocialLogin\Helper\Social $apiHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Magento\Framework\Controller\ResultFactory $resultFactory
        ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->sessionManager = $sessionManager;
        $this->subscription = $subscription;
        $this->_customerUrl = $customerUrl;
        $this->customerRepository = $customerRepository;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->apiObject = $apiObject;
        $this->apiHelper = $apiHelper;
        $this->_customerSession = $customerSession;
        $this->_redirect = $redirectInterface;
        $this->resultFactory = $resultFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $disable_multilogin = $this->_scopeConfig->getValue('customer/startup/customer_multilogin_disable');

        $type = $this->apiHelper->setType($observer->getRequest()->getParam('type'));
        $userProfile = $this->apiObject->getUserProfile($type);
        $websiteId = 1;
        $customer = $this->apiObject->getCustomerBySocial($userProfile->identifier, $type);
        if($customer->getId())
        {
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
            $customerObject = $this->customerRepository->getById($customer_id);
            $multilogin = 0 ;
            if($customerObject->getCustomAttribute('enable_multilogin'))
            {
                $multilogin = $customerObject->getCustomAttribute('enable_multilogin')->getValue();
            }

            if($disable_multilogin) 
            {

                if($loginTime > $logoutTime && $loginTime >= ($currentDate - $cookieTimeOut)  && $multilogin != 1 && empty($this->sessionManager->getSocialLogin()) && isset($customerType['customerType']) && $customerType['customerType'] == 'Active')
                {
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $previous_full_url = $this->_redirect->getRefererUrl();
                    $this->sessionManager->setUrl($previous_full_url);
                    
                    $this->sessionManager->setSocialLogin($customer_id);
                    $this->sessionManager->setUsername($customer->getEmail());
                    $this->sessionManager->setLoginType($type);
                    $this->sessionManager->setProductId($observer->getRequest()->getParam('productid'));
                    $beforeUrl = $this->sessionManager->getBeforeAuthUrl();

                    $url = $beforeUrl ? $beforeUrl : $this->_customerUrl->getLoginUrl();
                   
                    header("Location: ".$url);
                    exit;
                }
            
            }
        }
    }
}