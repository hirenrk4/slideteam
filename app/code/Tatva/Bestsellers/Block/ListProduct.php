<?php
namespace Tatva\Bestsellers\Block;

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
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layoutInterface;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
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
       \Magento\Catalog\Model\Product\Visibility $productVisibility,
       \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
       \Magento\Framework\View\LayoutInterface $layoutInterface,
       \Magento\Catalog\Model\Session $catalogSession,
       \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
       \Magento\Store\Model\StoreManagerInterface $storemanager,
       \Magento\Framework\Module\Manager $moduleManager,
       \Magento\Framework\App\Request\Http $request,
       \Magento\Framework\Registry $registry,
       array $data = []
   )
    {
        $this->_catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_catalogProductFactory = $catalogProductFactory;
        $this->_catalogCategoryFactory = $catalogCategoryFactory;
        $this->_layoutInterface = $layoutInterface;
        $this->_catalogSession = $catalogSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storemanager;
        $this->_productVisibility = $productVisibility;
        $this->_productStatus = $productStatus;
        $this->_moduleManager = $moduleManager;
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
                    'bestsellers', 
                  ['label'=>'Most Downloaded', 
                    'title'=>'Most Downloaded'
                ]
            );
    }
     public function getStoreID()
     {
       return $this->_storeManager->getStore()->getId();
     }

    protected function _getProductCollection()
    {       
        $category_id = !empty($this->request->getParam('c')) ? $this->request->getParam('c') : 0;
        $this->_productCollection = $this->getCatMostPopularProductCollection($category_id, $this->get_prod_count(), $this->get_cur_page());
        return $this->_productCollection;
    }

    public function getCatMostPopularProductCollection($category = 0, $pagesize = 0, $cur_page = 0)
    {
        $collection = $this->_catalogResourceModelProductCollectionFactory->create()
            ->setPageSize($this->get_prod_count())
            ->setCurPage($this->get_cur_page());
        
        $collection->getSelect()
        ->join(["cat_index" => $collection->getTable( "catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (". implode(",", $this->_productVisibility->getVisibleInSiteIds()).")  AND cat_index.category_id='2' ", [])
        ->columns('cat_index.position AS cat_index_position')
        ->where('e.number_of_downloads >= 0.1')
        //->where(new \Zend_Db_Expr("e.number_of_downloads >= 0.1 OR e.most_popular >= 1"))
        ->where('e.free != 1')
        //->order(new \Zend_Db_Expr("most_popular DESC,number_of_downloads DESC"));
        ->order('number_of_downloads DESC');
        
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );
        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','e.name','e.short_description','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));

        if (is_object($category) || $category > 0)
        {
            if (!is_object($category))
                $category = $this->_catalogCategoryFactory->create()->load($this->request->getParam('c'));

            if (is_object($category) && $category->getId() != "")
                $collection->addCategoryFilter($category);
        }

        $limit = (int)$this->get_prod_count();
        // we need to set pagination only if passed value integer and more that 0
        
        $popular_widget_status =  $this->_moduleManager->isEnabled("Tatva_Popularwidget");
        if($popular_widget_status == true){
            $popular_widget_block = $this->_layoutInterface->createBlock('popularwidget/popularwidget');
            $popularWidget_flags = $popular_widget_block->getMostDownloadWidgetFlags();
            $flag = $popularWidget_flags['main-page_flag'];
        }
        else{
            $flag = false;
        }

        if($flag){

            $mostdownload_widget_products = $popular_widget_block->getMostDownloadWidget_productNums();
            
            if($this->get_cur_page() == 1){
                if($limit > $mostdownload_widget_products ){
                    $collection->getSelect()->limit($limit-$mostdownload_widget_products);    
                }
                else if($limit == $mostdownload_widget_products){

                    // Total of emarsys products === limit 
                }                    
                else if($limit < $mostdownload_widget_products ){

                    // Total of emarsys products is greater than limit then change the toolbar limit
                }
            }
            else{
                $offset = $limit * ($this->get_cur_page()-1) - $mostdownload_widget_products;
                $collection->getSelect()->limit($limit,$offset);
            }
        }
        else if($flag == false){
            $collection->setPageSize($limit); 
        }
        $this->_productCollection = $collection;
        return $collection;
    }



    public function getMostPopularProductsOfCategory($category = 0, $pagesize = 0, $cur_page = 0)
    {
        $collection = $this->_catalogResourceModelProductCollectionFactory->create()
        ->addAttributeToSelect("number_of_downloads")
        ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
        ->addAttributeToSelect("url_path")
        ->addAttributeToSelect("free")
        ->setPageSize($pagesize)
        ->setCurPage($cur_page)
        ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
        ->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()])
        ->addAttributeToFilter("free", array("neq" => 1))
        ->addAttributeToFilter("number_of_downloads", array("gteq" => 0.1))
        ->addAttributeToSort("number_of_downloads", "desc");
        $collection->addUrlRewrite();
        $collection->getSelect()->joinLeft(array("cat_index1" => "catalog_category_product"), "cat_index1.product_id = e.entity_id", array(''));
        $collection->getSelect()->where('cat_index1.category_id = '.$category);

        if(ceil($collection->getSize()/$pagesize) < $cur_page )
        {
            $collection = "";
        }

        return $collection;
    }

    public function get_prod_count()
    {
        $this->_catalogSession->unsLimitPage();
        $default_limit = $this->_scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != null ? intval($this->_scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) : 60 ;
        $limit = (!empty($this->request->getParam('product_list_limit'))) ? intval($this->request->getParam('product_list_limit')) : $default_limit;

        return $limit;
    }

    public function get_cur_page()
    {
        return (!empty($this->request->getParam('p'))) ? intval($this->request->getParam('p')) : 1;
    }

    public function getConfigration()
    {
        return $this->_scopeConfig;
    }

    public function getCatlogCategoryModel()
    {
        return $this->_catalogCategoryFactory->create();
    }
}
?>