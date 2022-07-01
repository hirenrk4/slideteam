<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
// use Magento\Framework\Serialize\Serializer\Json;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as ResourceWishlist;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;

/**
 * Wishlist model
 *
 * @method int getShared()
 * @method \Magento\Wishlist\Model\Wishlist setShared(int $value)
 * @method string getSharingCode()
 * @method \Magento\Wishlist\Model\Wishlist setSharingCode(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Wishlist\Model\Wishlist setUpdatedAt(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @api
 * @since 100.0.2
 */
class Wishlist extends \Magento\Wishlist\Model\Wishlist
{
     /**
     * Cache tag
     */
     const CACHE_TAG = 'wishlist';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist';

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * Store filter for wishlist
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Shared store ids (website stores)
     *
     * @var array
     */
    protected $_storeIds;

    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistData;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var ItemFactory
     */
    protected $_wishlistItemFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var bool
     */
    protected $_useCurrentWebsite;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Json
     */
    // private $serializer;
    protected $customerCustomerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param ResourceWishlist $resource
     * @param Collection $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ItemFactory $wishlistItemFactory
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param bool $useCurrentWebsite
     * @param array $data
     * @param Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Wishlist\Helper\Data $wishlistData,
        ResourceWishlist $resource,
        Collection $resourceCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Wishlist\Model\ItemFactory $wishlistItemFactory,
        CollectionFactory $wishlistCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        $useCurrentWebsite = true,
        array $data = []
        // Json $serializer = null
        
        ) {
        $this->_useCurrentWebsite = $useCurrentWebsite;
        $this->_catalogProduct = $catalogProduct;
        $this->_wishlistData = $wishlistData;
        $this->storeManager = $storeManager;
        $this->_date = $date;
        $this->_wishlistItemFactory = $wishlistItemFactory;
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->mathRandom = $mathRandom;
        $this->dateTime = $dateTime;
        // $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $catalogProduct,$wishlistData,$resource, $resourceCollection,$storeManager,$date,$wishlistItemFactory,
            $wishlistCollectionFactory,$productFactory,$mathRandom,$dateTime,$productRepository);
        $this->productRepository = $productRepository;
        $this->customerCustomerFactory = $customerCustomerFactory;
    }

    public function getItemCollection()
    {
        if (is_null($this->_itemCollection)) {

            if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
                $customer = $this->customerCustomerFactory->create()->load($this->getCustomerId());
                $this->_itemCollection->setWebsiteId($customer->getWebsiteId());
                $this->_itemCollection->setCustomerGroupId($customer->getGroupId());
            }
            else{
                $currentWebsiteOnly = $this->storeManager->getStore()->getStoreId();
                $this->_itemCollection =  $this->_wishlistCollectionFactory->create()
                ->addWishlistFilter($this)
                ->addStoreFilter($currentWebsiteOnly)
                ->setVisibilityFilter();

            }
        }
        return $this->_itemCollection;
    }
    
    public function getcustomerItemCollection()
    {
        if (is_null($this->_itemCollection)) {
            /** @var $currentWebsiteOnly boolean */
            $currentWebsiteOnly = !($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE);
            $this->_itemCollection =  $this->_wishlistCollectionFactory->create()
            ->addWishlistFilter($this)
            ->addStoreFilter($this->getSharedStoreIds($currentWebsiteOnly))
            ->setVisibilityFilter();

            if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
                $customer = $this->customerCustomerFactory->create()->load($this->getCustomerId());
                $this->_itemCollection->setWebsiteId($customer->getWebsiteId());
                $this->_itemCollection->setCustomerGroupId($customer->getGroupId());
            }
        }

        return $this->_itemCollection;
    }
}