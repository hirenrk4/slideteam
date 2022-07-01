<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Subscription\Model;

use Tatva\Subscription\Api\Data\SubscriptionInterface;
use Tatva\Subscription\Model\Mostdownload;
use Tatva\Subscription\Model\Shareanddownloadproducts;
use Tatva\Catalog\Model\ProductdownloadhistoryFactory;
use Tatva\Catalog\Model\ProductdownloadhistorylogFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Sales\Model\Order;

class Subscription extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'subscription_history';

	const CUSTOMER_SUBSCRIPTION_STATUS_FREE = 'Free';

	const CUSTOMER_SUBSCRIPTION_STATUS_ACTIVE = 'Active';

	const CUSTOMER_SUBSCRIPTION_STATUS_EXPIRED = 'Expired';

	const CUSTOMER_SUBSCRIPTION_STATUS_DOWNLOAD_LIMIT_EXAUSTED = 'Download Limit Reached';

	const SUBSCRIPTION_Payment_STATUS_Paid = 'Expired';

	const SUBSCRIPTION_PAYMENT_STATUS_UNSUBSCRIBED = 'Unsubscribed';

	const SUBSCRIPTION_PAYMENT_STATUS_REQ_UNSUBSCRIBE = 'Requested Unsubscription';

	const SUBSCRIPTION_PAYMENT_STATUS_NOSUBSCRIPTION = 'No Subscription';
	
	protected $_cacheTag = 'subscription_history';

	protected $_eventPrefix = 'subscription_history';

	protected $_customerSession;

	protected $_adminsession;

	protected $_customer_id;

	/**
	 * @var \Tatva\Subscription\Model\SubscriptionhistoryFactory
	 */
	protected $_customer_subscriptions;

	protected $_storeManager;


	/**
	 * @var \Tatva\Subscription\Model\Mysql4\Productdownloadhistorylog\CollectionFactory
	 */
	protected $_cusomer_current_last_sub;

	/**
	 * [$_dateTime]
	 * @var [\Magento\Framework\Stdlib\DateTime\DateTime]
	 */
	protected $_dateAccTimezone;

	/**
	 * [$_mostDownload]
	 * @var [\Tatva\Subscription\Model\Mostdownload]
	 */
	protected $_mostDownload;

	/**
	 * [$_shareAndDownload]
	 * @var [\Tatva\Subscription\Model\Shareanddownloadproducts]
	 */
	protected $_shareAndDownload;


	/**
	 * [$_productdownloadhistoryFactory]
	 * @var [\Tatva\Catalog\Model\ProductdownloadhistoryFactory]
	 */
	protected $_productdownloadhistoryFactory;


	/**
	 * [$_productdownloadhistorylogFactory]
	 * @var [\Tatva\Catalog\Model\ProductdownloadhistorylogFactory]
	 */
	protected $_productdownloadhistorylogFactory;

	/**
	 * [$_downloadableLinkFactory]
	 * @var [\Magento\Downloadable\Model\LinkFactory]
	 */
	protected $_downloadableLinkFactory;


	/**
	 * [$_productFactory]
	 * @var [\Magento\Catalog\Model\ProductFactory]
	 */
	protected $_productFactory;

	/**
	 * [$_productFactory] loaded with product id
	 * @var [\Magento\Catalog\Model\ProductFactory]
	 */
	protected $_currentProduct;

	protected $_subscriptionProductsCollectionFactory;

	protected $_subscriptionProductsCollection;

	protected $_subscriptionProductsIds;

	protected $purchasedProductList;

	public function __construct(
		Order $purchasedProductList,
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Customer\Model\SessionFactory $customerSession,
		\Magento\Sales\Model\Order $orderModel,
		\Tatva\Paypalrec\Model\Result $resultModel,
		\Magento\Backend\Model\SessionFactory $adminsession,
		Timezone $dateAccTimezone,
		Mostdownload $mostDownload,
		Shareanddownloadproducts $shareAndDownload,
		LinkFactory $downloadableLinkFactory,
		ProductdownloadhistoryFactory $productdownloadhistoryFactory,
		ProductdownloadhistorylogFactory $productdownloadhistorylogFactory,
		ProductFactory $productFactory,
		CollectionFactory $productCollectionFactory,
		\Magento\Framework\App\State $state, 
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		$this->purchasedProductList=$purchasedProductList;
		$this->_customerSession = $customerSession;
		$this->_adminsession = $adminsession;
		$this->_dateAccTimezone = $dateAccTimezone;
		$this->_mostDownload = $mostDownload;
		$this->_downloadableLinkFactory = $downloadableLinkFactory;
		$this->_productdownloadhistoryFactory = $productdownloadhistoryFactory;
		$this->_productdownloadhistorylogFactory = $productdownloadhistorylogFactory;
		$this->_subscriptionProductsCollectionFactory = $productCollectionFactory;
		$this->_shareAndDownload = $shareAndDownload ;
		$this->_productFactory = $productFactory;
		$this->_state = $state;
		$this->request=$request;
		$this->_resourceConnection = $resourceConnection;
		$this->_dateFactory = $dateTimeDateTimeFactory;
		$this->_orderModel=$orderModel;
		$this->_resultModel=$resultModel;
		//$this->subscriptionMysql4ProductdownloadhistorylogCollectionFactory = $subscriptionMysql4ProductdownloadhistorylogCollectionFactory;
		parent::__construct(
			$context,
			$registry,
			$resource,
			$resourceCollection,
			$data
		);

	}


	protected function _construct()
	{
		$this->_init('Tatva\Subscription\Model\ResourceModel\Subscription');
	}

	public function getOrderCustomerId()
	{
		return $this->getCustomerId();
	}


	protected function getCustomerId()
	{
		if($this->_state->getAreaCode()=="adminhtml"){
			
			$customerId=$this->request->getParams('customer_id');
			$this->_customer_id =$customerId['customer_id'];
		}
		elseif($this->_customerSession){
			if(!$this->_customer_id){
				$customer = $this->_customerSession->create();
				$this->_customer_id = $customer->getCustomer()->getId();    
			}            
		}
		else{
			return false;
		}
		return $this->_customer_id;
	}
	

	/**
	 * [getCustomerSubscriptions ]
	 * @param  [int] $customer_id 
	 * @return [type]              [collection of customer's subscription]
	 */
	public function getCustomerSubscriptions($customer_id = null)
	{
		if(empty($customer_id)){
			$customer_id = $this->getCustomerId();
		}


		if($customer_id == false){
			return false;
		}
		
		$this->_customer_subscriptions = $this->getCollection()
											->addFieldToFilter('customer_id',$customer_id);
											
		
		return $this->_customer_subscriptions;
	}


	/**
	 * [getCustomersCurrentSubscription] Return customer's last/current subscription
	 * @param  [type] $customer_id [description]
	 * @return [type]              [description]
	 */
	public function getCustomersCurrentSubscription($customer_id = null)
	{
		if(empty($customer_id)){
			$customer_id = $this->getCustomerId();
		}

		if($customer_id == false){
			return false;
		}
		
		$this->getCustomerSubscriptions($customer_id);
		if($this->_customer_subscriptions){
			if($this->_customer_subscriptions->getSize()){
				$customer_current_sub_collection = $this->_customer_subscriptions->setOrder('subscription_history_id','DESC');
				$customer_current_sub_collection->getSelect()->limit(1);
				foreach($customer_current_sub_collection as $current_subscription){
					$this->_cusomer_current_last_sub = $current_subscription;
					return $this->_cusomer_current_last_sub;
					break;
				}    
			}    
		}
		
		return $this->_cusomer_current_last_sub;
	}


	public function getCustomerplan(){
		$_cusomer_current_last_sub = $this->getCustomersCurrentSubscription();
		if($subscription_data){
			foreach($_cusomer_current_last_sub as $subscription_data)
				return $subscription_data->getData("subscription_period");    
		}
		else{
			return false;  
		}
		
	}


	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}


	public function getDefaultValues()
	{
		$values = [];
		return $values;
	}


	public function getNumberOfProductsDownloaded($fromDate,$customer_id = null)
	{
		if(empty($customer_id)){
			$customer_id = $this->getCustomerId();
		}
		if($customer_id == false){
			return false;
		}
		$collection = $this->_productdownloadhistorylogFactory->create()->getCollection();
		$collection->addFieldToFilter('customer_id',$customer_id);
		if(!empty($fromDate)) :
			$collection->addFieldToFilter('download_date',array('gteq'=>$fromDate));
		endif;
		$collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
		$collection->getSelect()->columns('COUNT(DISTINCT(product_id)) as download_count');
		$collectionData = $collection->getData();
		$productsDownloaded = $collectionData[0]['download_count'];
		return $productsDownloaded;
	}


	/**
	 * [getCustomerType return type of customer]
	 * @param  [type] $customer_id [customer_id | get from current customer's session ]
	 * @return self::CUSTOMER_SUBSCRIPTION_STATUS_FREE Customer has not purchase any subscription yet
	 *         self::CUSTOMER_SUBSCRIPTION_STATUS_EXPIRED Customer's subscription is expired                                               
	 */
	public function getCustomerType($customer_id = null)
	{
		if(empty($customer_id)){
			$customer_id = $this->getCustomerId();
		}
		if($customer_id == false){
			return false;
		}
		
		$collection = $this->getCustomersCurrentSubscription($customer_id);
		$current_gmt_date = $this->_dateFactory->create()->gmtDate('Y-m-d');
        $currentDate = strtotime($current_gmt_date);
		$customerType = self::CUSTOMER_SUBSCRIPTION_STATUS_FREE;
		if($collection){
			if($collection->hasData()){
				$collectionData = $collection->getData();
				$toDate = strtotime($collection->getToDate());
				$latestSubscription = $collection->getSubscriptionPeriod();
				if($toDate < $currentDate)
				{
					$customerType = self::CUSTOMER_SUBSCRIPTION_STATUS_EXPIRED;
					return array("customerType" => $customerType, "latestSubscription" => $latestSubscription);
				}
				elseif($toDate >= $currentDate)
				{   
					$fromDate = $collection->getFromDate();
					$downloadLimit = $collection->getDownloadLimit();
					$productsDownloaded = $this->getNumberOfProductsDownloaded($fromDate,$customer_id);
					$customerType = self::CUSTOMER_SUBSCRIPTION_STATUS_EXPIRED;
					if($downloadLimit < 0 || $productsDownloaded < $downloadLimit )
					{
						$customerType = self::CUSTOMER_SUBSCRIPTION_STATUS_ACTIVE;
					}
					elseif($productsDownloaded == $downloadLimit){
						$customerType = self::CUSTOMER_SUBSCRIPTION_STATUS_DOWNLOAD_LIMIT_EXAUSTED;   
					} 
					return array("customerType" => $customerType, "latestSubscription" => $latestSubscription);
				}                
			}			
		}		
		return $customerType;
	}

	public function unsubscribe_id($subscription_history_id)
	{

		if($subscription_history_id != "")
		{
			$subscriptionhistorycollection = $this->load($subscription_history_id);
		}

		$subscription_id="";

		if($subscriptionhistorycollection && $subscriptionhistorycollection->getIncrementId() != "")
		{
			$increment_id=$subscriptionhistorycollection->getIncrementId();
			$orders = $this->_orderModel-> getCollection()
			-> addFieldToFilter("increment_id" , $increment_id);


			$payment = "";
			foreach($orders as $order):
				$payment = $order -> getPayment();
			endforeach;

			if($payment != "" && $payment -> getMethod() == "paypal_standard")
			{

				$paypal_rec = $this->_resultModel-> getCollection()
				-> addFieldToFilter("increment_id" , $increment_id);

				foreach($paypal_rec as $paypal):
					$subscription_id = $paypal -> getPaypalId();
				endforeach;
			}
		}

		return $subscription_id;
	}


	/**
	 * Need to work
	 * [getLastPaidSubscriptionhistory description]
	 * @return [type] [description]
	 */
	public function getLastPaidSubscriptionhistory($customer_id = null)
	{
		if(empty($customer_id))
		{
			$customer_id = $this->getCustomerId();
		}
		if($customer_id == false)
		{
			return false;
		}
		$subscriptionhistory = $this->getCustomersCurrentSubscription($customer_id);
		
		if(is_object($subscriptionhistory) && $subscriptionhistory->getId()>0)
		{
			if(($subscriptionhistory->getStatusSuccess() == "Paid" || $subscriptionhistory->getStatusSuccess() == self::SUBSCRIPTION_PAYMENT_STATUS_REQ_UNSUBSCRIBE)
				&& $subscriptionhistory->getIncrementId() != "")
			{
				if($subscriptionhistory->getUserStatusUnsubscribe()=="1")
				{
					return self::SUBSCRIPTION_PAYMENT_STATUS_UNSUBSCRIBED;
				}
				else
				{
					return $subscriptionhistory;
				}
			}
			else if(is_object($subscriptionhistory) && ($subscriptionhistory->getStatusSuccess() == self::SUBSCRIPTION_PAYMENT_STATUS_UNSUBSCRIBED))
			{
				return self::SUBSCRIPTION_PAYMENT_STATUS_UNSUBSCRIBED;
			}
			else if(is_object($subscriptionhistory) && ($subscriptionhistory->getId() == null))
			{
				return self::SUBSCRIPTION_PAYMENT_STATUS_NOSUBSCRIPTION;
			}
			else if($subscriptionhistory->getAdminModified() == '1' && $subscriptionhistory->getIncrementId() == "")
			{
				return $subscriptionhistory;
			}

			return "";
		}
		else
		{
			return self::SUBSCRIPTION_PAYMENT_STATUS_NOSUBSCRIPTION;
		}
	}

	/**
	 * [productCanBeDownloaded] : Determine that customer can download the product or can purchase new subscription or not
	 * @param  string $product_id_allowed_to_download [description]
	 * @param  string $share                          [description]
	 * @return [int]
	 *  Download limit < 0 => Unlimited Download Allowed.
	 *  Return 0 if customer is not logged in
	 *  Return 1 allow to download
	 *  Return 2 then and then user will be allowed to purchase new subscription || has not any subscription or subscription expired.
	 *  Return 3 customer will not be allowed to purchase new subscription untill he/she request for unsubscribe || download limit reached .
	 *  Return false if $product_id is not there
	 */
	public function productCanBeDownloaded($product_id_allowed_to_download ,$share = "") {
		if($product_id_allowed_to_download > 0){

			$customer_id = $this->getCustomerId();
			if($customer_id == null){
				return 0;
			}
			
			$isProductFree = $this->isProductFree($product_id_allowed_to_download);

			$isProductEbook = $this->isProductEbook($product_id_allowed_to_download);

			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$ebookHelper = $objectManager->create('Tatva\Ebook\Helper\Ebook');
			$allproductCollection = $ebookHelper->getEbookProductCollection();
			$allProductsPurchased = $ebookHelper->allProductsArePurchased($allproductCollection);
			$groupEbook = $ebookHelper->getGroupEbook();	
			$isCustomerPurchasedAllEbook = $ebookHelper->isCustomerPurchased($groupEbook->getEntityId());
			
			$freeDownload = $this->checkFreeProductDownloaded($customer_id);
			$customerType = $this->getCustomerType($customer_id);

			if($isProductFree){
				if($freeDownload == 3 && !is_array($customerType) && $customerType == 'Free')
				{
					return 2;
				}
				return 1;
			}
			elseif($share == true){
				$isShareAndDownloadProduct = $this->_shareAndDownload->isShareAndDownloadProduct($product_id_allowed_to_download);
				
				if(!empty($isShareAndDownloadProduct)){
					return 1;
				}
				else{
					return 2;
				}
			}
			else{
				$customers_current_subscription = $this->getCustomersCurrentSubscription($customer_id);
				if($customers_current_subscription != null){
					$downloaded = $this->checkDownloads($customers_current_subscription, $product_id_allowed_to_download, true);
					
					$product_allowed_to_download = false;
					
					if (is_array($downloaded)) {
						$product_allowed_to_download = $downloaded["allowed"];
						$downloaded = $downloaded["downloaded"];
					}

					$download_limit = $customers_current_subscription->getData("download_limit");
					
					if($product_allowed_to_download){

						//if Ebook then download
						if($isProductEbook == 1){
							return 1;	
						}

						if ($downloaded >= 0) {
							if ($download_limit < 0) {
								return 1;
							} else {
								if ($download_limit > $downloaded)
									return 1;
								else if ($download_limit == $downloaded && ($customers_current_subscription->getData("status_success") == "Unsubscribed" || $customers_current_subscription->getData("status_success") == "Requested Unsubscription") )
									return 2;
								else if ($download_limit == $downloaded)
									return 3;
								else
									return 2;
							}
						} 
					}
					else{
						return 2;                
					}         
				}
				else{
					$alreadyPurchased = $this->CheckProductAlreadyPurchased($product_id_allowed_to_download);
					if($alreadyPurchased == true || $allProductsPurchased == TRUE || $isCustomerPurchasedAllEbook == TRUE) {
						return 1;
					} else{
						return 2;                
					}
				}  
			}
		}   
		else{
			return false;
		}                   
	}
	

	/**
	 * [checkDownloads] : Check customer's download
	 * @param  [type]  $subscription [description]
	 * @param  string  $product_id    [description]
	 * @param  boolean $all           [description]
	 * @return [type]  array               [description]
	 */
	protected function checkDownloads($subscription, $product_id="", $all = false) {
		$downloaded = 0;
		$product_allowed_to_download = false;
		$customer_id = $this->getCustomerId();
		
		if (is_object($subscription) && $subscription->getId() != "") {
			if (!$customer_id) {
				$customer_id = $subscription->getCustomerId();
			}

			//$current_date = strtotime($this->_dateAccTimezone->formatDatetime(date("Y-m-d H:i:s")));
			$current_date = strtotime($this->_dateFactory->create()->gmtDate('Y-m-d'));    
    		$from_date = strtotime($subscription->getFromDate());
			//$from_date = strtotime($this->_dateAccTimezone->date($subscription->getFromDate())->format('Y-m-d H:i:s'));
			$to_date = strtotime($subscription->getToDate());
			
			// Check subscription is not expired and payment status is not failed
			$downloaded = $this->_mostDownload->downloadCheckPerCustomerSubscription($customer_id, $from_date);
			if ($current_date >= $from_date && $current_date <= $to_date && $subscription->getData("status_success") != "Failed") {
				// This will only true when product priviously downloaded in subscription period
				// $product_allowed_to_download = $this->_mostDownload->productAllowedToDownload($customer_id, $from_date, $product_id);
				$product_allowed_to_download = true;
			}
		}

		if ($all)
			return array("downloaded" => $downloaded, "allowed" => $product_allowed_to_download);
		else
			return $downloaded;
	}

	/**
	 * [updateDownloadData] Update product's download data in downloadlink,productdownloadhistory,productdownloadhistorylog
	 * @param  [type] $product_id     [description]
	 * @param  [type] $browsingDetail [description]
	 * @return [type]                 [description]
	 */
	public function updateDownloadData($product_id,$browsingDetail)
	{
		$product_downloadable_link_collection = $this->_downloadableLinkFactory->create()->getCollection()->addFieldToFilter('product_id',$product_id);        
		if($product_downloadable_link_collection->getSize() > 0){
			foreach($product_downloadable_link_collection as $download_link)
			{
				if($download_link->getLinkFile() != ""){
					$arr = explode("/",$download_link->getLinkFile());
					$file = $arr[count($arr)-1];
					$this->updateDownloadLinkModel($download_link->getLinkId());
					$this->updateProductdownloadhistory($product_id);
					$this->updateProductdownloadhistorylog($product_id,$browsingDetail);                        
				}
			}
		}
	}


	protected function updateDownloadLinkModel($download_link_id)
	{
		$downloadableLinkFactory = $this->_downloadableLinkFactory->create()->load($download_link_id);                
		if($downloadableLinkFactory->getData()){    
			$download_count = $downloadableLinkFactory->getNumberOfDownloads();
			$downloadableLinkFactory->setNumberOfDownloads($download_count + 1);
			$downloadableLinkFactory->save();
		}
	}


	public function getCurrentProduct($product_id,$flush = false)
	{
		if(!$this->_currentProduct || $flush == true){
			$this->_currentProduct = $this->_productFactory->create()->load($product_id);
		}
		return $this->_currentProduct;
	}

	public function isProductFree($product_id)
	{
		$product = $this->getCurrentProduct($product_id);
		if($product_id != $product->getId()){
			$product = $this->getCurrentProduct($product_id,true);
		}
		return $product->getFree();
	}

	protected function updateProductdownloadhistory($product_id)
	{   
		$customer_id = $this->getCustomerId();
		$product = $this->getCurrentProduct($product_id);
		$category_ids = $product->getCategoryIds();
		$category_ids = implode(",", $product->getCategoryIds());
		$categories = $product->getCategoryCollection();

		$main_category_id = array();
		foreach($categories as $_category){
			if($_category->getLevel() > 2){
				$main_category_id[] = $_category['parent_id'];
			}
			else{
				$main_category_id[] = $_category['entity_id'];
			}
		}
		$main_category_id = array_unique($main_category_id);
		$main_category_id=implode(",", $main_category_id);

		$productdownlaodcollection = $this->_productdownloadhistoryFactory->create()->getCollection()
		->addFieldToFilter('product_id',$product_id)
		->addFieldToFilter('customer_id',$customer_id);
		if($productdownlaodcollection->count() == 0){
			$productdownloadhistory = $this->_productdownloadhistoryFactory->create();
			$productdownloadhistory->setProductId($product_id);
			$productdownloadhistory->setCategoryIds($category_ids);
			$productdownloadhistory->setMainCategoryId($main_category_id);
			$productdownloadhistory->setCustomerId($customer_id);
			$productdownloadhistory->setDownloadCount(1);
			$productdownloadhistory->save();
		}
		else{
			foreach($productdownlaodcollection as $productdownlaod){
				$productdownloadhistory = $this->_productdownloadhistoryFactory->create()->load($productdownlaod->getDownloadHistoryId());
				$productdownloadhistory->setDownloadCount($productdownloadhistory->getDownloadCount()+1);
				$productdownloadhistory->setCategoryIds($category_ids);
				$productdownloadhistory->save();
			}
		}
	}

	protected function updateProductdownloadhistorylog($product_id,$browsingDetail)
	{
		$customer_id = $this->getCustomerId();
		//$current_date_acc_timezone = $this->_dateAccTimezone->date();
		//$current_date_utc = date("Y-m-d H:i:s"); // we can use this also but we must follow magento way
		//$current_date_utc = $this->_dateAccTimezone->convertConfigTimeToUtc($current_date_acc_timezone);
		$current_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
		$ip = $browsingDetail['ip'];
		$cookie_id = $browsingDetail['cookie_id'];
		$browser = $browsingDetail['browser'];
		$productdownloadhistorylog = $this->_productdownloadhistorylogFactory->create();
		$productdownloadhistorylog->setProductId($product_id);
		$productdownloadhistorylog->setCustomerId($customer_id);
		$productdownloadhistorylog->setDownloadDate($current_date);
		$productdownloadhistorylog->setIp($ip);
		$productdownloadhistorylog->setCookieId($cookie_id);
		$productdownloadhistorylog->setBrowser($browser);
		$productdownloadhistorylog->save();   
	}

	public function getSubscriptionProductsCollection()
	{
		if($this->_subscriptionProductsCollection === null)
		{
			$this->_subscriptionProductsCollection = $this->initializeSubscriptionProductCollection();
		}        
		return $this->_subscriptionProductsCollection;
	}

	protected function initializeSubscriptionProductCollection()
	{
		$collection = $this->_subscriptionProductsCollectionFactory->create()
		->addAttributeToSelect("*")
		->addAttributeToFilter('type_id', array('eq' => 'virtual'))
		->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    	->addAttributeToFilter('pricing_type',array('in' => array(0)))
		->setOrder('price','ASC');
		$this->_subscriptionProductsIds = $collection->getAllIds();                      

		return $collection;        
	}

	public function getIndividualSubscriptionProductsCollection()
	{
		$collection = $this->_subscriptionProductsCollectionFactory->create()
		->addAttributeToSelect("*")
		->addAttributeToFilter('type_id', array('eq' => 'virtual'))
		->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    	->addAttributeToFilter('pricing_type',array('in' => array(1)))
    	->addAttributeToFilter('pricing_product_type',array('eq' => '0'))
    	->addAttributeToFilter('am_rec_plans',array('nin' => array('1','2')))
		->setOrder('price','ASC');
		$this->_subscriptionProductsIds = $collection->getAllIds();                      

		return $collection;     
	}

	public function getBusinessSubscriptionProductsCollection()
	{
		$collection = $this->_subscriptionProductsCollectionFactory->create()
		->addAttributeToSelect("*")
		->addAttributeToFilter('type_id', array('eq' => 'virtual'))
		->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    	->addAttributeToFilter('pricing_type',array('in' => array(1)))
    	->addAttributeToFilter('pricing_product_type',array('eq' => '2'))
		->setOrder('price','ASC');
		$this->_subscriptionProductsIds = $collection->getAllIds();                      

		return $collection;     
	}

	public function getEducationSubscriptionProductsCollection()
	{
		$collection = $this->_subscriptionProductsCollectionFactory->create()
		->addAttributeToSelect("*")
		->addAttributeToFilter('type_id', array('eq' => 'virtual'))
		->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    	->addAttributeToFilter('pricing_type',array('in' => array(1)))
    	->addAttributeToFilter('pricing_product_type',array('eq' => '1'))
		->setOrder('price','ASC');
		$this->_subscriptionProductsIds = $collection->getAllIds();                      

		return $collection;     
	}

	public function getAllSubscriptionProductsCollection()
	{
		$collection = $this->_subscriptionProductsCollectionFactory->create()
		->addAttributeToSelect("*")
		->addAttributeToFilter('type_id', array('eq' => 'virtual'))
		->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
		->setOrder('price','ASC');
		$this->_subscriptionProductsIds = $collection->getAllIds();                      

		return $collection;   
	}

	public function getSubscriptionProductsIds()
	{
        if($this->_subscriptionProductsIds === null)
        {
            $this->getAllSubscriptionProductsCollection();
        }        
        return $this->_subscriptionProductsIds;
    }

    // check that book is Ebook
    public function isProductEbook($product_id)
	{
		$product = $this->getCurrentProduct($product_id);
		if($product_id != $product->getId()){
			$product = $this->getCurrentProduct($product_id,true);
		}
		return $product->getIsEbook();
	}

	//check product is already purchased or not
	public function CheckProductAlreadyPurchased($product_id_allowed_to_download)
    {
		$customerId = $this->getCustomerId();
        $orders = $this->purchasedProductList->getCollection()->addFieldToFilter("customer_id", $customerId);
        $products = array();
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $products[] = $item->getProductId();
            }
        }

        $product_list = array_unique($products);
        foreach ($product_list as $value) {
        	if($value == $product_id_allowed_to_download){
        		return true;
        	}
        }
        return false;
    }

    //Check count of free product downloaded
    public function checkFreeProductDownloaded($customer_id)
	{
		$resource = $this->_resourceConnection;
        $read = $resource->getConnection('core_read');
		$sql = "
        SELECT count( customer_id ) AS 'total_downloaded'
        FROM (

        SELECT *
        FROM (

        SELECT * , count( product_id )
        FROM productdownload_history_log
        WHERE customer_id = '$customer_id'
        GROUP BY `product_id`
        ) AS `main` 
        LEFT JOIN `catalog_product_entity_int` AS `catalog_int` ON `catalog_int`.`entity_id` = `main`.`product_id`
        WHERE `catalog_int`.`attribute_id` = '126'
        AND `catalog_int`.`store_id` = '0'
        AND `catalog_int`.`value` = '1'
        ) AS `outer`
        GROUP BY outer.customer_id;
        ";

        $result = $read->fetchOne($sql);

        return $result ? $result : 0;
	}
}
