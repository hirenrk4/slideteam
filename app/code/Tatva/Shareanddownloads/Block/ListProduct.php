<?php
namespace Tatva\Shareanddownloads\Block;

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
            'shareanddownloads', 
            ['label'=>'Share and Download for Free', 
            'title'=>'Share and Download for Free'
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
        $this->_productCollection = $this->getCatShareandDownloadCollection($category_id, $this->get_prod_count(), $this->get_cur_page());
        return $this->_productCollection;
    }

    public function getCatShareandDownloadCollection($category = 0, $pagesize = 0, $cur_page = 0)
    {
        $collection = $this->_catalogResourceModelProductCollectionFactory->create()
            ->addAttributeToSelect("url_path")
            ->addAttributeToSelect("blog_free")
            ->addAttributeToFilter('blog_free',array(array('null'=>true),array('neq'=>1)),'left')
            ->setPageSize($this->get_prod_count())
            ->setCurPage($this->get_cur_page());

        $collection->getSelect()
            ->join(["cat_index" => $collection->getTable("catalog_category_product_index_store1")], "cat_index.product_id=e.entity_id AND cat_index.store_id=1 AND cat_index.visibility IN (" . implode(",", $this->_productVisibility->getVisibleInSiteIds()) . ")  AND cat_index.category_id='2' ", [])
            ->join(array("shareanddownload" => "mcsshareanddownloadproducts"), "shareanddownload.product_id = e.entity_id", array())
            ->columns('cat_index.position AS cat_index_position')
            ->order('e.number_of_downloads DESC');

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr(ProductStatus::STATUS_ENABLED)]
        );
        $collection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','cat_index.position AS cat_index_position','e.name','e.short_description','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));

        if (is_object($category) || $category > 0) {
            if (!is_object($category))
                $category = $this->_catalogCategoryFactory->create()->load($this->request->getParam('c'));

            if (is_object($category) && $category->getId() != "")
                $collection->addCategoryFilter($category);
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

}
?>