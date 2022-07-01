<?php

namespace Tatva\Generalconfiguration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class A4Category implements ArrayInterface{
	
	protected $_categoryFactory;
	protected $_categoryCollectionFactory;
	
	public function __construct(
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory
	) {
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_categoryFactory = $categoryFactory;
	}
	
	/**
	* Get category collection
	*
	* @param bool $isActive
	* @param bool|int $level
	* @param bool|string $sortBy
	* @param bool|int $pageSize
	* @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
	*/
	
	
	public function getCategoryCollection($isActive = true, $level = true, $sortBy = false, $pageSize = false)
	{
		$collection = $this->_categoryCollectionFactory->create();
		$collection->addAttributeToSelect('*');
		
		// select only active categories
		if ($isActive) {
			$collection->addIsActiveFilter();
		}
		
		// select categories of certain level
		if ($level) {
			$collection->addLevelFilter($level);
		}
  
		// sort categories by some value
		if ($sortBy) {
			$collection->addOrderField($sortBy);
		}
  
		// select certain number of categories
		if ($pageSize) {
			$collection->setPageSize($pageSize);
		}
  
		return $collection;
	}
 
	public function toOptionArray(){
		
		$arr = $this->_toArray();
		$ret = [];
		foreach ($arr as $key => $value){
			$ret[] = [
				'value' => $key,
				'label' => $value
			];
		}	 
		return $ret;
	}
 
	public function _toArray()
    {
        $categories = $this->getCategoryCollection(true, '2', false, false);
        $catagoryList = array();
         foreach ($categories as $category){
             $category->getName();
             $catagoryList[$category->getEntityId()] = __($category->getName());
         }

        return $catagoryList;
    }
}