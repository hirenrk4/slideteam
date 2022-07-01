<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Free\Controller\Ajax;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\SubscriberFactory;
/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RegisterPdp extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;
    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;
    /**
     * @var \ManishJoy\CustomerLogin\Model\Customer $customerModel
     */
    protected $customerModel;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;
    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;
     /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
     * Initialize Login controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        \Tatva\Free\Model\Customer $customerModel,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        SubscriberFactory $subscriberFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerModel = $customerModel;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->_httpClientFactory  = $httpClientFactory;
        $this->scopeConfig         = $scopeConfig;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        $this->messageManager = $messageManager;
    }
    public function execute()
    {
        $userData = null;
        $httpBadRequestCode = 400;
        $response = [
            'errors' => false,
            'message' => __('Registration successful.')
        ];
        // echo "<pre>";
        // print_r($this->customerSession->getData('downloadable_ajax_loginform_word'));die;
        $captcha_data = $this->customerSession->getData('downloadable_ajax_loginform_word')['data'];
        $ebook_captcha = $this->getRequest()->getPost('captcha');

        /*Master captcha*/
        $master_enable = $this->scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if($ebook_captcha['downloadable_ajax_loginform'] != $captcha_data)
        {
            if($master_enable)
            {
                $captcha_data = $master_captcha;
            }
        }

        if($captcha_data != $ebook_captcha['downloadable_ajax_loginform']){
            $response = [
                'errors' => true,
                'message' => __('Invalid Captcha.')
            ];
        } else if ($this->customerModel->userExists($this->getRequest()->getPost('email'))) {
            $response = [
                'errors' => true,
                'message' => __('A user already exists with this email id.')
            ];
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            try {
                $userData = [
                                    'firstname' => $this->getRequest()->getPost('firstname'),
                                    'lastname' => $this->getRequest()->getPost('lastname'),
                                    'email' => $this->getRequest()->getPost('email'),
                                    'password' => $this->getRequest()->getPost('password'),
                                    'password_confirmation' => $this->getRequest()->getPost('password_confirmation')
                                ];
            } catch (\Exception $e) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            if (!$userData || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
                return $resultRaw->setHttpResponseCode($httpBadRequestCode);
            }
            try {
                $isUserRegistered = $this->customerModel->createUser($userData);
                if (!$isUserRegistered) {
                    $response = [
                        'errors' => true,
                        'message' => __('Something went wrong.')
                    ];
                } else {
                    $customer = $this->customerAccountManagement->authenticate(
                        $userData['email'],
                        $userData['password']
                    );
                    if ($this->getRequest()->getParam('is_subscribed', false)) {
                        $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                    }

                    // $field4 =  $this->EmarsysHelper->isApiEnabled();
  
                    //Emarsys Integration
                                           
                    // if($field4)
                    // {
                    //     $subscriberFactory=$this->subscriberFactory->create();
                    //     $newsletter_subscribe=$subscriberFactory->loadByCustomerId($customer->getId())->isSubscribed();

                    //     $contact = array(
                    //             "1"=>$customer->getFirstname(),
                    //             "2"=>$customer->getLastname(),
                    //             "3"=>$customer->getEmail(),
                    //             "485"=>$customer->getId(),
                    //             "490"=>2
                    //           );

                    //    if(!empty($newsletter_subscribe))
                    //     {   
                    //         $contact["31"] = true;
                    //     }
                    //     else
                    //     {             
                    //         $contact["31"] = false;
                    //     }
                                             

                    //     try {
                    // 	   $encode = json_encode($contact); 
                    // 	   $apiHelper = $this->emarsysApiHelper;

                    //        $result = $apiHelper->send('POST', 'contact/getdata', '{"keyId": "485","keyValues":["'.$customer->getId().'"],"fields": ["3"]}');

                    //        if(count($result->data->errors))
                    //        {
                    //             $apiHelper->send('POST', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    //        }
                    //        else
                    //        {
                    //             $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    //        }                               

                    // 		$response = [
					//             'errors' => false,
					//             'message' => __('Registration successful.')
					//         ];
                    //         $this->messageManager->addSuccessMessage(__('Thank you for registering with SlideTeam.'));

                    //     } catch (\Exception $e) {
                    //        $response = [
                    //             'errors' => true,
                    //             'message' => $e->getMessage()
                    //         ];
                    //     }
                    // }

                    //Emarsys Integration

                    $response = [
                        'errors' => false,
                        'message' => __('Registration successful.')
                    ];
                    $this->messageManager->addSuccessMessage(__('Thank you for registering with SlideTeam.'));

                    $this->customerSession->setCustomerDataAsLoggedIn($customer);
                    $this->customerSession->regenerateId();
                    $this->_eventManager->dispatch('customer_register_success',['customer' => $customer ]); 
                }
            } catch (LocalizedException $e) {
                $response = [
                    'errors' => true,
                    'message' => $e->getMessage()
                ];
            } catch (\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' => __('Something went wrong.')
                ];
            }
            $this->customerSession->setCustomerFormData($this->getRequest()->getPostValue());
        }
            
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}