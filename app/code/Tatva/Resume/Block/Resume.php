<?php 
namespace Tatva\Resume\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ResourceConnection;

class Resume extends \Magento\Framework\View\Element\Template
{
	protected $productCollectionFactory;

    protected $categoryFactory;

    protected $scopeConfig;
    
    protected $customerSession;

    protected $orderList;

    protected $resourceConnection;

    protected $connection;

	public function __construct(
        Context $context,        
        CollectionFactory $productCollectionFactory,        
        array $data = [],
        CategoryFactory $categoryFactory,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Order $orderList,
        ResourceConnection $resourceConnection
    )
    {    
        $this->productCollectionFactory = $productCollectionFactory;    
        $this->categoryFactory = $categoryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->orderList = $orderList;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Resume store'));
        return $this;
    }

    public function getResumeProductList()
    {
        $sort = "0";
        if (isset($_GET['sort'])) {
            $sort = $_GET['sort'];
        }
        
           
        $categoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);  
    	$collection = $this->productCollectionFactory->create()
                      ->addCategoriesFilter(['eq' => $categoryId])
		              ->addAttributeToFilter('is_resume',['eq'=>1])                    
                      ->addAttributeToFilter('type_id', array('eq' => 'downloadable'))
                      ->addAttributeToSelect('*');
        
        $joinConditions = 'e.entity_id = downloadable_link.product_id';
        $collection->getSelect()->join(['downloadable_link'],$joinConditions,[])->columns('downloadable_link.sample_file');
        
        if($sort == '1'){
            $collection->setOrder('created_at','DESC');
        } elseif($sort == '2') {            
            $collection->addAttributeToSort('name');
        }

        return $collection;
    }


    public function OrderedProductList()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $orders = $this->orderList->getCollection()->addFieldToFilter("customer_id", $customerId);
        $products = array();
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $incremetnId = $order->getIncrementId();

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
            }
        }
        $product_list = array_unique($products);
        return $product_list;
    }
}
