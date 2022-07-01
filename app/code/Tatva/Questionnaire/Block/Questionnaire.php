<?php

namespace Tatva\Questionnaire\Block;

class Questionnaire extends \Magento\Framework\View\Element\Template
{

    protected $_scopeConfig;
    protected $_customerSession;
    protected $_cookieManager;

    public function __construct
    (
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Customer\Model\Session $customerSession, 
        array $data = [])
    {
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_cookieManager = $cookieManager;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function getEmail()
    {
        return $this->_scopeConfig->getValue('contact/customemail/recipient_email_design', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
