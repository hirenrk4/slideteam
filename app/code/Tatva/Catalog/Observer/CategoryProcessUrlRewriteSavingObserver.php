<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Catalog\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteDatabaseMap;
use Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Model\ResourceModel\Group\Collection as StoreGroupCollection;
use Magento\Framework\App\ObjectManager;

/**
 * Generates Category Url Rewrites after save and Products Url Rewrites assigned to the category that's being saved
 */
class CategoryProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer
     */
    private $urlRewriteBunchReplacer;

    /**
     * @var \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
     */
    private $urlRewriteHandler;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\DatabaseMapPool
     */
    private $databaseMapPool;

    /**
     * @var string[]
     */
    private $dataUrlRewriteClassNames;

    /**
     * @var CollectionFactory
     */
    private $storeGroupFactory;

    /**
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param UrlRewriteHandler $urlRewriteHandler
     * @param UrlRewriteBunchReplacer $urlRewriteBunchReplacer
     * @param DatabaseMapPool $databaseMapPool
     * @param string[] $dataUrlRewriteClassNames
     * @param CollectionFactory|null $storeGroupFactory
     */
    public function __construct(
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlRewriteHandler $urlRewriteHandler,
        UrlRewriteBunchReplacer $urlRewriteBunchReplacer,
        DatabaseMapPool $databaseMapPool,
        $dataUrlRewriteClassNames = [
            DataCategoryUrlRewriteDatabaseMap::class,
            DataProductUrlRewriteDatabaseMap::class
        ],
        CollectionFactory $storeGroupFactory = null
    ) {
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlRewriteHandler = $urlRewriteHandler;
        $this->urlRewriteBunchReplacer = $urlRewriteBunchReplacer;
        $this->databaseMapPool = $databaseMapPool;
        $this->dataUrlRewriteClassNames = $dataUrlRewriteClassNames;
        $this->storeGroupFactory = $storeGroupFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getEvent()->getData('category');
        if ($category->getParentId() == Category::TREE_ROOT_ID) {
            return;
        }

        if (!$category->hasData('store_id')) {
            $this->setCategoryStoreId($category);
        }

        $mapsGenerated = false;
        
            if ($category->dataHasChangedFor('url_key')
                || $category->dataHasChangedFor('is_anchor')
                || $category->getChangedProductIds()
            ) {
            if ($category->dataHasChangedFor('url_key')) {
                $categoryUrlRewriteResult = $this->categoryUrlRewriteGenerator->generate($category);
                $this->urlRewriteBunchReplacer->doBunchReplace($categoryUrlRewriteResult);
            }
            $productUrlRewriteResult = $this->urlRewriteHandler->generateProductUrlRewrites($category);
            $productids = $category->getChangedProductIds();
            if($productids)
            {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();        
                $tableName1 = $resource->getTableName('tatva_product_category_remove');
                
                foreach($productids as $key => $product_id)
                {
                    $sql = "Select * FROM " . $tableName1." Where product_id = ".$product_id;
                    $result = $connection->fetchAll($sql);
                    $count = count($result);
                    if(!empty($count))
                    {
                        $bypass = 1;    
                    }
                }                
            }

            if(empty($bypass))
            {
                $this->urlRewriteBunchReplacer->doBunchReplace($productUrlRewriteResult); 
            }
            $mapsGenerated = true;
        }
        

        //frees memory for maps that are self-initialized in multiple classes that were called by the generators
        if ($mapsGenerated) {
            $this->resetUrlRewritesDataMaps($category);
        }
    }

    /**
     * in case store_id is not set for category then we can assume that it was passed through product import.
     * store group must have only one root category, so receiving category's path and checking if one of it parts
     * is the root category for store group, we can set default_store_id value from it to category.
     * it prevents urls duplication for different stores
     * ("Default Category/category/sub" and "Default Category2/category/sub")
     *
     * @param Category $category
     * @return void
     */
    private function setCategoryStoreId($category)
    {
        /** @var StoreGroupCollection $storeGroupCollection */
        $storeGroupCollection = $this->storeGroupFactory->create();

        foreach ($storeGroupCollection as $storeGroup) {
            /** @var \Magento\Store\Model\Group $storeGroup */
            if (in_array($storeGroup->getRootCategoryId(), explode('/', $category->getPath()))) {
                $category->setStoreId($storeGroup->getDefaultStoreId());
            }
        }
    }

    /**
     * Resets used data maps to free up memory and temporary tables
     *
     * @param Category $category
     * @return void
     */
    private function resetUrlRewritesDataMaps($category)
    {
        foreach ($this->dataUrlRewriteClassNames as $className) {
            $this->databaseMapPool->resetMap($className, $category->getEntityId());
        }
    }
}
