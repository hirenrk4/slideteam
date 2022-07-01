<?php
namespace Tatva\NewProduct\Block;

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

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;
    protected $_registry;

    public function __construct(
          \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
         array $data = []
    ) {
        $this->catalogProductFactory = $catalogProductFactory;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->catalogSession = $catalogSession;
        $this->storeManager = $storeManager;
        $this->_productVisibility = $productVisibility;
        $this->_productStatus = $productStatus;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->_scopeConfig= $scopeConfig;
        $this->request = $request;
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
            'newproduct', 
            ['label'=>'Newly Added', 
            'title'=>'Newly Added'
            ]
            );
    }

    public function getcategoryById($categoryId)
    {
        return $this->catalogCategoryFactory->create()->load($categoryId);
    }
    
    public function getProductById($productId)
    {
        return $this->catalogProductFactory->create()->load($productId);
    }
    
    public function getconfigValue($params){
        return $this->_scopeConfig->getValue($params);
    }

    protected function _getProductCollection()
    {
        $categoryId = "";
        
        $prevmonthDate = date("Y-m-d",strtotime(date("Y-m-d",strtotime(date('Y-m-d')))." -10 month"));
        $limit = $this->scopeConfig->getValue("newproduct_options/max_size/max_size_newproduct", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
        
        $storeId = $this->_getStoreId();
        $product = $this->catalogProductFactory->create();
        $categoryId = $this->request->getParam('c');

        if(empty($categoryId))
        {
            $registry = $this->_registry->unregister('current_category');
            
            $rootcategoryId = 2;
            $current_cat = $this->getcategoryById($rootcategoryId);
            $this->_registry->register('current_category', $current_cat);
        }
        
        $collection = $product->setStoreId($storeId)->getResourceCollection();
        $collection->getSelect()->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (" . implode(",", $this->_productVisibility->getVisibleInSiteIds()) . ")  AND cat_index.category_id='2' ", [])
            ->columns('cat_index.position AS cat_index_position');
            
        $collection->getSelect()->where("e.created_at >= '".$prevmonthDate."'");

        $collection->addAttributeToSort('entity_id', 'desc')           
            ->setPageSize($this->get_prod_count())
            ->setMaxSize($limit)
            ->setCurPage($this->get_cur_page());

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );
        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','cat_index.position AS cat_index_position','e.name','e.short_description','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));

        if (isset($categoryId)) {
            $catcollection = $this->getcategoryById($categoryId);
            $collection->addCategoryFilter($catcollection);
        }
        else
        {
            //$resumecategoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
            $ebookcategoryId = $this->scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            //$catalog_ids = [$resumecategoryId,$ebookcategoryId];
            $catalog_ids = [$ebookcategoryId];
            $collection->addCategoriesFilter(array('nin' => $catalog_ids));
        }
        $this->_productCollection = $collection;
        return $this->_productCollection;
    }

    public function get_prod_count()
    {
        $limit="";
        //unset any saved limits
        $prod_per_page = $this->scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->catalogSession->unsLimitPage();
        $limit=($this->request->getParam('product_list_limit'));
        return (isset($limit)) ? intval($limit) : $prod_per_page;
    }

    // get_prod_count
    public function get_cur_page()
    {
        $page="";
        $page=($this->request->getParam('p'));
        return (isset($page)) ? intval($page) : 1;
    }

    // get_cur_page
    protected function _getStoreId()
    {
        $storeId = null;
        if ($storeId == null)
        {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    public function get_recommended_template(){

        $_categoryId = $this->scopeConfig->getValue('button/recommended_premium_templates/recommended_premium_templates_field2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $_productlimit = 2 * $this->scopeConfig->getValue('button/recommended_premium_templates/recommended_premium_templates_field3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productCollection = $this->catalogResourceModelProductCollectionFactory->create() 
                            ->setPageSize($_productlimit)
                            ->setCurPage($this->get_cur_page())
                            ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left') 
                            ->addAttributeToSelect('*') 
                            ->addAttributeToFilter('category_id', array('in' => $_categoryId));
                            //->addAttributeToSort("entity_id", "desc");
        return $productCollection;
    }

}

?>