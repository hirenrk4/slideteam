<?php
/**
 * @category   Tatva
 * @package    Tatva_Gtm
 */
namespace Tatva\Gtm\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Tatva\Gtm\Helper\Data as Gtmhelper;

/**
 * Class Gtminit used for adding gtm script in before body element
 */
class Gtm extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Checkout\Model\Session
     */
	protected $_checkoutSession;
	/**
     * @var \Magento\Checkout\Model\Session
     */
	protected $_customerSessionFactory;

    protected $_registry;
    protected $_category;

	/**
     * @var ScopeConfigInterface
     */
    protected $_gtmhelper;   
    
    protected $timezone;
    protected $_cmsPage;
    protected $_resourceConnection;	
	protected $_customerSession;
	protected $_connection;
	protected $customerDataHelper;

	function __construct(
        Context $context,
        Gtmhelper $gtmhelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryRepository $gtmcategory,
        \Magento\Framework\App\ResourceConnection $gtmresourceConnection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Cms\Model\Page $gtmcmsPage,       
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Tatva\Gtm\Helper\CustomerData $customerData,
        array $data = []
	)
	{
		parent::__construct($context,$data);
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->_category = $gtmcategory;
        $this->_gtmhelper = $gtmhelper;
        $this->timezone = $timezone;
        $this->_cmsPage = $gtmcmsPage;        
        $this->_resourceConnection = $gtmresourceConnection;
        $this->_customerSession = $customerSession;
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->customerDataHelper = $customerData;
	}

	public function getCustomerLayerData(){
	  $customer = $this->_customerSessionFactory->create();
	  $customerId=$customer->getCustomer()->getId();

	  $customerEmail =  $customer->getCustomer()->getEmail();
	  $originalDate = $customer->getCustomer()->getCreatedAt();
	  $registrationDate = date("Y-m-d", strtotime($originalDate));
	  $customerName = $customer->getCustomer()->getName();
	  
	  $customerData['customerId']=$customerId;
	  $customerData['customerEmail']=$customerEmail;
	  $customerData['registrationDate']=$registrationDate;
	  $customerData['customerName']=$customerName;

	  return json_encode($customerData);
	}
	public function getGtmStatus()
	{
		return $this->_gtmhelper->getGtmStatus();
	}

	public function getGtmCode()
	{
		return $this->_gtmhelper->getGtmCode();
	}

	/**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
	protected function getSuccessOrder()
	{
		return $order = $this->_checkoutSession->getLastRealOrder();
	}

	public function getOrderSuccessData()
	{
		$success_order_data = [];
		$order = $this->getSuccessOrder();
		$transactionTotal = floatval($order->getGrandTotal());
		$transactionTax = floatval($order->getTaxAmount());
		$transactionShipping = floatval($order->getShippingAmount());

		$success_order_data['event'] = "transaction"; 
		$success_order_data['pageType'] = "Order Success";
		$success_order_data['transactionId'] = $order->getIncrementId();
		$success_order_data['transactionTotal'] = $transactionTotal;
		$success_order_data['transactionTax'] = $transactionTax;
		$success_order_data['transactionShipping'] = $transactionShipping;

		$items = $order->getAllVisibleItems();
		foreach ($items as $item) {
			$item_price = floatval($item->getPrice());
			$item_qty = floatval($item->getQtyOrdered());
			$success_order_data['transactionProducts'][] = [
				'sku' => $item->getSku(),
				'name' => $item->getName(),
				'category' => "Subscription",
				'price' => $transactionTotal,
				'quantity' => $item_qty
			];
		}

		return $success_order_data;
	}

	public function getProductLayerData()
	{
		$productlayer_data = [];	
		$productlayer_data['pageType'] = "Product Page"; 
		$current_product = $this->_registry->registry('current_product');

		$category = $current_product->getCategory();		
		
		if($category) {
			$categoryIds = array();
            $categoryName = array();
			$categoryTree = $category->getPath();
			$categoryIds = explode('/', $categoryTree);
            $categoryIds = array_diff($categoryIds,array('1','2'));
            foreach($categoryIds as $_categoryId)
            {
                $categoryName[] = $this->_category->get($_categoryId)->getName();
            }
			switch(count($categoryName))
            {
            	case "1" :
            		$productlayer_data['categoryLevel1'] = $categoryName[0];
            		break;
            	case "2" :
            		$productlayer_data['categoryLevel1'] = $categoryName[0];
            		$productlayer_data['categoryLevel2'] = $categoryName[1];
            		break;
            	case "3" :
            		$productlayer_data['categoryLevel1'] = $categoryName[0];
            		$productlayer_data['categoryLevel2'] = $categoryName[1];
            		$productlayer_data['categoryLevel3'] = $categoryName[2];
            		break;
            }
		} else {
			$category_collection = $current_product->getCategoryIds();
			if (!empty($category_collection)) {
                $categoryId = $category_collection[0];
                $categoryname = $this->_category->get($categoryId)->getName();
                $productlayer_data['categoryLevel1'] = $categoryname;                 
            }  
		}

		$this->_connection = $this->_resourceConnection->getConnection();
		$result = $this->_connection->fetchCol("SELECT DATEDIFF(CURDATE(),created_at) as 'number_of_days' from catalog_product_entity where `entity_id` =" . $current_product->getId());
		
		if (is_array($result) && !empty($result))
		{
			$productDays = $result[0];
		    $product_data_number_of_downloads = (isset($current_product["number_of_downloads"]) == true ? $current_product->getNumberOfDownloads() : 0);		    
		    $total_downloads = round($product_data_number_of_downloads * $productDays);	
		}

 		$originalDate = $this->timezone->formatDate($current_product->getCreatedAt(), \IntlDateFormatter::MEDIUM, false);
 		$newDate = date("Y-m-d", strtotime($originalDate));		

	    $productlayer_data['product']['productId'] = $current_product->getId();
	    $productlayer_data['product']['productName'] = $current_product->getName();
	    $productlayer_data['product']['numberOfDownloads'] = $total_downloads;
	    $productlayer_data['product']['productCreateDate'] = $newDate;
	    $productlayer_data['product']['free'] = $current_product->getFree() == 0 ? "false" : "true" ;

	    return $productlayer_data;
	}

	function getCategoryLayerData($all=false){

		if ($all) {
			$category_all_layer_data = [];	
			$category_all_layer_data['pageType'] = "Category Page"; 
			$categoryId = $this->getRequest()->getParam('c');
			$categoryName = $this->_category->get($categoryId)->getName();
			$category_all_layer_data['categoryLevel1'] = $categoryName;

			return $category_all_layer_data;
		} else {
			$category_layer_data = [];	
			$category_layer_data['pageType'] = "Category Page"; 
			$currentCategory = $this->_registry->registry('current_category');
			if ($currentCategory) {
				$categoryTree = $currentCategory->getPath();
		        $categoryIds = explode('/', $categoryTree);
		        $categoryIds = array_diff($categoryIds,array('1','2'));
		        foreach($categoryIds as $_categoryId)
		        {
		            $categoryName[] = $this->_category->get($_categoryId)->getName();
		        }
		        switch(count($categoryName))
		        {
		        	case "1" :
	            		$category_layer_data['categoryLevel1'] = $categoryName[0];
	            		break;
	            	case "2" :
	            		$category_layer_data['categoryLevel1'] = $categoryName[0];
	            		$category_layer_data['categoryLevel2'] = $categoryName[1];
	            		break;
	            	case "3" :
	            		$category_layer_data['categoryLevel1'] = $categoryName[0];
	            		$category_layer_data['categoryLevel2'] = $categoryName[1];
	            		$category_layer_data['categoryLevel3'] = $categoryName[2];
	            		break;	
		        }
			}
			return $category_layer_data;
		}
	}

	public function getgtmLayerData($type)
	{
		$gtm_layer_data = [];

		if ($type == "bestseller")
		{			
			$gtm_layer_data['pageType'] = "Most Downloaded";
			$categoryId = $this->getRequest()->getParam('c');
			if (isset($categoryId) && $categoryId != "") {
				$current_category = $this->_category->get($categoryId)->getName();
				$gtm_layer_data['categoryLevel1'] = $current_category;
			}			
		}

		if ($type == "newadded") {

			$gtm_layer_data['pageType'] = "Newly Added";
			$categoryId = $this->getRequest()->getParam('c');	
			if (isset($categoryId) && $categoryId != "") {
				$current_category = $this->_category->get($categoryId)->getName();
				$gtm_layer_data['categoryLevel1'] = $current_category;
			}	
		}

		if ($type == "cms") {
			$pageidentifier = $this->_cmsPage->getIdentifier();
			$gtm_layer_data['pageType'] = "cms";
			$gtm_layer_data['pageName'] = $pageidentifier;			
		}
		
		return $gtm_layer_data;	
	}	

	public function getStore()
	{
		 return $this->_storeManager->getStore()->getCode();
	}

	public function isCustomerLoggedIn() {
        return $this->_customerSession->isLoggedIn();
    }
	
}
