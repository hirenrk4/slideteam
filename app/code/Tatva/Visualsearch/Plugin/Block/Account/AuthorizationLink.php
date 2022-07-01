<?php

namespace Tatva\Visualsearch\Plugin\Block\Account;

use Magento\Customer\Model\Context;
use Magento\Customer\Block\Account\SortLinkInterface;



class AuthorizationLink
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    protected $headerHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        \Tatva\Theme\Helper\Header $headerHelper,
        array $data = []
    ) {
        $this->_customerUrl = $customerUrl;
        $this->headerHelper = $headerHelper;
    }

    public function aftergetHref(\Magento\Customer\Block\Account\AuthorizationLink $customerAccount)
    {
        $vs_store_key = $this->headerHelper->getConfig('button/config/text') . '/';
        return $customerAccount->isLoggedIn()
            ? str_replace($vs_store_key, '', $this->_customerUrl->getLogoutUrl())
            : str_replace($vs_store_key, '', $this->_customerUrl->getLoginUrl());
    }

    
}