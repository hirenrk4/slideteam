<?php

namespace Tatva\Newsletter\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Subscriber extends \Magento\Newsletter\Model\Subscriber
{

	  public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Newsletter\Helper\Data $newsletterData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DateTime $dateTime = null
    ) {
        $this->_newsletterData = $newsletterData;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->inlineTranslation = $inlineTranslation;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->dateTime = $dateTime ?: ObjectManager::getInstance()->get(DateTime::class);
        
        parent::__construct($context, $registry, $newsletterData, $scopeConfig, $transportBuilder,$storeManager,$customerSession,$customerRepository,$customerAccountManagement,$inlineTranslation,$resource,$resourceCollection,$data);
    }

	public function confirm($code)
    {

        if($this->getCode()==$code)
        {
          if($this->getUuid())
          {
             $uuid = $this->getUuid();
          }
          else
          {
            $uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),mt_rand( 0, 0xffff ),mt_rand( 0, 0x0fff ) | 0x4000,        mt_rand( 0, 0x3fff ) | 0x8000,mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));
            $this->setUuid($uuid);
          }
            $this->setStatus(self::STATUS_SUBSCRIBED)
                ->setIsStatusChanged(true)
                ->save();
            //Emarsys integration start//

              $field4 =  $this->EmarsysHelper->isApiEnabled();
              if($field4)
                {
                    
                    $contact = array(
                                "3"=>$this->getSubscriberEmail(),
                                "485"=>$uuid,
                                "31"=>true
                              );
                    $encode = json_encode($contact); 

                    try {
                        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                       
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            //Emarsys integration end//
            return true;
        }
        return false;
    }
    
    public function subscribe($email)
    {
        $this->loadByEmail($email);

        if ($this->getId() && $this->getStatus() == self::STATUS_SUBSCRIBED) {
            return $this->getStatus();
        }

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed = $this->_scopeConfig->getValue(
            self::XML_PATH_CONFIRMATION_FLAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1 ? true : false;
        $isOwnSubscribes = false;

        $isSubscribeOwnEmail = $this->_customerSession->isLoggedIn()
            && $this->_customerSession->getCustomerDataObject()->getEmail() == $email;

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true) {
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                     $uuid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                    mt_rand( 0, 0xffff ),
                    mt_rand( 0, 0x0fff ) | 0x4000,
                    mt_rand( 0, 0x3fff ) | 0x8000,
                    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
                    );
                    $this->setUuid($uuid);
                    //Emarsys integration start//
                    $field4 =  $this->EmarsysHelper->isApiEnabled();
		              if($field4)
		                {
                        $contact = array(
                                "485"=>$ownerId,
                                "31"=>true
                              );                  
                    $encode = json_encode($contact);
                   
                  try {
                        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');
                        $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');

                        $subscriber = $this->_subscriberFactory->create()->loadByEmail($customer->getEmail());
                        $subscriber->setImportMode(true)->setCustomSubscribe($customer->getEmail(),true);

                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                    }
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
        }

        if ($isSubscribeOwnEmail) {
            try {
                $customer = $this->customerRepository->getById($this->_customerSession->getCustomerId());
                $this->setStoreId($customer->getStoreId());
                $this->setCustomerId($customer->getId());
            } catch (NoSuchEntityException $e) {
                $this->setStoreId($this->_storeManager->getStore()->getId());
                $this->setCustomerId(0);
            }
        } else {
            $this->setStoreId($this->_storeManager->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setStatusChanged(true);

        try {
            /* Save model before sending out email */
            $this->save();
            if ($isConfirmNeed === true
                && $isOwnSubscribes === false
            ) {
                $this->sendConfirmationRequestEmail();
            } else {
                $this->sendConfirmationSuccessEmail();
            }
            return $this->getStatus();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
  
}