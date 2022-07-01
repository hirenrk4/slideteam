<?php
namespace Tatva\Downloadable\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
//use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Downloadable\Helper\File as DownloadableFile;
use Magento\Framework\Exception\LocalizedException as CoreException;
use Tatva\Subscription\Model\Subscription;
use Tatva\Ebook\Helper\Ebook;
use Tatva\Ebook\Model\ProductDownloadEbooksHistoryLogFactory;
use Magento\Framework\Stdlib\DateTime\Timezone;

class Download extends \Magento\Downloadable\Controller\Download
{
	/**
	 * [$_customerSession ]
	 * @var [\Magento\Customer\Model\Session]
	 */
	protected $_customerSession;

	/**
	 * [$_urlInterface ]
	 * @var [\Magento\Framework\UrlInterface]
	 */
	protected $_urlInterface;

	/**
	 * [$_subscription]
	 * @var [\Tatva\Subscription\Model\Subscription;]
	 */
	protected $_subscription;


	protected $_downloadableFile;

	/**
	 * [$_browsingDetail]
	 * @var [array]
	 */
	protected $_browsingDetail;

	/**
	 * [$_file_path] Downloadable file full path
	 * @var [string]
	 */
	protected $_file_path;

	/**
	 * [$_link_type] Downloadable type 
	 * @var [\Magento\Downloadable\Helper\Download::LINK_TYPE_URL || LINK_TYPE_FILE]
	 */
	protected $_link_type;

	protected $_remoteAddress;
	
	protected $_filesystem;
	protected $_downloadLink;
	protected $_downloadSample;
	protected $_catalogSession;
	protected $_responseInterface;
	protected $_productdownloadebookhistorylog;
	protected $_timezone;

	public function __construct(
		Timezone $timezone,
		ProductDownloadEbooksHistoryLogFactory $productDownloadEbooksHistoryLog,
		Ebook $ebook,
		\Magento\Downloadable\Model\Link $downloadLink,
		\Magento\Downloadable\Model\Sample $downloadSample,
		\Magento\Framework\App\ResponseInterface $responseInterface,
		\Magento\Catalog\Model\Session $catalogSession,
		\Magento\Framework\Filesystem $filesystem,
		UrlInterface $urlInterface,
		\Magento\Customer\Model\Session $customerSession,
		Subscription $subscription,
		RemoteAddress $remoteAddress,
		DownloadableFile $downloadableFile,
		\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
		Context $context)
	{
		$this->_timezone = $timezone;
		$this->_productdownloadebookhistorylog = $productDownloadEbooksHistoryLog;
		$this->ebook = $ebook;
		$this->_filesystem = $filesystem;
		$this->_responseInterface = $responseInterface;
		$this->_downloadLink = $downloadLink;
		$this->_downloadSample = $downloadSample;
		$this->_catalogSession = $catalogSession;
		$this->_customerSession = $customerSession;
		$this->_urlInterface = $urlInterface;
		$this->_subscription = $subscription;
		$this->_remoteAddress = $remoteAddress;
		$this->_downloadableFile = $downloadableFile;
		$this->_dateFactory = $dateTimeDateTimeFactory;
		//$this->resultFactory = $context->getResultFactory();
		parent::__construct($context);
	}

	/**
	 * [execute description]
	 *
	 * Flow
	 * -------------------
	 * 1.) Customer is logged in and it is downloadable product
	 *    	0.) Redirect to login
	 * 		1.) Validate Customer has subscription to download product using helper's method candownloadableproduct
	 * 			0.)	Redirect to pricing
	 * 			1.) Process for download
	 * 				*.) Restrict download functionality ==> curently not active
	 * 				*.) Get collection from subscription/productdownloadhistory >> get customer's download count and determine the captcha status
	 * 				*.) set data for subscription/productdownloadhistory model or increment the download count
	 * 				*.) Add entry in subscription/productdownloadhistorylog for specific download 
	 * 				*.) Prepare download
	 * 			 
	 * 
	 * @return [download the product]
	 */
	public function execute()
	{

		// Check for customer's login
		if($this->_customerSession->isLoggedIn())
		{

			$captchaStatus = $this->_customerSession->getCaptchaStatus();	 
			$captcha_data = $this->_catalogSession->getCaptchaPost();   
			$_captcha = $this->_customerSession->getData('downloadable_captcha_word'); 
 
			if($captchaStatus) 
			{ 
				$referel_url = $this->_redirect->getRefererUrl(); 
    			$resultRedirect = $this->resultRedirectFactory->create(); 
				$resultRedirect->setUrl($referel_url); 
 
                if(isset($captcha_data)) { 
 
    				if($captcha_data != $_captcha['data']) 
    				{ 
    					$this->messageManager->addError(__('Please Enter Valid Captcha.'));    					 
    					return $resultRedirect; 
    				} 
                } 
                else 
                { 
                	$this->messageManager->addError(__('Please Enter Valid Captcha.')); 
                	return $resultRedirect;  
                } 
			} 
 
			// $product_id = $this->_catalogSession->getData('productId');
			$product_id = $this->_catalogSession->getProductValue();
			if(!isset($product_id))
			{
				$referel_url = $this->_redirect->getRefererUrl();
				$this->messageManager->addError(__("Something went wrong while getting the requested content."));
				header("Location: ".$referel_url);
				exit;
			}
			$share = $this->getRequest()->getParam('share');

			$productCanBeDownloaded = $this->_subscription->productCanBeDownloaded($product_id,$share);
			$Allebook = $this->ebook->getGroupEbook();
			$AllEbookAlreadyPurchased = $this->ebook->isCustomerPurchased($Allebook->getEntityID());
			if($AllEbookAlreadyPurchased == true){
				$productCanBeDownloaded = 1;
			}

			//1 = Customer is allowed to download the product
			if($productCanBeDownloaded == 1){

				// Need to validate captcha if customer has reached the threshold limit , here taken flase as we need to implement it yet
				
				$downloadable_file_exists = $this->prepareDownload();

				if($downloadable_file_exists){
					// Update downloadable/link model as per download
					
					$browsingDetail = $this->getBrowsingDetail();	
					$isProductEbook = $this->_subscription->isProductEbook($product_id);
					if($isProductEbook == 1) {
						$current_date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
					    $customer_id = $this->_customerSession->getCustomer()->getId();

						$ip = $browsingDetail['ip'];
						$cookie_id = $browsingDetail['cookie_id'];
						$browser = $browsingDetail['browser'];

						$productdownloadebookhistorylog = $this->_productdownloadebookhistorylog->create();
						$productdownloadebookhistorylog->setProductId($product_id);
						$productdownloadebookhistorylog->setCustomerId($customer_id);
						$productdownloadebookhistorylog->setDownloadDate($current_date);
						$productdownloadebookhistorylog->setIp($ip);
						$productdownloadebookhistorylog->setCookieId($cookie_id);
						$productdownloadebookhistorylog->setBrowser($browser);
						$productdownloadebookhistorylog->save();   
					} else {
						$this->_subscription->updateDownloadData($product_id,$browsingDetail);
					}
					$download_status = $this->finalizeDownload();
					exit;
				}
				else{
					// Get referer url
					$referel_url = $this->_redirect->getRefererUrl();
					$this->messageManager->addError(__("An error occurred while getting the requested content."));

					header("Location: ".$referel_url);
					exit;
					// Redirect to product page with error message
					/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
					//$resultRedirect = $this->resultRedirectFactory->create();
					//$resultRedirect->setUrl($referel_url);
					//return $resultRedirect;
				}


			}
			//2 then and then user will be allowed to purchase new subscription.
			elseif ($productCanBeDownloaded == 2) {
				// Customer has not any subscription and need to purchase new one so redirect ot pricing
				// Pricing URL
				$pricing_url = $this->_urlInterface->getUrl('pricing');
				
				$isProductEbook = $this->_subscription->isProductEbook($product_id);
				if($isProductEbook == 1) {	
					$ebook_url = $this->_urlInterface->getUrl('ebook');
					$this->messageManager->addNotice(__("You must have Buy to download the product."));
					header("Location: ".$ebook_url);
					exit;
				} else {
					$this->messageManager->addNotice(__("You must have subscription to download the product."));
				}

				header("Location: ".$pricing_url);
				exit;
	           	// Redirect to Pricing URL
				/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				//$resultRedirect = $this->resultRedirectFactory->create();
				//$resultRedirect->setUrl($pricing_url);
				//return $resultRedirect;
			}
			//3 customer will not be allowed to purchase new subscription untill he/she request for unsubscribe (Download limit exausted).
			elseif ($productCanBeDownloaded == 3) {
				// redirect to subscription list in user account area
				$acc_subscription_list_url = $this->_urlInterface->getUrl('subscription/index/list');
				//$this->messageManager->addNotice(__("You need to unsubscribe previous Subscription to buy new Subscription."));

				header("Location: ".$acc_subscription_list_url);
				exit;
	           	// Redirect to login URL
				/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				//$resultRedirect = $this->resultRedirectFactory->create();
				//$resultRedirect->setUrl($acc_subscription_list_url);
				//return $resultRedirect;
			}
		}
		else
		{
			// Get referer url
			$referel_url = $this->_redirect->getRefererUrl();

        	// Create login URL
			$login_url = $this->_urlInterface->getUrl('customer/account/login', array('referer' => base64_encode($referel_url),'product_id'=>$this->_catalogSession->getProductValue()));
			$this->messageManager->addNotice(__("Please login to download the product."));

			header("Location: ".$login_url);
			exit;
           	// Redirect to login URL
			/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
			//$resultRedirect = $this->resultRedirectFactory->create();
			//$resultRedirect->setUrl($login_url);
			//return $resultRedirect;
		}
	}

	/**
	 * [getBrowsingDetail] Get browser-type ,User's Ip ,Cookie Id
	 * @return [array] [$this->_browsingDetail]
	 */
	protected function getBrowsingDetail()
	{
		$cookie_id = $this->getRequest()->getParam('cookie_id');
		$browser = $this->getRequest()->getParam('browser');
		$ip_address =  $this->_remoteAddress->getRemoteAddress();
		$ip = getenv('HTTP_CLIENT_IP')?:
		getenv('HTTP_X_FORWARDED_FOR')?:
		getenv('HTTP_X_FORWARDED')?:
		getenv('HTTP_FORWARDED_FOR')?:
		getenv('HTTP_FORWARDED')?:
		getenv('REMOTE_ADDR');
		$ip = $ip.",".$ip_address;
		$ip = str_replace(" ", "", $ip);
		$ip = implode(',', array_unique(explode(',', $ip)));
		$this->_browsingDetail = array('cookie_id' => $cookie_id ,'browser' => $browser, 'ip' => $ip );
		
		return $this->_browsingDetail;
	}


	/**
	 * [finalizeDownload ] Prepare Download and check if the file exists or not
	 * @return [boolean] [downloadable file existance in system]
	 */
	protected function prepareDownload()
	{
		// Get referer url
		$referel_url = $this->_redirect->getRefererUrl();

		// $product_id = $this->_catalogSession->getData('productId');
		$product_id = $this->_catalogSession->getProductValue();;
		$base_path = $this->_downloadLink->getBasePath();
		$link_file = "";
		$link_type = "";
		$file_path = null;
		$isProductFree = $this->_subscription->isProductFree($product_id);

		if($isProductFree){

			// Check first for samples
			$download_links_sample = $this->_downloadSample->getCollection()->addFieldToFilter('product_id', array('eq' => $product_id));

			if($download_links_sample->getSize()){
				foreach ($download_links_sample as $sample_link) {
					$link_file = $sample_link->getData('sample_file');			
					$link_type = $sample_link->getData('sample_type');			
					$base_path_sample = $this->_downloadSample->getBasePath();
					$file_path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $base_path_sample . $link_file;
				}	
			}
			else{
				// Then check if sample links are there or not
				$download_links = $this->_downloadLink->getCollection()->addFieldToFilter('product_id', array('eq' => $product_id));
				
				if($download_links->getSize()){
					foreach ($download_links as $link) {	
						if($link->getData('sample_file') != null){
							$link_file = $link->getData('sample_file');			
							$link_type = $link->getData('sample_type');			
							$base_path_link_sample = $link->getBaseSamplePath();
							$file_path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$base_path_link_sample.$link_file;	
						}
						elseif($link->getData('link_file') != null){
							$link_file = $link->getData('link_file');			
							$link_type = $link->getData('link_type');			
							$file_path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath().$base_path.$link_file;	
						}
					}	
				}
			}	
		}
		else{

		 	//Downloadable file/link 
			$download_links = $this->_downloadLink->getCollection()->addFieldToFilter('product_id', array('eq' => $product_id));
			
			if($download_links->getSize()){
				foreach ($download_links as $link) {
					$link_file = $link->getData('link_file');			
					$link_type = $link->getData('link_type');			
					$file_path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $base_path . $link_file;	
				}	
			}	
		}

		$fileExists = $this->_downloadableFile->ensureFileInFilesystem($file_path);
		if($fileExists){
			$this->_file_path = $file_path;
			$this->_link_type = $link_type;
		}

		return $fileExists;

	}

	/**
	 * [finalizeDownload ] Actual Download action
	 * @return [type] [description]
	 */
	protected function finalizeDownload()
	{		
		//$this->_eventManager->dispatch('downloadable_before_download');
		
		try {
			$fileExists = $this->_downloadableFile->ensureFileInFilesystem($this->_file_path);
			if ($fileExists) {
				$this->_processDownload($this->_file_path, $this->_link_type);
			} else {
				return false;
			}            
		} catch (\Exception $e) {
			$this->messageManager->addError(__('Something went wrong while getting the requested content.'));
		}

	}

}
