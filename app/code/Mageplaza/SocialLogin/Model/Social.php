<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SocialLogin
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SocialLogin\Model;

use Exception;
use Hybrid_Auth;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Social
 *
 * @package Mageplaza\SocialLogin\Model
 */
class Social extends AbstractModel
{
    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @type \Mageplaza\SocialLogin\Helper\Social
     */
    protected $apiHelper;

    /**
     * @type
     */
    protected $apiName;

    protected $subsciber;

    /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
     * $EmarsysHelper
     *
     * @type \Magento\Customer\Model\Customer
     */
    protected $CustomerModel;

    /**
     * Social constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerFactory $customerFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param \Mageplaza\SocialLogin\Helper\Social $apiHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        \Mageplaza\SocialLogin\Helper\Social $apiHelper,
        \Magento\Newsletter\Model\Subscriber $subsciber,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Tatva\Subscription\Model\SubscriptionFactory $subscriptionRepostry,
        \Tatva\Subscription\Model\SubscriptionInvitationFactory  $subscriptioninvitation,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Paypalrec\Model\Unsubscribe $unsubscribe,
        Customer $CustomerModel,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        array $data = []
    ) {
    parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager = $storeManager;
        $this->apiHelper = $apiHelper;
        $this->subscriber          = $subsciber;
        $this->customerSession     = $customerSession;
        $this->scopeConfig         = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->__subscriptionRepostry = $subscriptionRepostry;
        $this->_subscriptioninvitation = $subscriptioninvitation;
        $this->SubscriptionHelper = $SubscriptionHelper;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->CustomerModel = $CustomerModel;
        $this->registry = $registry;
        $this->_unsubscribe = $unsubscribe;
        $this->_teamplanModel = $teamplanModel;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Social::class);
    }

    /**
     * @param $identify
     * @param $type
     *
     * @return Customer
     */
    public function getCustomerBySocial($identify, $type)
    {
        $customer = $this->customerFactory->create();

        $socialCustomer = $this->getCollection()
            ->addFieldToFilter('social_id', $identify)
            ->addFieldToFilter('type', $type)
            ->getFirstItem();
        if ($socialCustomer && $socialCustomer->getId()) {
            $customer->load($socialCustomer->getCustomerId());
        }

        return $customer;
    }

    public function deleteSocialData($identify, $type="facebook")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("1"); 
        $customer = $this->customerFactory->create();
        $this->registry->register('isSecureArea', true);
        
        $socialCustomer = $this->getCollection()
            ->addFieldToFilter('social_id', $identify)
            ->addFieldToFilter('type', $type)
            ->getFirstItem();
        try
        {
            if ($socialCustomer && $socialCustomer->getId()) {

                $customerId = $socialCustomer->getCustomerId();
                //$socialCustomer->delete();   
                
                $logger->info("Unsubscribe Start");
                
                $this->_unsubscribe->Unsubscribe($customerId);
                
                $logger->info("Unsubscribe End");
                
                $customer->load($customerId)->delete(); 
                return "200";
                
                //$this->registry->register('isSecureArea', false);
                //return true;
            }
            else
            {
                //$this->registry->register('isSecureArea', false);
                //return false;
                return "403";
            }
        }
        catch(Exception $e)
        {
            $this->registry->register('isSecureArea', false);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
        }
        
    }

    public function CheckSocialFacebookCustomer($identify,$type="facebook")
    {
        $socialCustomer = $this->getCollection()
            ->addFieldToFilter('social_id', $identify)
            ->addFieldToFilter('type', $type)
            ->getFirstItem();

        if ($socialCustomer && $socialCustomer->getId()) {
            
            return "403";
        }
        else
        {
            return "200";
        }
    }

    /**
     * @param $email
     * @param null $websiteId
     *
     * @return Customer
     * @throws LocalizedException
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        /** @var Customer $customer */
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId ?: $this->storeManager->getWebsite()->getId());
        $customer->loadByEmail($email);

        return $customer;
    }

    /**
     * @param $data
     * @param $store
     *
     * @return mixed
     * @throws Exception
     */
    public function createCustomerSocial($data, $store)
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $customer->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());

        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer);
            $this->connectByCreatingAccount($customer);
            $objectManager = ObjectManager::getInstance();
            $mathRandom = $objectManager->get(Random::class);
            $newPasswordToken = $mathRandom->getUniqueHash();
            $accountManagement = $objectManager->get(AccountManagementInterface::class);
            $accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);

            if ($this->apiHelper->canSendPassword($store)) {
                $this->getEmailNotification()->newAccount(
                    $customer,
                    EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD
                );
            }

            $this->setAuthorCustomer($data['identifier'], $customer->getId(), $data['type']);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.')
            );
        } catch (Exception $e) {
            if ($customer->getId()) {
                $this->_registry->register('isSecureArea', true, true);
                $this->customerRepository->deleteById($customer->getId());
            }
            throw $e;
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create()->load($customer->getId());

        return $customer;
    }

    public function connectByCreatingAccount($customer)
    {
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

        //Emarsys integration start//
        $field4 =  $this->EmarsysHelper->isApiEnabled();
        if($field4)
            {
                   
                $fields_string = "";
                //$url = $this->EmarsysHelper->getApiUrl();

                $contact = array(
                            "1"=>$customer->getFirstname(),
                            "2"=>$customer->getLastname(),
                            "3"=>$customer->getEmail(),
                            "485"=>$customer->getId(),
                            "490"=>"2",
                            "31"=>true
                          );
                $encode = json_encode($contact);
               
                try {
                    
                    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');

                    $result = $apiHelper->send('POST', 'contact/getdata', '{"keyId": "485","keyValues":["'.$customer->getId().'"],"fields": ["3"]}');

                    if(count($result->data->errors))
                    {
                        $apiHelper->send('POST', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    }
                    else
                    {
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    }
                    

                    $subscriber = $this->_subscriberFactory->create()->loadByEmail($customer->getEmail());
                    $subscriber->setImportMode(true)->setCustomSubscribe($customer->getEmail(),true);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     */
    private function getEmailNotification()
    {
        return ObjectManager::getInstance()->get(EmailNotificationInterface::class);
    }

    /**
     * @param $identifier
     * @param $customerId
     * @param $type
     *
     * @return $this
     * @throws Exception
     */
    public function setAuthorCustomer($identifier, $customerId, $type)
    {
        $this->setData([
            'social_id'              => $identifier,
            'customer_id'            => $customerId,
            'type'                   => $type,
            'is_send_password_email' => $this->apiHelper->canSendPassword()
        ])
            ->setId(null)
            ->save();

        return $this;
    }

    public function setCustomSubscribe($email,$social=NULL){
        $customer = $this->getCustomerByEmail($email,NULL);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $newsletter_sub = $objectManager->create('Magento\Newsletter\Model\Subscriber');

        $item = $this->subscriber->loadByEmail($email);

        if(!empty($item->getData()))
        {
            $item->load($email);
            $item->addData([
                'subscriber_confirm_code' =>  $this->subscriber->randomSequence(),
                'store_id'                =>  $customer->getStoreId(),
                'is_status_changed'       =>  true
            ])
            ->save();
        }
        else
        {
            $newsletter_sub->setSubscriberConfirmCode($this->subscriber->randomSequence());
            $newsletter_sub->setStatus(1);
            $newsletter_sub->setSubscriberEmail($email);
            $newsletter_sub->setStoreId($customer->getStoreId());
            $newsletter_sub->setCustomerId($customer->getEntityId());
            $newsletter_sub->setIsStatusChanged(true);  

            $newsletter_sub->save();
        }

        return $this;
    }

    /**
     * @param $apiName
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getUserProfile($apiName)
    {
      try 
      {
        $config = [
            'base_url'   => $this->apiHelper->getBaseAuthUrl(),
            'providers'  => [
                $apiName => $this->getProviderData($apiName)
            ],
            'debug_mode' => false
        ];

        $auth = new Hybrid_Auth($config);
        $adapter = $auth->authenticate($apiName, $this->apiHelper->getAuthenticateParams($apiName));

        return $adapter->getUserProfile();
      } catch (Exception $e) 
      {
          $redirectUrl= $this->storeManager->getStore()->getBaseUrl(); 
          $this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
          exit();
      }
    }

    /**
     * @return array
     */
    public function getProviderData($apiName)
    {
        $data = [
            'enabled' => $this->apiHelper->isEnabled(),
            'keys'    => [
                'id'     => $this->apiHelper->getAppId(),
                'key'    => $this->apiHelper->getAppId(),
                'secret' => $this->apiHelper->getAppSecret()
            ]
        ];

        return array_merge($data, $this->apiHelper->getSocialConfig($apiName));
    }
}
