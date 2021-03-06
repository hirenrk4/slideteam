<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Wishlist\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;

class Plugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @param CustomerSession $customerSession
     * @param \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState
     * @param ScopeConfigInterface $config
     * @param RedirectInterface $redirector
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState,
        ScopeConfigInterface $config,
        RedirectInterface $redirector,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->authenticationState = $authenticationState;
        $this->config = $config;
        $this->redirector = $redirector;
        $this->_customerUrl = $customerUrl;
    }

    /**
     * Perform customer authentication and wishlist feature state checks
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {

        if ($this->authenticationState->isEnabled() && !$this->customerSession->authenticate()) {
            
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            if (!$this->customerSession->getBeforeWishlistUrl()) {                
                $this->customerSession->setBeforeWishlistUrl($this->redirector->getRefererUrl());
            }

            if(empty($request->getParams()))
            {
                $loginurl = $this->_customerUrl->getLoginUrl();
                $urls = explode("referer",$loginurl);

                header("Location: ".$urls[0]);
                exit;
            }
            
            $this->customerSession->setBeforeWishlistRequest($request->getParams());
            $this->customerSession->setBeforeRequestParams($this->customerSession->getBeforeWishlistRequest());
            $this->customerSession->setBeforeModuleName('wishlist');
            $this->customerSession->setBeforeControllerName('index');
            $this->customerSession->setBeforeAction('add');
        }
        if (!$this->config->isSetFlag('wishlist/general/active')) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
