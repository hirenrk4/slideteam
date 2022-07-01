<?php
namespace Tatva\Wishlist\Block\Wishlist\Customer\Wishlist;

class Items extends \Magento\Wishlist\Block\Customer\Wishlist\Items
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    protected $_imageHelper;
    
    protected $_productloader;
    
    protected $blockFactory;
    
    protected $pageConfig;
    
    protected $wishListItemCollection;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistWishlistFactory;

    protected $pager;

    protected $wishlistHelper;

    protected $subscriptionmodel;

    protected $scopeConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Helper\Image $_imageHelper,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Tatva\Subscription\Model\Subscription $subscriptionmodel,
        \Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionFactory $productDownloadHistoryLogFactory
        ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->wishlistWishlistFactory = $wishlistWishlistFactory;
        $this->_productloader = $_productloader;
        $this->_imageHelper = $_imageHelper;
        $this->_blockFactory = $blockFactory;
        $this->_pageConfig = $pageConfig;
        $this->wishlistHelper = $wishlistHelper;
        $this->scopeConfig = $scopeConfig;
        $this->subscriptionmodel = $subscriptionmodel;
        $this->productDownloadHistoryLogFactory = $productDownloadHistoryLogFactory;

    }

    public function _prepareLayout() {
        parent::_prepareLayout();
        $this->_pageConfig->getTitle()->set(__('My Favourites'));
        $pager = $this->getLayout()->createBlock(
            '\Tatva\Downloadscount\Block\Pager',
            'downloadscount.items.pager');
        $pager->setAvailableLimit(array(4=>4,8=>8,12=>12,16=>16)); 
        $customerId = $this->customerSession->getCustomerId();
        $wishlist = $this->wishlistWishlistFactory->create()->loadByCustomerId($customerId); 
        $this->wishListItemCollection = $wishlist->getcustomerItemCollection();      
        $pager->setCollection($this->wishListItemCollection);
        $this->setChild('pager', $pager);
        $this->wishListItemCollection->load();
        return $this;
    }

    public function favourites(){
        return $this->wishListItemCollection;
    }

    public function getToolbarHtml(){
        return $this->getChildHtml('pager');
    }

    public function getLoadProduct($storeid,$product_id)
    {
        return $this->_productloader->create()->setStoreId($storeid)->load($product_id);
    }

    public function init($productData,$image_id,$attributename=[]){
        $attributenameArray[]=$attributename;
        return $this->_imageHelper->init($productData,$image_id,$attributenameArray);
    }

    public function getWishlistItems($item)
    {
        return $this->wishlistHelper->getRemoveParams($item);
    }

    public function getCustomerId()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            return $customerId;
        }
    }

    public function getCustomerDownloadCount() 
    {

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

    public function getSystemValue($path)
    {
        return $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
    }

}
