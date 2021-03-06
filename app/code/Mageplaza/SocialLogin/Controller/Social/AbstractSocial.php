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

namespace Mageplaza\SocialLogin\Controller\Social;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SocialLogin\Helper\Social as SocialHelper;
use Mageplaza\SocialLogin\Model\Social;

/**
 * Class AbstractSocial
 *
 * @package Mageplaza\SocialLogin\Controller
 */
abstract class AbstractSocial extends Action
{
    /**
     * @type Session
     */
    protected $session;

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type AccountManagementInterface
     */
    protected $accountManager;

    /**
     * @type SocialHelper
     */
    protected $apiHelper;

    /**
     * @type Social
     */
    protected $apiObject;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @type
     */
    protected $cookieMetadataManager;

    /**
     * @type
     */
    protected $cookieMetadataFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * Login constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManager
     * @param SocialHelper $apiHelper
     * @param Social $apiObject
     * @param Session $customerSession
     * @param AccountRedirect $accountRedirect
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManager,
        SocialHelper $apiHelper,
        Social $apiObject,
        Session $customerSession,
        AccountRedirect $accountRedirect,
        RawFactory $resultRawFactory,
        \Magento\Catalog\Model\Session $catalogSession,
        \Tatva\Subscription\Model\Subscription $subscription,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->accountManager = $accountManager;
        $this->apiHelper = $apiHelper;
        $this->apiObject = $apiObject;
        $this->session = $customerSession;
        $this->accountRedirect = $accountRedirect;
        $this->resultRawFactory = $resultRawFactory;
        $this->_catalogSession  = $catalogSession; 
        $this->_subscription    = $subscription;
        $this->_messageManager = $messageManager;
        $this->_productRepository = $productRepository;
        $this->_coreSession = $coreSession;

    }

    /**
     * Get Store object
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param $userProfile
     * @param $type
     *
     * @return bool|Customer|mixed
     * @throws Exception
     * @throws LocalizedException
     */
    public function createCustomerProcess($userProfile, $type)
    {
        $name = explode(' ', $userProfile->displayName ?: __('New User'));
        $user = array_merge([
            'email'      => $userProfile->email ?: $userProfile->identifier . '@' . strtolower($type) . '.com',
            'firstname'  => $userProfile->firstName ?: (array_shift($name) ?: $userProfile->identifier),
            'lastname'   => $userProfile->lastName ?: (array_shift($name) ?: $userProfile->identifier),
            'identifier' => $userProfile->identifier,
            'type'       => $type
        ], $this->getUserData($userProfile));

        return $this->createCustomer($user, $type);
    }

    /**
     * Create customer from social data
     *
     * @param $user
     * @param $type
     *
     * @return bool|Customer|mixed
     * @throws Exception
     * @throws LocalizedException
     */
    public function createCustomer($user, $type)
    {
        $customer = $this->apiObject->getCustomerByEmail($user['email'], $this->getStore()->getWebsiteId());
        if ($customer->getId()) {
            $this->apiObject->setAuthorCustomer($user['identifier'], $customer->getId(), $type);
        } else {
            try {
                $customer = $this->apiObject->createCustomerSocial($user, $this->getStore());
            } catch (Exception $e) {
                $this->emailRedirect($e->getMessage(), false);

                return false;
            }
        }

        return $customer;
    }

    /**
     * @param $profile
     *
     * @return array
     */
    protected function getUserData($profile)
    {
        return [];
    }

    /**
     * Redirect to login page if social data is not contain email address
     *
     * @param $apiLabel
     * @param bool $needTranslate
     *
     * @return $this
     */
    public function emailRedirect($apiLabel, $needTranslate = true)
    {
        $message = $needTranslate ? __('Email is Null, Please enter email in your %1 profile', $apiLabel) : $apiLabel;
        $this->messageManager->addErrorMessage($message);
        $this->_redirect('customer/account/login');

        return $this;
    }

    /**
     * Return redirect url by config
     *
     * @return mixed
     */
    protected function _loginPostRedirect()
    {
        $url = $this->_url->getUrl('customer/account');

        if ($this->_request->getParam('authen') === 'popup') {
            $url = $this->_url->getUrl('checkout');
        } else {
            $requestedRedirect = $this->accountRedirect->getRedirectCookie();
            if ($requestedRedirect && !$this->apiHelper->getConfigValue('customer/startup/redirect_dashboard')) {
                $url = $this->_redirect->success($requestedRedirect);
                $this->accountRedirect->clearRedirectCookie();
            }
        }

        $object = ObjectManager::getInstance()->create(DataObject::class, ['url' => $url]);
        $this->_eventManager->dispatch('social_manager_get_login_redirect', [
            'object'  => $object,
            'request' => $this->_request
        ]);
        $url = $object->getUrl();

        $social = "";
        $socialType= $this->_request->getParam('type');
        $socialTypes=array('facebook'=>'facebook','google'=>'google');
        if(in_array($socialType,$socialTypes))
        {
            $social = $socialTypes[$socialType];
        }
        if(!empty($this->_request->getParam('url')))
        {
            $url = $this->_request->getParam('url');
        }
        if (explode('=',$_SERVER['QUERY_STRING'])[0]=="urlredirect"){
            $url = explode('=',$_SERVER['QUERY_STRING'])[1];
        }
        if(isset($_SERVER['productid']) && $_SERVER['productid'] != null){
            $url = $this->_url->getUrl('checkout/cart/add/')."product/".$_SERVER['productid'];
        }
        
        $this->messageManager->addSuccess(__('You have successfully logged in using your '.$social.' account.'));
        
        // 1464 start
        /*if($this->_request->getParam('loginfromtype')){
            if ($this->_request->getParam('loginfromtype') == 'pdp_sociallogin') {
                if ($this->getUserSubscription() == '0') {
                    $url = $this->_url->getUrl()."pricing";
                }else{
                    $url = $this->_request->getParam('redirect_urllink');
                }
            }
            if ($this->_request->getParam('loginfromtype') == 'pricing_sociallogin') {
                if ($this->getUserSubscription() == '1') {
                    $url = $this->_url->getUrl()."subscription/index/list";
                }else{
                    $url = $this->_url->getUrl('checkout/cart/add/')."product/".$this->_request->getParam('addto_productId');
                }
            }
        }*/
        // 1464 end

        return $url;
    }

    /**
     * Return javascript to redirect when login success
     *
     * @param null $content
     *
     * @return Raw
     */
    public function _appendJs($content = null)
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents($content ?: sprintf(
            "<script>window.opener.socialCallback('%s', window);</script>",
            $this->_loginPostRedirect()
        ));
    }

    /**
     * @param $customer
     *
     * @throws InputException
     * @throws FailureToSendException
     */
    public function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->session->setCustomerAsLoggedIn($customer);
            $this->session->regenerateId();

            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @return PhpCookieManager
     * @deprecated
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }

        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return CookieMetadataFactory
     * @deprecated
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }

        return $this->cookieMetadataFactory;
    }

    // 1464
    /*public function getUserSubscription(){
        $subscription_history = "";
        $subscription_history = $this->_subscription->getLastPaidSubscriptionhistory();
        $response = '';
        $_subscriptions = $this->_subscription->getCustomerSubscriptions();
        $subscriptions_count = $_subscriptions->getSize();
        $lastorderData = $this->_subscription->getLastCustomerOrder();
        // $customerIpnWait = $this->_subscription->checkCustomerIpnWait();
        // die('.-.');
        $nosubscription = 0;
        if(isset($lastorderData[0]))
        {
            $currentDate = strtotime($this->_subscription->getCurrentGmtDate());
            $orderDate = strtotime($lastorderData[0]['created_at']);
            
            if(($currentDate-$orderDate)/60 > 5 && ($lastorderData[0]['status'] != "payment_completed" || $lastorderData[0]['status'] != "complete"))
            {
                $nosubscription = 1;
            }
        } else {
            $nosubscription = 1;
        }
        if (is_object($subscription_history) && $subscription_history !== "Unsubscribed"){
            $response ='1';
        }elseif($nosubscription == '1'){
            $response ='0';
        }else{
            $response ='0';
        }
        return $response;
    }*/
    // 1464 end
}
