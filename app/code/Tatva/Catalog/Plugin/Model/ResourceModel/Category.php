<?php

namespace Tatva\Catalog\Plugin\Model\ResourceModel;

class Category
{
    public function aftergetChildrenCategories(\Magento\Catalog\Model\ResourceModel\Category $categoryData, $category)
    {
       $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
            'url_key'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'all_children'
        )->addAttributeToSelect(
            'is_anchor'
        )->addAttributeToFilter(
            'is_active',
            1
        )->addIdFilter(
            $category->getChildren()
        )->addAttributeToSort("name","asc")->joinUrlRewrite();

        return $collection;
    }
}