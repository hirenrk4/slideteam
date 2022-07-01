<?php

namespace Tatva\Categoryreport\Block\Adminhtml;

class Categoryreport extends \Magento\Framework\View\Element\Template
{
    
    //protected $_template = "Tatva_Categoryreport::categoryreport.phtml";
	protected $_categoryCollectionFactory;
    protected $_backendUrl;
    
	 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,		
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,        
        array $data = []
        )
    {   
    	$this->_categoryCollectionFactory = $categoryCollectionFactory;        
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        //$this->setTemplate("Tatva_Categoryreport::categoryreport.phtml");
        return $this;
    }  
	
	public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
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
        
        return $collection;
    }
	
	public function getCategories()
	{
		$catArray = array();
	 	$categories = $this->getCategoryCollection(true, 2, 'name', false);	
		foreach ($categories as $category) {
	   		$catArray[$category->getId()] = $category->getName();		
		}
		return $catArray;
	} 

    public function getBackendUrl()
    {
        $url = $this->_backendUrl->getUrl("categoryreport/report/generatecsv");
        return $url;
    }


}
