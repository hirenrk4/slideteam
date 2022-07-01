<?php
namespace Tatva\Freesamples\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of List
 *
 * @author om
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $catalogProductAttributeSourceStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;
    protected $_registry;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Tatva\Subscription\Model\ResourceModel\Shareanddownloadproducts\CollectionFactory $shareAndDownloadCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_catalogProductFactory = $catalogProductFactory;
        $this->_catalogCategoryFactory = $catalogCategoryFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_catalogSession = $catalogSession;
        $this->_storeManager = $storemanager;
        $this->_productVisibility = $productVisibility;
        $this->_productStatus = $productStatus;
        $this->_scopeConfig= $scopeConfig;
        $this->_shareAndDownloadCollectionFactory = $shareAndDownloadCollectionFactory;
        $this->_registry = $registry;
        parent::__construct(
            $context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,
            $data
        );
    }
  
    
    public function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
        $breadcrumbs->addCrumb(
            'freesamples', 
            ['label'=>'Free Samples', 
            'title'=>'Free Samples'
            ]
            );
    }
	
    protected function _getProductCollection()
    {
        $new_collection = $this->_shareAndDownloadCollectionFactory->create();

        foreach ($new_collection->getData() as $value) {
            $product_id[] = $value['product_id'];
        }

        $collection = $this->_catalogResourceModelProductCollectionFactory->create()
                ->addAttributeToSelect("url_path")
                ->addAttributeToSelect("blog_free")
                ->addAttributeToFilter('blog_free',array(array('null'=>true),array('neq'=>1)),'left')
                ->setPageSize($this->get_prod_count())
                ->setCurPage($this->get_cur_page());

        $collection->getSelect()
            ->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (" . implode(",", $this->_productVisibility->getVisibleInSiteIds()) . ")  AND cat_index.category_id='2' ", [])
            ->columns('cat_index.position AS cat_index_position')
            ->where('e.entity_id NOT IN (' . implode(",", $product_id) . ')')
            ->where('e.free = 1')
            ->order('e.number_of_downloads DESC');

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );
        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','e.name','e.short_description','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));

        if (!empty($this->getRequest()->getParam('c')))
        {
            $category = $this->_catalogCategoryFactory->create()->load($this->getRequest()->getParam('c'));
            if (is_object($category) && $category->getId() != "")
                $collection->addCategoryFilter($category);
        }
        
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }
    
    public function getconfigValue($params){
       return $this->_scopeConfig->getValue($params);
    }

    public function get_prod_count()
    {
        //unset any saved limits
        $prod_per_page = $this->_scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->_catalogSession->unsLimitPage();
        return (!empty($this->getRequest()->getParam('product_list_limit'))) ? intval($this->getRequest()->getParam('product_list_limit')) : $prod_per_page;
    }

    // get_prod_count
    public function get_cur_page()
    {
        return (!empty($this->getRequest()->getParam('p'))) ? intval($this->getRequest()->getParam('p')) : 1;
    }

    // get_cur_page
    public function getAllCategory(){
        $collection = $this->_catalogCategoryFactory->create()->getCollection()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('show_in_frontend_listing', 1)
                                ->addAttributeToFilter('level',2)
                                ->addIsActiveFilter()
                                ->addOrderField('position');
        return $collection;
    }
}

?>