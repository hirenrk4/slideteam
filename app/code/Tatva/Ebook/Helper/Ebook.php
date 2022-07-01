<?php
namespace Tatva\Ebook\Helper;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Tatva\Subscription\Model\Subscription;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use \Magento\Payment\Model\Config;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class Ebook extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $listProduct;
	protected $productRepository;
	protected $scopeConfig;
	protected $customerSession;
	protected $subscription;
	protected $orderList;
    protected $product;
    protected $categoryFactory;
    protected $productCollectionFactory;
    protected $resourceConnection;
    protected $connection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ListProduct $listProduct,
        ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Subscription $subscription,
        Order $orderList,
        Product $product,
        Config $paymentModelConfig,
        CategoryFactory $categoryFactory,
        CollectionFactory $productCollectionFactory,
        ResourceConnection $resourceConnection,
        \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $linkCollectionFactory
    ) {
    	$this->listProduct = $listProduct;
    	$this->productRepository = $productRepository;
    	$this->scopeConfig = $scopeConfig;
    	$this->customerSession = $customerSession;
    	$this->subscription = $subscription;
    	$this->orderList = $orderList;
        $this->product = $product;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->_linkCollectionFactory = $linkCollectionFactory;
        parent::__construct($context);
    }

    public function getPostParam($ebook)
    {
        return $this->listProduct->getAddToCartPostParams($ebook);
    }

    public function getGroupEbook()
    {
        $AllEbookSku = $this->scopeConfig->getValue('ebook/general/sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);   
        $productObj = $this->productRepository->get($AllEbookSku);
        return $productObj;
    }

    public function isCustomerSubscribed()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if($customerId == null){
            return null;
        }
        $customers_current_subscription = $this->subscription->getCustomersCurrentSubscription($customerId);
        return $customers_current_subscription;
        // if(!empty($customers_current_subscription)){
        //     $subscription_period = $customers_current_subscription->getData('subscription_period');
        //     if(in_array($subscription_period,array('Annual','Annual + Custom Design','4 user enterprise license'))){
        //         return $customers_current_subscription;
        //     } else {
        //         return null;
        //     }
        // } else {
        //     return null;
        // }
    }

    public function isCustomerPurchased($productId)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $orders = $this->orderList->getCollection()->addFieldToFilter("customer_id", $customerId);
        $products = array();
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $incremetnId = $order->getIncrementId();

                $attrsetid = $item->getProduct()->getAttributeSetId();  
                if($attrsetid == 9)
                {
                    continue;
                }

                $connection = $this->resourceConnection->getConnection();
                $paypal_data = $connection->fetchCol("SELECT COUNT(increment_id) AS 'increment_id' FROM paypal_result where `increment_id` =  '".$incremetnId."'");

                $two_checkout_data = $connection->fetchCol("SELECT COUNT(vendor_order_id) AS 'vendorCount' FROM 2checkout_ins WHERE `vendor_order_id` = '".$incremetnId."' AND `message_type` = 'ORDER_CREATED'");

                if(!empty($paypal_data)) {
                    if($paypal_data[0] >= 1){
                        $products[] = $item->getProductId();
                    }
                }

                if(!empty($two_checkout_data)) {
                    if($two_checkout_data[0] >= 1){
                        $products[] = $item->getProductId();
                    }
                }
                // $products[] = $item->getProductId();
            }
        }
        $product_list = array_unique($products);
        return in_array($productId,$product_list);
    }

    public function loadProductById($productId)
    {
        $productCheck = $this->product->load($productId);    
        return $productCheck;
    }

    public function getSampleFileCollection($productId){
        $collection = $this->_linkCollectionFactory->create();
        $collection->addProductToFilter($productId);
        return $collection;
    }

    public function allProductsArePurchased($collection)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $orders = $this->orderList->getCollection()->addFieldToFilter("customer_id", $customerId);
        $products = array();
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $products[] = $item->getProductId();
            }
        }

        foreach ($collection as $product) { 
            $productCollection[] = $product->getEntityId();
        }

        $product_list = array_unique($products);
        sort($product_list);
        sort($productCollection);
        return $result = count(array_intersect($productCollection, $product_list)) == count($productCollection);
    }


    public function getEbookProductCollection()
    {

        $AllEbookSku = $this->scopeConfig->getValue('ebook/general/sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);   
        $categoryId = $this->scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);  

        $collection = $this->productCollectionFactory->create()
                      ->addCategoriesFilter(['eq' => $categoryId])
                      ->addAttributeToFilter('is_ebook',['eq'=>1])
                      ->addAttributeToFilter('sku',['neq'=> $AllEbookSku])
                      ->addAttributeToFilter('type_id', array('eq' => 'downloadable'))
                      ->addAttributeToSelect('*');
        return $collection;
    }
}