<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tag
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Tatva\Tag\Block\Product;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

/**
 * List of tagged products
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Result extends  \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * Unique Html Id
     *
     * @var string
     */
    protected $_uniqueHtmlId = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Tatva\Tag\Model\TagFactory
     */
    protected $tagTagFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;
    protected $readHandler;

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
        \Magento\Framework\Registry $registry,
        \Tatva\Tag\Model\ResourceModel\Product\Collection $productCollection,
        \Tatva\Tag\Model\ResourceModel\Tag\Collection $tagResourceCollection,
       \Magento\Framework\App\Request\Http $request,
       \Tatva\Tag\Model\TagFactory $tagTagFactory,
       \Magento\Framework\App\Response\Http $response,
       \Magento\Framework\View\Page\Config $pageConfig,
       GalleryReadHandler $readHandler,
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
         $this->tagTagFactory = $tagTagFactory;
          $this->registry = $registry;
          $this->_productCollection = $productCollection;
          $this->_tagResourceCollection = $tagResourceCollection;
         $this->response = $response;
         $this->pageConfig = $pageConfig;
         $this->readHandler = $readHandler;
        parent::__construct(
            $context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,
            $data
        );
    }
    public function _prepareLayout()
    {
      $tag=$this->getTag();
         $tagname = (isset($tag) && is_object($tag))?$tag->getName():"";
        if($tagname!=""){
            $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Home'),
                        'link' => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
            $tagSym=ucfirst(substr($tagname , 0,1 ));
            $currentUrl=$this->getUrl('all-powerpoint-categories');
            $separator = '?';
            $new_Url = rtrim($currentUrl,"/").$separator.'t='.$tagSym;;
            $breadcrumbs->addCrumb('Popular Categories', array('label' => 'Popular Categories'  , 'link' => $this->getUrl('all-powerpoint-categories')) );
            $breadcrumbs->addCrumb('Tagnamesymbol', array('label' => $tagSym ,'link' => $new_Url));
            $breadcrumbs->addCrumb('Tagnames', array('label' => ucfirst($tagname)) );
        }

        $ProductCollection = $this->_getProductCollection();
        $ProductCollection = $ProductCollection->setPageSize(5);
        $ProductCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $ProductCollection->getSelect()->columns(array('e.name'));
        if($ProductCollection->getSize()){
            $productNames = array_column($ProductCollection->getData(),'name');
            $productNames = implode(",",$productNames);
            $productNames = substr($productNames,0,165);
            $this->pageConfig->setDescription($productNames);
        }
    }

    public function getTag()
    {
        
        $arr = explode('/',trim($this->request->getPathInfo(), '/'));
        $tag_id=$this->request->getParam('tagId');

        if($tag_id!="" && $tag_id>0)
        {
            $tag=$this->tagTagFactory->create()->load($tag_id);
            return $tag;
        }
        elseif($this->registry->registry('current_tag'))
        {
            return $this->registry->registry('current_tag');
        }
        elseif(count($arr)==1 && $arr[0]=='tag'){   
            $tag = $this->tagTagFactory->create()->load($arr[0],'name');
            $this->registry->register('current_tag', $tag);     
           return $this->registry->registry('current_tag');
        }
        else
        {
            $norouteUrl = $this->getBaseUrl()."noRoute";
            header("Location: ".$norouteUrl);
            exit;    
        }
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }
     public function getBreadCrums()
    {
        return $this->getChildHtml('breadcrumbs');
    }
      public function getEmarsysListHtml()
    {
        return $this->getChildHtml('search_result_emarsys');
    }
    public function getCustomTaggedProductsUrl($tagName=""){
      return $this->tagTagFactory->create()->getCustomTaggedProductsUrl($tagName);
    }

    protected function _getProductCollection()
    {
            $tagModel = $this->tagTagFactory->create();
            $this->_productCollection = $this->_productCollection
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addTagFilter($this->getTag()->getId())
                ->addStoreFilter($this->_storeManager->getStore()->getId())
               // ->addMinimalPrice()
                ->setPageSize($this->get_prod_count())
                ->setCurPage($this->get_cur_page())
                ->addUrlRewrite()
                ->setActiveFilter();
               
        $this->_productCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->_productCollection->getSelect()->columns(
            ['status' => new \Zend_Db_Expr($this->_productStatus::STATUS_ENABLED)]
        );
        $this->_productCollection->getSelect()->columns(array('e.entity_id','e.attribute_set_id','e.type_id','e.created_at','e.updated_at','e.sku','cat_index.position AS cat_index_position','e.name','e.short_description','e.image','e.small_image','e.thumbnail','e.url_key','e.free','e.number_of_downloads','e.sentence1','e.url_path'));
        $this->_productCollection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
        $this->_productCollection->getSelect()->order('e.number_of_downloads desc');
         return $this->_productCollection;
    }
    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->_getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
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

    public function addGallery($product){
        $this->readHandler->execute($product);
    }

}
