<?php
namespace Tatva\Bestsellers\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class ListHome extends \Magento\Catalog\Block\Product\ListProduct
{

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        GalleryReadHandler $readHandler,
        array $data = []
    )
    {
        $this->_catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->_productVisibility = $productVisibility;
        $this->_productFactory = $productFactory;
        $this->readHandler = $readHandler;

        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
    }  

    public function _getProductCollection()
    {
        $limit = $this->getData('custom_limit');
        $offset = $this->getData('custom_offset');
        $collection = $this->_catalogResourceModelProductCollectionFactory->create();
        $collection->getSelect()
        ->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (". implode(",", $this->_productVisibility->getVisibleInSiteIds()).")  AND cat_index.category_id='2' ", [])
        ->columns('cat_index.position AS cat_index_position')
        ->where('e.number_of_downloads >= 0.1')
        ->where('e.free != 1')
        ->order('number_of_downloads DESC');

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );

        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','e.name','e.short_description','e.image','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));
        $collection->getSelect()->limit($limit,$offset);
        
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    protected function _beforeToHtml()
    {
        $collection = [];
        return $collection;
    }

    public function addGallery($product){
        $this->readHandler->execute($product);
    }
}
?>