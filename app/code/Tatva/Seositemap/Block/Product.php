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
class Product extends Template
{   
    protected $_storeManager;
    protected $_productCollection;
	protected $_scopeConfig;   

    public function __construct(
		Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,		
        $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_productCollection = $productCollection;
		$this->_scopeConfig = $scopeConfig;
		
        parent::__construct($context, $data);
    }
	
	public function getCategorySitemapUrl($categorymap)
	{
		return $this->_storeManager->getStore()->getUrl($categorymap);
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
		
		$productCollection = $this->getProductCollection();
        if ($productCollection) {
            $pager = $this->getLayout()->createBlock('Tatva\Seositemap\Block\Html\Pager','custom.history.pager')->setAvailableLimit($paginationarray)->setShowPerPage(true)->setCollection($productCollection);
            $this->setChild('pager', $pager);
            $productCollection->load();
        }
        return $this;
    }

    public function getProductCollection(){

		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;		
		$paginationValue = explode(",",$this->_scopeConfig->getValue("catalog/frontend/grid_per_page_values",$storeScope));	
		
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : $paginationValue[0];

        $collection = $this->_productCollection->create()->addAttributeToSelect('*')->addAttributeToFilter('type_id',['eq'=>'downloadable'])->setStore($this->_storeManager->getStore())->addAttributeToSort('name','ASC');
		
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;        
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}  