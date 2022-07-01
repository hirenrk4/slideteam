<?php

namespace Tatva\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\CollectionFilterInterface;

class CollectionFilter extends \Magento\Catalog\Model\Layer\Category\CollectionFilter
{
    public function __construct(\Magento\Catalog\Model\Product\Visibility $productVisibility, \Magento\Catalog\Model\Config $catalogConfig) {
        parent::__construct($productVisibility, $catalogConfig);
    }
    public function filter($collection,\Magento\Catalog\Model\Category $category)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addUrlRewrite($category->getId())
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }
}
