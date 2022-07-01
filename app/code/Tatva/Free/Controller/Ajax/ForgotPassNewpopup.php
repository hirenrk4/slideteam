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
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Escaper;

/**
 * Login controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPassNewpopup extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
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
        Escaper $escaper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->escaper = $escaper;
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
     * Login registered users and initiate a session.
     *
     * Expects a POST. ex for JSON {"username":"user@magento.com", "password":"userpassword"}
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $httpBadRequestCode = 400;
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $email = (string)$this->getRequest()->getPost('email');

        if ($email) {
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $response = [
                    'errors' => true,
                    'message' => __('Please correct the email address.')
                ];
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );

                $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                    $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                    $this->getAccountRedirect()->clearRedirectCookie();
                }
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch(SecurityViolationException $exception) {
                $response = [
                    'errors' => true,
                    'message' => $e->getMessage()
                ];
            } catch(\Exception $e) {
                $response = [
                    'errors' => true,
                    'message' =>__('We\'re unable to send the password reset email.').$e->getMessage()
                ];
            }
            $response = [
                'errors' => false,
                'message' =>$this->getSuccessMessage($email)
            ];
        } else {
            $response = [
                'errors' => true,
                'message' => __('Please enter your email.')
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}