<?php
namespace Tatva\Theme\Helper;

class Header extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $httpHeader;
    
    protected $scopeConfig;
    protected $_storeManager;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\HTTP\Header $httpHeader,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Customer\Model\Session $customerSession
) {
        $this->httpHeader = $httpHeader;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    
    public function isMobile(){
        $userAgent = $this->httpHeader->getHttpUserAgent();
        $data = \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
        return $data;
    }
    
    public function getConfig($config_path){
        return $this->scopeConfig->getValue($config_path);
    }
    
    public function getBase($urltype){
        return $this->_storeManager->getStore()->getBaseUrl($urltype);
    }
    public function isCustomerLoggedIn(){
        return $this->_customerSession->isLoggedIn();
    }
    public function getStoreBaseUrl(){
        return   $this->_storeManager->getStore(1)->getBaseUrl();
    }
}