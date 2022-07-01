<?php
/**
* Copyright � 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Tatva\Customer\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriberFactory;


class CustomerRegisterafter implements ObserverInterface
{
     /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    protected $_redirect;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $currentGMTDate;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        SubscriberFactory $subscriberFactory,
        \Tatva\Subscription\Model\SubscriptionFactory $subscriptionRepostry,
        \Tatva\Subscription\Model\SubscriptionInvitationFactory  $subscriptioninvitation,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->_messageManager = $messageManager;
        $this->subscriberFactory = $subscriberFactory;
        $this->__subscriptionRepostry = $subscriptionRepostry;
        $this->_subscriptioninvitation = $subscriptioninvitation;
        $this->SubscriptionHelper = $SubscriptionHelper;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_request = $request;
        $this->curl = $curl;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->_redirect = $redirectInterface;
        $this->customerFactory = $customerFactory;
        $this->_teamplanModel = $teamplanModel;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->currentGMTDate = $dateTimeFactory;
        $this->_remoteAddress = $remoteAddress;
    }

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $telephone = $this->_request->getParam('contact_number');
        $isd_code = $this->_request->getParam('isd-code');
        $country_code = $this->_request->getParam('country_id');
        $contact_number = '+'.$isd_code.' '.$telephone;
        
        $customer = $observer->getEvent()->getCustomer();
        $customerData = $this->customerFactory->create()->load($customer->getId())->getDataModel();
        
        /*Subscriptioninvitation to set child customer subscription*/

        $invitations = $this->SubscriptionHelper->getSubscriptionInvitation($customer->getEmail());
        $invitations_email = null;
        $parent_id = null;
        foreach ($invitations as $data) {
            
            $invitations_email =  $data->getCustomerEmail();
            $parent_id =  $data->getParentCustomer();
        }
        if ($invitations_email == $customer->getEmail()) {

            $remaining = $this->SubscriptionHelper->getSubscriptionRemaining($parent_id);
            $sub_plan =  $this->SubscriptionHelper->getSubscriptionPlan($parent_id);
            $subscription_plan = $sub_plan->getSubscriptionPeriod();
            
            list($userlimit,$remaining_limit,$no_of_users) = $this->_teamplanModel->getPlanLimit($subscription_plan,$remaining,$parent_id);
            
            if ($remaining_limit >= 0){
                $Subscriptionhistory = $this->SubscriptionHelper->getSubscriptionHistory($parent_id);
                
                $post = array();
                foreach ($Subscriptionhistory as $post) {
                  /*invitation to save*/
                    $customerId = (int)$customer->getId();
                    
                    $model = $this->__subscriptionRepostry->create();
                    $model->addData([
                        "customer_id" => $customerId,
                        "increment_id" => $post->getIncrementId(),
                        "subscription_period" => $post->getSubscriptionPeriod(),
                        "from_date" => $post->getFromDate(),
                        "to_date" => $post->getToDate(),
                        "admin_start_date" => $post->getAdminStartDate(),
                        "admin_end_date" => $post->getAdminEndDate(),
                        "renew_date" => $post->getRenewDate(),
                        "reminder_success" => $post->getReminderSuccess(),
                        "status_success" => $post->getStatusSuccess(),
                        "user_status_unsubscribe" => $post->getUserStatusUnsubscribe(),
                        "user_status_unsubscribe_date" => $post->getUserStatusUnsubscribeDate(),
                        "paypal_id" => $post->getPaypalId(),
                        "txn_id" => $post->getTxnId(),
                        "two_checkout_message_id" => $post->getTwoCheckoutMessageId(),
                        "download_limit" => $post->getDownloadLimit(),
                        "admin_modified" => 1,
                        // "reminder_purchase" => $post->getReminderPurchase(),
                        "subscription_start_date" => $post->getSubscriptionStartDate(),
                        "test_column" => "invitation-customer",
                        "subscription_title" => $post->getSubscriptionTitle(),
                        "subscription_detail" => $post->getSubscriptionDetail(),
                        "parent_customer_id" => $parent_id
                    ]);

                  $model->save();
                  
                  $invitationmodel = $this->_subscriptioninvitation->create()->load($customer->getEmail(), 'customer_email');
                  
                  $invitationmodel->addData([
                      "child_customer_id" => $customerId
                      ]);
                  $saveData = $invitationmodel->save();
                  
                }
            }
            /*invitation to save end*/
        }
        /*Subscriptioninvitation to set child customer subscription*/
        if(isset($telephone) && !empty($telephone))
        {
            $customerData->setCustomAttribute('contact_number', $contact_number);            
        }
        $isValidCountryCode = 0;
        if(isset($country_code) && !empty($country_code)){
            $customerData->setCustomAttribute('customer_country_code', $country_code);
            $isValidCountryCode = 1;
        }

        //Emarsys integration start//
        $field4 =  $this->EmarsysHelper->isApiEnabled();

        if($field4)
        {
            $subscriberFactory=$this->subscriberFactory->create();
            $newsletter_subscribe=$subscriberFactory->loadByCustomerId($customer->getId())->isSubscribed();

            $emarsys_flag = 0;
            $email = explode(',',$this->_scopeConfig->getValue('button/emarsys_exclued_user/email_like',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cust_mail = $customer->getEmail();
            foreach ($email as $email_id) {
            if(stripos($cust_mail,$email_id) !== false )        
                {       
                   $emarsys_flag = 1;       
                }
            }

            $contact = array(
                            "1"=>$customer->getFirstname(),
                            "2"=>$customer->getLastname(),
                            "3"=>$customer->getEmail(),
                            "485"=>$customer->getId(),
                            "490"=>"2"
                          );              

            if(!empty($newsletter_subscribe))
            {
                $contact["31"] = true;
            }
            else
            {
                $contact["31"] = false;
            }   

            if ($emarsys_flag == 0) {
                try {
                    $encode = json_encode($contact);
                    $apiHelper = $this->emarsysApiHelper;

                    $result = $apiHelper->send('POST', 'contact/getdata', '{"keyId": "485","keyValues":["'.$customer->getId().'"],"fields": ["3"]}');

                    if(count($result->data->errors))
                    {
                        $apiHelper->send('POST', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    }
                    else
                    {
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    }        

                } catch (\Exception $e) {
                    echo $e->getMessage();
                }    
            }
            
        }
        //Zoho CRM integration start//
        $accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $ipAddress = $this->getIpAddress();
        if(!$isValidCountryCode){
            $url = "http://api.ipstack.com/".$ipAddress."?access_key=".$accesskey."&format=1";
            $locationjson = json_decode(file_get_contents($url));
            if(!empty($locationjson->country_code)){
                $customerData->setCustomAttribute('ipstack_customer_country_code', $locationjson->country_code);
            }
        }else{
            if(isset($country_code) && !empty($country_code)){
                $customerData->setCustomAttribute('ipstack_customer_country_code', $country_code);
            }
        }
        $isSendZoho=0;
        if($this->zohoCRMHelper->isEnabled()){
            if($isValidCountryCode && $this->zohoCRMHelper->isAllowCountry($isd_code,$country_code)){
                $isSendZoho=1;
            }

            if(!$isValidCountryCode){
                if(!empty($locationjson->country_code) && $this->zohoCRMHelper->isAllowIpCountry($locationjson->country_code)){
                    $isSendZoho=1;
                }
            }
        }
        if($isSendZoho == 1){
            try {
                $metadata = $this->cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setDuration(86400)
                        ->setPath($this->sessionManager->getCookiePath())
                        ->setDomain($this->sessionManager->getCookieDomain());
                $currentDate = $this->currentGMTDate->create()->gmtDate('Y-m-d H:i:s');
                $this->cookieManager->setPublicCookie(
                        "zoho_customer_data",
                        $currentDate,
                        $metadata
                    );                        
                $url = $this->_redirect->getRefererUrl();
                $contactModule =array(
                    "First_Name"=>$customer->getFirstname(),
                    "Last_Name"=>$customer->getLastname(),
                    "Email"=>$customer->getEmail(),
                    "Customer_ID"=>$customer->getId(),
                    "Customer_Type" => "Free Registeration",
                    "Subscription_Type" => "Free Registeration",
                    "Website"=>"https://www.slideteam.net/",
                    "Priority"=>"1",
                    "Comment"=> "Create customer",
                    "URL"=>$url,
                    "Ip_Address"=>$ipAddress
                );
                if(isset($telephone) && !empty($telephone)){
                    $contactModule["Phone"] = $telephone;
                }
                if(isset($country_code) && !empty($country_code)){
                    $contactModule["Country"] = $this->zohoCRMHelper->getCountryname($country_code);
                }
                $this->zohoCRMHelper->createCustomer($contactModule);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        //Zoho CRM integration end//
        $this->_customerRepositoryInterface->save($customerData);
    }

    protected function getIpAddress()
    {
        $ip_address =  $this->_remoteAddress->getRemoteAddress();
        return $ip_address;
    }
}