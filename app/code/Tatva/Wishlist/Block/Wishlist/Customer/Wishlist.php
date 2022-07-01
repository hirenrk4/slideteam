<?php
namespace Tatva\Wishlist\Block\Wishlist\Customer;

class Wishlist extends \Magento\Wishlist\Block\Customer\Wishlist
{

    protected $_wishlistData = null;

    protected $wishlistWishlistFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Wishlist\Helper\Rss $wishlistRss,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        array $data=[]
        ) {
        parent::__construct($context,$httpContext,$helperPool,$currentCustomer,$postDataHelper,$data);
        $this->pageConfig = $pageConfig;
        $this->_wishlistData = $wishlistData;
        $this->_wishlistRss = $wishlistRss;
        $this->wishlistWishlistFactory = $wishlistWishlistFactory;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Favourites'));
    }

    public function isAllow(){
        return $this->_wishlistData->isAllow();
    }

    public function isRssAllow(){
        return $this->_wishlistRss->isRssAllow();
    }

    public function getRssUrl(){
        return $this->_wishlistRss->getRssUrl();
    }

    public function favourites(){
        $customer = $this->currentCustomer->getCustomerId();
        if($customer)
        {
            $wishlist = $this->wishlistWishlistFactory->create()->loadByCustomerId($customer);
            $wishListItemCollection = $wishlist->getcustomerItemCollection();
            if($wishListItemCollection->getSize()){
              return 1;
            }else{                    
              return 0;
            }
        }
    }


}
