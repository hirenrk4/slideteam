<?php 
namespace Tatva\Catalog\Model;

class Layer extends \Magento\Catalog\Model\Layer\Category
{
    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context, 
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory, 
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory, 
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, 
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = array()
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $layerStateFactory, $attributeCollectionFactory, $catalogProduct, $storeManager, $registry, $categoryRepository, $data);
    }
    
    public function getProductCollection() 
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()]))
        {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        }
        else
        {
            $collection = $this->getCurrentCategory()->getProductCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        
        //$newly_added = $this->getRequest()->getParam('newly_added');
        $newly_added = isset($_GET['newly_added']) ? $_GET['newly_added'] : '' ;

        if($newly_added == '1'){
            $collection->addAttributeToSort("entity_id", "DESC");
        }
        else{
            $collection->addAttributeToSelect("position")
            ->addAttributeToSort("position", "DESC");    
        }
        
        return $collection;
    }

    public function prepareProductCollection($collection)
    {
        $collection
        ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
//                ->addMinimalPrice()
//                ->addFinalPrice()
//                ->addTaxPercents()
//                ->addStoreFilter()
        ->addUrlRewrite($this->getCurrentCategory()->getId())
        ->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        return $this;
    }
}
?>