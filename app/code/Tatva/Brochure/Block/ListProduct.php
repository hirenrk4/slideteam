<?php
namespace Tatva\Brochure\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;


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
    protected $_productImageHelper;

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
        \Magento\Catalog\Helper\Image $productImageHelper,
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
        //$this->_scopeConfig= $scopeConfig;
        $this->request = $request;
        $this->_productImageHelper = $productImageHelper;
         parent::__construct(
            $context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,
            $data
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
        return $this->scopeConfig->getValue($params);
    }

    public function resizeImage($product, $imageId, $width, $height = null)
    {

        $resizedImage = $this->_productImageHelper
                           ->init($product, $imageId)
                           ->constrainOnly(TRUE)
                           ->keepAspectRatio(FALSE)
                           ->keepTransparency(TRUE)
                           ->keepFrame(FALSE)
                           ->resize($width, $height);
        return $resizedImage;
    }    

    protected function _getProductCollection()
    { 
        $categoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        $category = $this->catalogCategoryFactory->create()->load($categoryId); 
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*');
        $categoryProducts->setPageSize($this->get_prod_count())
                      ->setCurPage($this->get_cur_page());
        $categoryProducts->getSelect()->order('entity_id desc');
        $this->_productCollection = $categoryProducts;
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
    public function getBrochureCategory()
    {
        $brochurecategoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $category = $this->getcategoryById($brochurecategoryId);
        return $category;
    }
}

?>