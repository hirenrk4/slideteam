<?php

namespace Tatva\Catalog\Block\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface; 


class View extends \Magento\Catalog\Block\Product\View
{

    const ROUTE_ACCOUNT_LOGIN = 'customer/account/login';
    const REFERER_QUERY_PARAM_NAME = 'referer';
    const XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD = 'customer/startup/redirect_dashboard';
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @deprecated 101.1.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Tatva\Subscription\Model\Mysql4\Productdownloadhistory\CollectionFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistWishlistFactory;
    protected $urlBuilder;

    protected $request;
    /**
     * @var \Magento\Framework\Registry
     */
    
    /**
     * [$_registry]
     * @var [type]
     */
    protected $_registry;

    protected $_catalogSession;
    
    /**
     * @var \Tatva\Subscription\Model\Subscription
     */
    protected $subscriptionmodel;

    protected $popupModel;

    protected $mostDownload;
    private $_resourceConnection;
    protected $catalogCategoryFactory;
    protected $langFactory;

    protected $traslatedata;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Session $catalogSession,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        \Tatva\Categoryid\Model\Product $productCollection,
        \Tatva\Subscription\Model\Subscription $subscriptionmodel,
        \Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionFactory $productDownloadHistoryLogFactory,
        \Tatva\Popup\Model\Popup $popupModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Tatva\Translate\Model\LanguageFactory $langFactory,
        \Tatva\Translate\Model\Translatedata $traslatedata,
        array $data = []
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_productHelper = $productHelper;
        $this->urlEncoder = $urlEncoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productTypeConfig = $productTypeConfig;
        $this->string = $string;
        $this->_localeFormat = $localeFormat;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
        $this->wishlistWishlistFactory = $wishlistWishlistFactory;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->_productCollection = $productCollection;
        $this->_registry = $registry;
        $this->_catalogSession = $catalogSession;
        $this->setCurrentProduct();
        $this->storeManager = $storeManager;
        $this->subscriptionmodel = $subscriptionmodel;
        $this->productDownloadHistoryLogFactory = $productDownloadHistoryLogFactory;
        $this->popupModel = $popupModel;
        $this->_resourceConnection = $resourceConnection;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->langFactory = $langFactory;
        $this->traslatedata = $traslatedata;

        parent::__construct(
          $context,$urlEncoder,$jsonEncoder,$string,$productHelper,$productTypeConfig,$localeFormat,$customerSession,
          $productRepository,$priceCurrency,
          $data
      );

    }

    public function getSystemValue($path)
    {
        return $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setCurrentProduct()
    {
        $currentProduct = null;
        if ( $currentProduct == null){return;}
        $this->_catalogSession->setData('productId',$currentProduct->getId());
    }


    public function getLoginUrl($download='',$wishlist='',$prd_id="")
    {
        return $this->urlBuilder->getUrl(self::ROUTE_ACCOUNT_LOGIN, $this->getLoginUrlParams($download,$wishlist,$prd_id));
    }   


    public function getLoginUrlParams($download='',$wishlist='',$prd_id="")
    {
        $params = [];
        $query = ['download' => $download, 'wishlist' => $wishlist,'prd_id'=> $prd_id];
        $referer = $this->request->getParam(self::REFERER_QUERY_PARAM_NAME);
        if (!$referer && !$this->scopeConfig->isSetFlag(self::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,ScopeInterface::SCOPE_STORE) && !$this->customerSession->getNoReferer()) {
            $referer = $this->urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
            $referer = $this->urlEncoder->encode($referer);
        }

        if ($referer) {
            $params = [self::REFERER_QUERY_PARAM_NAME => $referer];
        }
        return $params;
    }
    

    public function getProductCollection()
    {
        $collection = $this->_productCollection->getCategories();

        return $collection;
    }

    public function getcategoryById($categoryId)
    {
        return $this->catalogCategoryFactory->create()->load($categoryId);
    }

    public function getCurrentCategory()
    {
        $currentCategory = $this->_registry->registry('current_category');

        if(empty($currentCategory))
        {
            $rootcategoryId = 2;
            $current_cat = $this->getcategoryById($rootcategoryId);
            $this->_registry->register('current_category', $current_cat);
            return $current_cat;
        }

        return $currentCategory;
    }
    
    public function getCurrentProduct()
    {       
        return $this->_registry->registry('current_product');
    }

    /**
     * [getDownloadLink ]
     * @return [type] [download url for products]
     */
    public function getDownloadLink(){
        $downloadLink = "downloadable/index/download";
        if($this->isProductShareAndDownloadable()){
            $downloadLink = "downloadable/index/download/share/1";
        }
        return $downloadLink ;
    }


    /**
     * [getDownloadBtnText : download button text]
     * @return [string] 
     */
    public function getDownloadBtnText()
    {
        $product = $this->getCurrentProduct();
        $categoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        $brochurecategoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $onepageIds = $this->_registry->registry("onepage_cat_ids");
        $documentreportIds = $this->_registry->registry("documentreport_cat_ids");
        $ukraincrisisIds = $this->_registry->registry("ukraincrisis_cat_ids");
        $letterheadIds = $this->_registry->registry("letterhead_cat_ids");
        $edutechIds = $this->_registry->registry("edutech_cat_ids");

        if(empty($onepageIds))
        {
            $onepagecategoryId = $this->scopeConfig->getValue('resume/general/onepagecategoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$onepagecategoryId;
            $results = $connection->fetchAll($sql);
            $onepageIds = explode(',',$results[0]['category_list']);
            $this->_registry->register("onepage_cat_ids",$onepageIds);
        }

        if(empty($documentreportIds))
        {
            $docreportcategoryId = $this->scopeConfig->getValue('resume/general/document_report_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$docreportcategoryId;
            $results = $connection->fetchAll($sql);
            $docReportIds = explode(',',$results[0]['category_list']);
            $this->_registry->register("documentreport_cat_ids",$docReportIds);
        }

        if(empty($ukraincrisisIds))
        {
            $ukraincrisiscategoryId = $this->scopeConfig->getValue('resume/general/ukrain_crisis_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$ukraincrisiscategoryId;
            $results = $connection->fetchAll($sql);
            $ukrainReportIds = explode(',',$results[0]['category_list']);
            $this->_registry->register("ukraincrisis_cat_ids",$ukrainReportIds);
        }

        if(empty($letterheadIds))
        {
            $letterheadcategoryId = $this->scopeConfig->getValue('resume/general/letterhead_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$letterheadcategoryId;
            $results = $connection->fetchAll($sql);
            $letheadIds = explode(',',$results[0]['category_list']);
            $this->_registry->register("letterhead_cat_ids",$letheadIds);
        }

        if(empty($edutechIds))
        {
            $edutechcategoryId = $this->scopeConfig->getValue('resume/general/edutech_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$edutechcategoryId;
            $results = $connection->fetchAll($sql);
            $eduIds = explode(',',$results[0]['category_list']);
            $this->_registry->register("edutech_cat_ids",$eduIds);
        }

        $catIds = $product->getCategoryIds();
        if(in_array($categoryId,$catIds))
        {
            $text = "Download This Resume";
        }
        elseif(in_array($brochurecategoryId,$catIds))
        {
            $text = "Download This Brochure";   
        }
        elseif(array_intersect($onepageIds,$catIds))
        {
            $text = "Download This Document";   
        }
        elseif (array_intersect($documentreportIds, $catIds)) 
        {
            $text = "Download This Document";   
        }
        elseif (array_intersect($ukraincrisisIds, $catIds)) 
        {
            $text = "Download This Document";   
        }
        elseif (array_intersect($letterheadIds, $catIds))
        {
            $text = "Download This Letterhead";
        }
        elseif (array_intersect($edutechIds, $catIds))
        {
            $text = "Download Now";
        }
        else{
            $text = "Download this presentation";
        }
        // if(!$this->customerSession->isLoggedIn()){ /* as per slideteam bug tracking trell ticket */
        //     $text = "Log in & Download Now ";
        // }
        return $text;
    }

    /**
     * @return boolean true|false as product is Share & Downloadable or not 
     */
    public function isProductShareAndDownloadable(){
        $flag = false;
        $flag = $this->getRequest()->getParam('share') != null ? true : false;
        return $flag;
    }


    /**
     * @return boolean true|false as product is Share & Downloadable or not 
     */
    public function isProductFree(){
        $flag = false;
        $free = $this->getProduct()->getFree();
        
        if($free != null){
            $flag = $free;
        }
        return $flag;
    }

    public function getShowRecommandedPopup()
    {
        $flag = false;
        $isProductFree = $this->isProductFree();
        $shareableProduct = $this->isProductShareAndDownloadable();
        
        $customerLogin = $this->customerSession->isLoggedIn();
        if($isProductFree && $customerLogin && ($shareableProduct == false) ){
            $flag = true;
        }
        return $flag;
    }
    
    /**
     * @return boolean true|false as customer can download product or not
     */
    public function getshowDownloadLimitExahustedPopup()
    {
        $flag = false;
        $shareableProduct = $this->isProductShareAndDownloadable();
        $current_product_id = $this->getCurrentProduct()->getId();
        $downloadlimitExhausted = $this->subscriptionmodel->productCanBeDownloaded($current_product_id,$shareableProduct);
        if($downloadlimitExhausted == 3){
            $flag=true;
        }
        return $flag;
    }

    
    /**
     * @return current product's download count
     */
    public function getCurProductDownCount(){

        $download_count = 0;
        $customer_id = $this->customerSession->getCustomerId();
        $productdownlaodcollection = $this->productDownloadHistoryLogFactory->create()->addFieldToFilter('customer_id',$customer_id);
        $productdownlaodcollection->getSelect()->columns("sum(download_count) as final_download_count")
        ->group("main_table.customer_id");
        if(is_object($productdownlaodcollection) && $productdownlaodcollection->getSize()>0){
            foreach($productdownlaodcollection as $productdownload)
            {
                $download_count = $productdownload->getData("final_download_count");
            }
        }

        return $download_count;
    }

    public function getCustomerDownloadCount() {

        $download_count = 0;
        $customer_id = $this->customerSession->getCustomerId();

        $customerType = $this->subscriptionmodel->getCustomerType($customer_id);
        if($customerType == 'Free')
        {
            $productdownlaodcollection = $this->productDownloadHistoryLogFactory->create()->addFieldToFilter('customer_id',$customer_id);
            $productdownlaodcollection->getSelect()
                ->columns("COUNT(DISTINCT product_id) as final_download_count")
                ->group("main_table.customer_id");
            
            if(is_object($productdownlaodcollection) && $productdownlaodcollection->getSize()>0){
                foreach($productdownlaodcollection as $productdownload)
                {
                    $download_count = $productdownload->getData("final_download_count");
                }
            }
        }
        
        return $download_count;
    }

    /**
    *   @return popup content for download btn
    */
    public function getPopupContent($identifier)
    {
        $popupData = null;
        $popupCollection = $this->popupModel->getCollection()->addFieldToFilter('identifier',$identifier)->addFieldToFilter('status','1');
        if($popupCollection->getSize() > 0) 
        {
            foreach ($popupCollection as $item) {
                $popupData = $item->getData();
            }
        }
        return $popupData;
    }

    /**
     * Need to work for this
     * @return captcha code status
     */
    public function getCaptchaCodeStatus()
    {
        $captcha_status = 0;
        if($this->customerSession->isLoggedIn())
        {
            $customer_id = $this->customerSession->getCustomerId();
            $download_count = $this->subscriptionmodel->getNumberOfProductsDownloaded(null, $customer_id);
            $captcha_max_admin = $this->scopeConfig->getValue('subscription_options/captcha/captcha_max', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore());
            $customer = $this->_customerRepositoryInterface->getById($customer_id);
            
            $disable_captcha_by_admin = $customer->getCustomAttribute('deactivate_captcha') ? $customer->getCustomAttribute('deactivate_captcha')->getValue() : "0";
            
            if($download_count != '' && $download_count >= $captcha_max_admin)
            {
                if(isset($disable_captcha_by_admin) && ($disable_captcha_by_admin!="") && (!empty($disable_captcha_by_admin)))
                {
                    $captcha_status = 0;
                }
                else
                {
                    $captcha_status = 1;
                }
            }
            else
            {
                $captcha_status = 0;
            }
        }
        $this->customerSession->setCaptchaStatus($captcha_status);
        return $captcha_status;
    }


    /**
     * @return product is added to wishlist
     */

    public function checkWishlist(){
        $customerId = $this->customerSession->getCustomerId();
        if($customerId)
        {
            $wishlist = $this->wishlistWishlistFactory->create()->loadByCustomerId($customerId);
            $wishListItemCollection = $wishlist->getItemCollection();
            $product = $this->_registry->registry('current_product');
            $id = $product->getId();                
            $storeId = $this->storeManager->getStore()->getStoreId();
            $wishListItemCollection->addFieldToFilter('product_id',$id);
            $wishListItemCollection->addFieldToFilter('store_id',$storeId);
            if($wishListItemCollection->getSize()){
               return 1;
           }else{                    
               return 0;
           }                
       }
   }

    /**
     * @return wishlist_item_id of product
     */
    public function removeWishlistproduct($product_id){
        $customer = $this->customerSession->getCustomerId();
        $storeId = $this->storeManager->getStore()->getStoreId();
        $wishlist = $this->wishlistWishlistFactory->create()->loadByCustomerId($customer);
        $wishListItemCollection = $wishlist->getcustomerItemCollection()
        ->addFieldToSelect('wishlist_item_id')
        ->addFieldToFilter('product_id',$product_id)
        ->addFieldToFilter('store_id',$storeId);
        foreach($wishListItemCollection as $wish){
            $id = $wish->getWishlistItemId();
        }
        return $id;
    }

    public function getCustomerSession()
    {
        return $this->_catalogSession;
    }

    public function getStoreConfigValue()
    {
        return $this->scopeConfig;
    }

    public function getLangauges()
    {
        $collection = $this->langFactory->create()->getCollection();
        $size = sizeof($collection);
        if($size != 0)
        {
            $lang_array = array();
            $i=0;
            foreach ($collection as $kay=>$value) 
            {
              $lang_array[$i]['laguage'] = $value['laguage'];
              $i++;
            }
        }
        return $lang_array;
    }

    public function getCurrentlangdata()
    {
        $lang = $this->request->getParam('lang');
        $product_id = $this->getCurrentProduct()->getId();
        $translatedata = $this->traslatedata->getTraslatedata($product_id, $lang);
        //$this->_catalogSession->setlang($lang);
        $arrayvalue = array_column($translatedata, 'value', 'attribute_id');

        return $arrayvalue;
    }

    public function getCustomerSessData(){
        return $this->customerSession;
    }

}

