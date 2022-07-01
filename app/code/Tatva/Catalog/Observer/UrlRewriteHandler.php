<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Catalog\Observer;
use Magento\Catalog\Model\Category;
/**
 * Class for management url rewrites.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteHandler extends \Magento\CatalogUrlRewrite\Observer\UrlRewriteHandler
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var array
     */
    protected $isSkippedProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator
     */
    private $categoryBasedProductRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator $categoryBasedProductRewriteGenerator
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator $categoryBasedProductRewriteGenerator,
        \Magento\UrlRewrite\Model\MergeDataProviderFactory $mergeDataProviderFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryBasedProductRewriteGenerator = $categoryBasedProductRewriteGenerator;

        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory =  \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\UrlRewrite\Model\MergeDataProviderFactory::class
            );
        }

        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();

        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
    }

    public function generateProductUrlRewrites(Category $category): array
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $this->isSkippedProduct[$category->getEntityId()] = [];
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = $category->getStoreId();

        if ($category->getChangedProductIds()) {
            $this->isSkippedProduct[$category->getEntityId()] = $category->getAffectedProductIds();
            /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create()
                ->setStoreId($storeId)
                ->addIdFilter($category->getAffectedProductIds())
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');

            $collection->setPageSize(1000);
            $pageCount = $collection->getLastPageNumber();
            $currentPage = 1;
            while ($currentPage <= $pageCount) {
                $collection->setCurPage($currentPage);
                foreach ($collection as $product) {
                    $product->setStoreId($storeId);
                    $product->setData('save_rewrites_history', $saveRewriteHistory);
                    /*$mergeDataProvider->merge(
                        $this->productUrlRewriteGenerator->generate($product, $category->getEntityId())
                    );*/
                }
                $collection->clear();
                $currentPage++;
            }
        } else {
            $mergeDataProvider->merge(
                $this->getCategoryProductsUrlRewrites(
                    $category,
                    $storeId,
                    $saveRewriteHistory,
                    $category->getEntityId()
                )
            );
        }

        $productids = $category->getAffectedProductIds();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();        
        $tableName1 = $resource->getTableName('tatva_product_category_remove');
        $found = 0;

        if(!empty($productids))
		{
	        foreach($productids as $key => $productid)
	        {
	            $sql = "Select product_id FROM " . $tableName1." Where status = 1 and product_id=".$productid;
	            $result = $connection->fetchAll($sql);
	            if(!empty($result))
	            {
	                $found = 1;
	            }
	        }
	    }
        
        if(empty($found))
        {
            foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
                $mergeDataProvider->merge(
                    $this->getCategoryProductsUrlRewrites(
                        $childCategory,
                        $storeId,
                        $saveRewriteHistory,
                        $category->getEntityId()
                    )
                );
            }
        }
        
        return $mergeDataProvider->getData();
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @param int|null $rootCategoryId
     * @return array
     */
    private function getCategoryProductsUrlRewrites(
        Category $category,
        $storeId,
        $saveRewriteHistory,
        $rootCategoryId = null
    ) {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();

        $productCollection->addCategoriesFilter(['eq' => [$category->getEntityId()]])
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        foreach ($productCollection as $product) {
            if (isset($this->isSkippedProduct[$category->getEntityId()]) &&
                in_array($product->getId(), $this->isSkippedProduct[$category->getEntityId()])
            ) {
                continue;
            }
            $this->isSkippedProduct[$category->getEntityId()][] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            /*$mergeDataProvider->merge(
                $this->categoryBasedProductRewriteGenerator->generate($product, $rootCategoryId)
            );*/
        }

        return $mergeDataProvider->getData();
    }
}