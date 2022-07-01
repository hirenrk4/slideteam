<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Newsletter\Controller\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    protected $_customerSession;
    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Tatva\Emarsys\Helper\ApiData $emarsehelper,
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement
    ) {
        $this->_customerSession = $session;
        $this->emarsehelper = $emarsehelper;
        $this->customerAccountManagement = $customerAccountManagement;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl
        );
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailAvailable($email)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        if ($this->_customerSession->getCustomerDataObject()->getEmail() !== $email
            && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This email address is already assigned to another user.')
            );
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateGuestSubscription()
    {
        if ($this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)
                ->getValue(
                    \Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) != 1
            && !$this->_customerSession->isLoggedIn()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->_customerUrl->getRegisterUrl()
                )
            );
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateEmailFormat($email)
    {
        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid email address.'));
        }
    }

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This email address is already subscribed.')
                    );
                }

                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $this->messageManager->addSuccess(__('The confirmation request has been sent.'));
                } else {
                    

                    if ($this->_customerSession->isLoggedIn()) {
                        //Emarsys integration start//

                        $uuid = $this->_customerSession->getCustomer()->getId();


                          $field4 =  $this->emarsehelper->isApiEnabled();
                          if($field4)
                            {
                                
                                $contact = array(
                                            "3"=>$email,
                                            "485"=>$uuid,
                                            "31"=>true
                                          );
                                $encode = json_encode($contact); 

                                try {
                                    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                    $apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');
                                    $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');

                                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));

                                   
                                } catch (\Exception $e) {
                                    echo $e->getMessage();
                                }
                            }
                        //register user - Emarsys integration end//
                    } else {
                        //Emarsys integration start//

                        // $uuid = $this->_customerSession->getCustomer()->getId();
                          $field4 =  $this->emarsehelper->isApiEnabled();
                          if($field4)
                            {
                                
                                $contact = array(
                                            "1"=>'Guest User',
                                            "2"=>'Guest User',
                                            "3"=>$email,
                                            "485"=>$email,
                                            "31"=>true
                                          );
                                $encode = json_encode($contact); 

                                try {
                                    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                    $apiHelper = $_objectManager->get('Tatva\Subscription\Helper\EmarsysHelper');
                                    // $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');


                                    $apiHelper->send('POST', 'contact', '{"key_id": "485","contacts":['.$encode.']}');


                                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                                   
                                } catch (\Exception $e) {
                                    echo $e->getMessage();
                                }
                            }
                        //Emarsys integration end//
                    }

                    
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addException(
                    $e,
                    __('There was a problem with the subscription: %1', $e->getMessage())
                );
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong with the subscription.'));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
