<?php

namespace Tatva\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLoginafter implements ObserverInterface
{
    protected $_customerSession;

    public function __construct(
        \Magento\Customer\Model\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Tatva\Subscription\Model\Subscription $subscription,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory $visitorCollectionFactory,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        \Tatva\Customer\Model\Killedsesssions $killedSessions,
        \Tatva\Customer\Model\KilledsesssionsFactory  $killedSessionsFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress ,
        \Tatva\Catalog\Model\Productdownloadhistorylog $productDownloadLog
    ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->sessionManager = $sessionManager;
        $this->subscription = $subscription;
        $this->_customerUrl = $customerUrl;
        $this->customerRepository = $customerRepository;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->visitorCollectionFactory = $visitorCollectionFactory;
        $this->saveHandler = $saveHandler;
        $this->_customerSession = $customerSession;
        $this->request = $request;
        $this->killedSessions = $killedSessions;
        $this->killedSessionsFactory = $killedSessionsFactory;
        $this->_remoteAddress = $remoteAddress;
        $this->productDownloadLog = $productDownloadLog;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $disable_multilogin = $this->_scopeConfig->getValue('customer/startup/customer_multilogin_disable');
        $customer = $this->_customerSession->getCustomer();
        $customer_id = $customer->getId();
        $customerType = $this->subscription->getCustomerType($customer_id);

        $subscriptionPeriod = '';
        if (isset($customerType['customerType']) && $customerType['customerType'] == 'Active') {
            $customerSubscription = $this->subscription->getCustomersCurrentSubscription($customer_id);
            $subscriptionPeriod = $customerSubscription->getSubscriptionPeriod();
        }
        $log = $this->logger->get($customer_id);
        $currentDate = strtotime($this->_dateTimeFactory->create()->gmtDate('Y-m-d H:i:s'));
        $loginTime = strtotime($log->getLastLoginAt());
        $logoutTime = strtotime($log->getLastLogoutAt());
        $cookieTimeOut = $this->_scopeConfig->getValue('web/cookie/cookie_lifetime');
        $customerObject = $this->customerRepository->getById($customer_id);
        $multilogin = 0;
        if ($customerObject->getCustomAttribute('enable_multilogin')) {
            $multilogin = $customerObject->getCustomAttribute('enable_multilogin')->getValue();
        }
        if ($disable_multilogin) {
            if (!isset($params['key'])) {
                if ($loginTime > $logoutTime && $loginTime >= ($currentDate - $cookieTimeOut) && $multilogin != 1 && isset($customerType['customerType']) && $customerType['customerType'] == 'Active') {
                    $accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $ip = $this->_remoteAddress->getRemoteAddress();

                    $url = "http://api.ipstack.com/" . $ip . "?access_key=" . $accesskey . "&format=1";
                    $locationjson = json_decode(file_get_contents($url));

                    $city = NULL;
                    $regionName = NULL;
                    $country = NULL;

                    if (!empty($locationjson->city)) {
                        $city = $locationjson->city. " , ";
                    }
                    if (!empty($locationjson->city)) {
                        $regionName = $locationjson->region_name. " , ";
                    }
                    if (!empty($locationjson->city)) {
                        $country = $locationjson->country_name;
                    }

                    $location = $city  . $regionName  . $country;
                    if($location == '' || $location == null) {
                        $location = 'NULL';
                    }

                    $timestamp = $this->_dateTimeFactory->create()->gmtDate('Y-m-d H:i:s');

                    $this->destroyCustomerSessions($customer->getId(), $location);
                    $this->sessionManager->unsLogin();
                    $this->sessionManager->unsSocialLogin();

                    if ($this->_scopeConfig->getValue('button/destroyemail/destroyemaildisable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        $mail = new \Zend_Mail();
                        $message = "Customer Detail whose session is destroyed.";
                        $message .= "<br/>Customer Id :: " . $customer->getId();
                        $message .= "<br/>Customer Email :: " . $customer->getEmail() ;
                        $message .= "<br/>Location :: " . $location ;
                        $message .= "<br/>Timestamp :: " . $timestamp ;
                        $mail->setFrom("support@slideteam.net", 'Slideteam Support');
                        $mail->setSubject('Customer Session Destroyed');
                        $mail->setBodyHtml($message);
                        $to_email = $this->_scopeConfig->getValue('button/destroyemail/toemail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                        $cc_email = explode(',', $this->_scopeConfig->getValue('button/destroyemail/ccemail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                        $send = 0;
                        if (!empty($to_email)) {
                            $mail->addTo($to_email);
                            $send = 1;
                        }
                        if (!empty($cc_email)) {
                            $mail->addCc($cc_email);
                        }
                        try {
                            if ($send) :
                                $mail->send();
                            endif;
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }

                    return $this;
                }
            }
        }
    }
    public function destroyCustomerSessions($customerId, $location)
    {
        $sessionLifetime = $this->_scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $currentDate = strtotime($this->_dateTimeFactory->create()->gmtDate('Y-m-d H:i:s'));
        $activeSessionsTime = date('Y-m-d H:i:s', ($currentDate - $sessionLifetime));
        $visitorCollection = $this->visitorCollectionFactory->create();
        $visitorCollection->addFieldToFilter('customer_id', $customerId);
        $visitorCollection->addFieldToFilter('session_id', ['neq' => $this->sessionManager->getSessionId()]);

        foreach ($visitorCollection->getItems() as $visitor) {
            $sessionId = $visitor->getSessionId();
            $this->sessionManager->start();
            $this->saveHandler->destroy($sessionId);
            $visitor->delete();
            $this->sessionManager->writeClose();
        }

        $customerSubscription = $this->subscription->getCustomersCurrentSubscription($customerId);
        $subscriptionType  = $customerSubscription->getSubscriptionPeriod();

        $timestamp = $this->_dateTimeFactory->create()->gmtDate('Y-m-d H:i:s');
        $email =  $this->_customerSession->getCustomer()->getEmail();
        
        $from = strtotime('-7 day', strtotime($timestamp));
        $from = date('Y-m-d h:i:s', $from); 
        $to = $timestamp ;
        $ip = $this->_remoteAddress->getRemoteAddress();
        $downloadHostory = $this->productDownloadLog->getCollection()->addFieldToFilter('customer_id', ['eq' => $customerId])->addFieldToFilter('download_date', array('from'=>$from, 'to'=>$to));
        $noOfDownloads = $downloadHostory->getSize();

        $Collection = $this->killedSessions->getCollection()->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($Collection->getSize() == 0) {
            $totalKilled = 1;
            $modelFactory = $this->killedSessionsFactory->create();
            $data = [
                'customer_id' => $customerId,
                'email' =>  $email,
                'total_killed' => $totalKilled,
                'timestamp' => '1) '. $timestamp.' ',
                'location' => '1) '.$location.' ',
                'subscription_type' => $subscriptionType ,
                'no_of_downloads' => $noOfDownloads,
                'ip_address' => $ip
            ];
            $modelFactory->setData($data);
            $modelFactory->save();
        } else {
            $lastKilled = $Collection->getLastItem();
            $totalKilled = $lastKilled->getTotalKilled() + 1;

            $timestampUpdate = $lastKilled->getTimestamp().'<br/>'.$totalKilled.") ".$timestamp." ";
            $locationUpdate = $lastKilled->getLocation().'<br/>'.$totalKilled.") ".$location." ";
            $ipUpdate = $lastKilled->getIpAddress().'<br/>'.$totalKilled.") ".$ip." ";
            $lastKilled->setEmail($email);
            $lastKilled->setTotalKilled($totalKilled);
            $lastKilled->setTimestamp($timestampUpdate);
            $lastKilled->setLocation($locationUpdate);
            $lastKilled->setSubscriptionType($subscriptionType);
            $lastKilled->setNoOfDownloads($noOfDownloads);
            $lastKilled->setIpAddress($ipUpdate);
            $lastKilled->save();
        }
    }
}
