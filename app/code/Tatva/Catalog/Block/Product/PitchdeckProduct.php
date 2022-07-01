<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Catalog\Block\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

/**
 * Product list
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class PitchdeckProduct extends \Magento\Framework\View\Element\Template
{
    protected $imageBuilder;
    protected $readHandler;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Cms\Model\Page $page,
        GalleryReadHandler $readHandler,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        array $data = []
    )
    {
        $this->_catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->_productVisibility = $productVisibility;
        $this->page = $page;
        $this->readHandler = $readHandler;
        $this->imageBuilder = $imageBuilder;
        parent::__construct($context,$data);
    }

    public function getMetaContent()
    {
        return $this->page;
    }

    public function getPitchdeckCollection()
    {   
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 40;

        $collection = $this->_catalogResourceModelProductCollectionFactory->create();
        $collection->getSelect()
        ->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (". implode(",", $this->_productVisibility->getVisibleInSiteIds()).")  AND cat_index.category_id='2537' ", [])
        ->columns('cat_index.position AS cat_index_position');
        
        $collection->getSelect()->order('e.number_of_downloads desc');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );

        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','e.name','e.small_image','e.thumbnail','e.url_key','e.url_path','e.image','e.number_of_downloads'));
        return $collection;
    }

    public function getPitchdeckCollectionInitial()
    {   
        
        $collection = $this->_catalogResourceModelProductCollectionFactory->create();
        $collection->getSelect()
        ->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (". implode(",", $this->_productVisibility->getVisibleInSiteIds()).")  AND cat_index.category_id='2537' ", [])
        ->columns('cat_index.position AS cat_index_position');

        $collection->getSelect()->order('e.number_of_downloads desc');
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );

        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','e.name','e.small_image','e.thumbnail','e.url_key','e.url_path','e.image','e.number_of_downloads'));
        return $collection;
    }

    public function addGallery($product){
        $this->readHandler->execute($product);
    }

    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

}
