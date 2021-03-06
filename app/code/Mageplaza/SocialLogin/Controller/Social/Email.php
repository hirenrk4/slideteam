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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SocialLogin\Helper\Social as SocialHelper;
use Mageplaza\SocialLogin\Model\Social;

/**
 * Class AbstractSocial
 *
 * @package Mageplaza\SocialLogin\Controller
 */
class Email extends AbstractSocial
{
    /**
     * @type JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Customer
     */
    protected $customerFactory;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManager
     * @param SocialHelper $apiHelper
     * @param Social $apiObject
     * @param Session $customerSession
     * @param AccountRedirect $accountRedirect
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManager,
        SocialHelper $apiHelper,
        Social $apiObject,
        Session $customerSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \Tatva\Subscription\Model\Subscription $subscription,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AccountRedirect $accountRedirect,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerFactory = $customerFactory;

        parent::__construct(
            $context,
            $storeManager,
            $accountManager,
            $apiHelper,
            $apiObject,
            $customerSession,
            $accountRedirect,
            $resultRawFactory,
            $catalogSession,
            $subscription,
            $messageManager,
            $productRepository,
            $coreSession
        );
    }

    /**
     * @return ResponseInterface|Json|ResultInterface|void
     * @throws InputException
     * @throws LocalizedException
     * @throws FailureToSendException
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $type = $this->apiHelper->setType($this->getRequest()->getParam('type', null));
        if (!$type) {
            $this->_forward('noroute');

            return;
        }

        $result = ['success' => false];

        $realEmail = $this->getRequest()->getParam('realEmail', null);
        if (!$realEmail) {
            $result['message'] = __('Email is Null');

            return $resultJson->setData($result);
        }

        $customer = $this->customerFactory->create()
            ->setWebsiteId($this->getStore()->getWebsiteId())
            ->loadByEmail($realEmail);
        if ($customer->getId()) {
            $result['message'] = __('Email already exists');

            return $resultJson->setData($result);
        }

        $userProfile = $this->session->getUserProfile();
        $userProfile->email = $realEmail;

        $customer = $this->createCustomerProcess($userProfile, $type);
        $this->refresh($customer);

        $result['success'] = true;
        $result['message'] = __('Success!');
        $result['url'] = $this->_loginPostRedirect();

        return $resultJson->setData($result);
    }
}
