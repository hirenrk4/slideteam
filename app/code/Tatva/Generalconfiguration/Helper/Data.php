<?php

namespace Tatva\Generalconfiguration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    protected $httpHeader;
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Header $httpHeader
    ) {
        parent::__construct($context);
        $this->httpHeader = $httpHeader;
    } 

    public function getConfig($config_path){
        return $this->scopeConfig->getValue(
                $config_path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
    }

    public function isMobileDevice()
    {
        $userAgent = $this->httpHeader->getHttpUserAgent();
        return \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
    }
}
