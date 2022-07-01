<?php
namespace Tatva\Limit\Block;

class Index extends \Magento\Framework\View\Element\Template {
    
    protected $_scopeConfig; 


    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            array $data = array()) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
    
    public function getScopeConfig($path)
    {
        return $this->_scopeConfig->getValue($path);
    }
}