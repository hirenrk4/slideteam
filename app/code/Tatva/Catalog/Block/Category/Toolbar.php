<?php

namespace Tatva\Catalog\Block\Category;



class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{

    public function setCollection($collection)
    {
        $routepath = $this->getRequest()->getFullActionName();

        //$curUrl = $this->getRequest()->getUriString();
        //$position = strpos($curUrl, '/new-powerpoint-templates'); 
        
        $collection->getSelect()->reset(\Zend_Db_Select::ORDER);
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());
        
        $limit = (int)$this->getLimit();
        if ($limit) 
        {
            $this->_collection->setPageSize($limit);
        }

        if($routepath == 'newproduct_index_index')
        {
            //$this->_collection->getSelect()->order('e.entity_id desc'); 
        }
        else
        {
            if ($this->getCurrentOrder()) 
            {
                if ($this->getCurrentOrder() == 'newest')
                {
                    $this->_collection->getSelect()->order('e.entity_id desc');  
                    //$this->_collection->setOrder('number_of_downloads', 'desc');
                }
                else 
                {
                    //$this->_collection->getSelect()->order('cat_index.position desc');
                    $this->_collection->setOrder('number_of_downloads', 'desc'); 
                }
            }
        }
        return $this;
    }

}