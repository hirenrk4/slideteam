<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Free\Controller\Ajax;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;
    /**
     * @var \Magento\Framework\Json\Helper\Data $helper
     */
    protected $helper;
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

    private $getCookiedata;
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
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Tatva\Free\Helper\Data $getCookiedata,
        \Tatva\Customer\Model\CustomerRedirectionFlow $customerRedirectionFlow,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->getCookiedata = $getCookiedata;
        $this->_customerRedirectionFlow = $customerRedirectionFlow;
        $this->customerRepository = $customerRepository;
    }
    /**
     * Get account redirect.
     * For release backward compatibility.
     *
     * @deprecated
     * @return AccountRedirect
     */
    protected function getAccountRedirect()
    {
        if (!is_object($this->accountRedirect)) {
            $this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
        }
        return $this->accountRedirect;
    }
    /**
     * Account redirect setter for unit tests.
     *
     * @deprecated
     * @param AccountRedirect $value
     * @return void
     */
    public function setAccountRedirect($value)
    {
        $this->accountRedirect = $value;
    }
    /**
     * @deprecated
     * @return ScopeConfigInterface
     */
    protected function getScopeConfig()
    {
        if (!is_object($this->scopeConfig)) {
            $this->scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }
        return $this->scopeConfig;
    }
    /**
     * @deprecated
     * @param ScopeConfigInterface $value
     * @return void
     */
    public function setScopeConfig($value)
    {
        $this->scopeConfig = $value;
    }
    /**
    * Retrieve cookie metadata factory
    *
    * @deprecated
    * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
    */
   private function getCookieMetadataFactory()
   {
       if (!$this->cookieMetadataFactory) {
           $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
               \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
           );
       }
       return $this->cookieMetadataFactory;
   }
    /**
    * Retrieve cookie manager
    *
    * @deprecated
    * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
    */
   private function getCookieManager()
   {
       if (!$this->cookieMetadataManager) {
           $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
               \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
           );
       }
       return $this->cookieMetadataManager;
   }
    /**
     * Login registered users and initiate a session.
     *
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        try {
            $credentials = [
                                'username' => $this->getRequest()->getPost('username'),
                                'password' => $this->getRequest()->getPost('password')
                            ];
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        // if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
        //     return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        // }
        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        
        $login = $this->getRequest()->getPost();
        try {
            if(isset($login['remember_me'])) {
                $logindetails = array('username' => $login['username'], 'password' => $login['password'], 'remchkbox' => 1);
                $logindetails = json_encode($logindetails);
                $this->getCookiedata->set($logindetails,$this->getCookiedata->getCookielifetime());
            }else{
                $this->getCookiedata->delete('remember');
            }

            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
             //$customer = $this->customerRepository->getById('1193425');
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
            
            $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
            if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                $this->getAccountRedirect()->clearRedirectCookie();
            }
            $redirectUrlForCustomer = $this->_customerRedirectionFlow->getRedirectUrlForCustomer();
            $response['url'] = $redirectUrlForCustomer;
        } catch (EmailNotConfirmedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.').$e->getMessage()
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}