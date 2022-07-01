<?php

namespace Tatva\Emarsys\Block;

class Emarsys extends \Magento\Framework\View\Element\Template
{
    protected $_page;
    protected $_scopeConfig;
    protected $_registry;
    protected $_storeManager;
    protected $_request;
    protected $_categoryFactory;

    public function __construct
    (
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
         \Tatva\Tag\Model\TagFactory $tagTagFactory
    ) 
    {
        $this->_page = $page;
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->tagTagFactory = $tagTagFactory;
        parent::__construct($context);
    }
    
    public function getPageIdentifier()
    {
        return $this->_page->getIdentifier();
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    public function getEmarsysIntegrationStatus()
    {
        return $this->scopeConfig->getValue('button/emarsys_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeManager->getStore());
    }
    
    public function getRegistry()
    {
        return $this->_registry;
    }

    public function getRequest()
    {
        return $this->_request;
    }
    public function getCategory()
    {
        return $this->_categoryFactory;
    }
    public function getCategoryList()
    {
        $a4_category_id = $this->scopeConfig->getValue('A4/general/a4category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $a4categoryCollection = explode(",",$a4_category_id);
        $catarray = [];
        foreach ($a4categoryCollection as $value) {
            $category = $this->_categoryFactory->create()->load($value);
            $catarray[] = $category->getName();
        }
        return $catarray;
    }
}