<?php
/**
 * Copyright © 2015 AionNext Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Seositemap\Block;

use Magento\Framework\View\Element\Template;

/**
 * Aion Test Page block
 */
class Category extends Template
{   
    protected $_storeManager;
    protected $_categoryCollection;
	protected $_scopeConfig;   

    public function __construct(
		Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Category $categoryCollection,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,		
        $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_categoryCollection = $categoryCollection;
		$this->_scopeConfig = $scopeConfig;
		
        parent::__construct($context, $data);
    }
	
	public function getProductSitemapUrl($productmap)
	{
		return $this->_storeManager->getStore()->getUrl($productmap);
	}
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();       
		
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;		
		$paginationValue = explode(",",$this->_scopeConfig->getValue("catalog/frontend/grid_per_page_values",$storeScope));		
		$paginationarray = array();
		
		foreach($paginationValue as $key => $value)
		{
			$paginationarray[$value] = $value;	
		}
		
		$catCollection = $this->getCategoriesCollection();
        if ($catCollection) {
            $pager = $this->getLayout()->createBlock('Tatva\Seositemap\Block\Html\Pager','custom.history.pager')->setAvailableLimit($paginationarray)->setShowPerPage(true)->setCollection($catCollection);
            $this->setChild('pager', $pager);
            $catCollection->load();
        }
        return $this;
    }

    public function getCategoriesCollection(){

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;		
		$paginationValue = explode(",",$this->_scopeConfig->getValue("catalog/frontend/grid_per_page_values",$storeScope));	
		
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : $paginationValue[0];

        $collection = $this->_categoryCollection->getStoreCategories('name', true, false)->addAttributeToFilter('visual_search',['neq'=>1]);
		//$collection->addIsActiveFilter();
		$collection->addOrderField('name');
		
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;        
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}  