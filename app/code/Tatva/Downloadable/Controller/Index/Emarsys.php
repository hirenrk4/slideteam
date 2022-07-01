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

class Emarsys extends \Magento\Downloadable\Controller\Download
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
	protected $resultJsonFactory;
	protected $productDownloadHistoryLogFactory;

	public function __construct(
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
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionFactory $productDownloadHistoryLogFactory,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		Context $context)
	{
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
		$this->resultJsonFactory = $resultJsonFactory;
		$this->productDownloadHistoryLogFactory = $productDownloadHistoryLogFactory;
		$this->productRepository = $productRepository;
		//$this->resultFactory = $context->getResultFactory();
		parent::__construct($context);
	}

	public function execute() 
	{
		$product_id = !empty($_POST['product_id'])?$_POST['product_id']:'';
		$share = $this->getRequest()->getParam('share');

		if(empty($product_id))
		{
			$response = ['success_return' => 5];
			$resultJson = $this->resultJsonFactory->create();
			$resultJson->setData($response);
			return $resultJson;	
			exit;
		}

		$customer_id = $this->_customerSession->getCustomerId();
		if(empty($customer_id)){
			$response = ['success_return' => 6];
			$resultJson = $this->resultJsonFactory->create();
			$resultJson->setData($response);
			return $resultJson;
			exit;
		}
		
		$productcanbeDownloaded = $this->_subscription->productCanBeDownloaded($product_id,$share);
		$resultJson = $this->resultJsonFactory->create();
		$customerproductDetail = $this->getCustomerProductDownloadDetails($product_id);
		$response = ['success_return' => $productcanbeDownloaded,'detail' => $customerproductDetail];
		$resultJson->setData($response);
		return $resultJson;	
	
	}

	public function getCustomerProductDownloadDetails($product_id){
        $freeProducts = $paidProducts = $download_result = array();
        $customer_id = $this->_customerSession->getCustomerId();
        $customerType = $this->_subscription->getCustomerType($customer_id);

        $download_result['paidCustomer'] = 0;
        $download_result['customer_id'] = $customer_id;

        $download_result['paidProductsDownloaded'] = $download_result['freeProductsDownloaded'] = 0;
        $download_result['lastPaidProductDownloadDate'] = $download_result['lastFreeProductDownloadDate'] = null;

        $isProductFree = $this->_subscription->isProductFree($product_id);
        if($isProductFree)
        {
        	$download_result['content_type'] = 'free';
        }else {
        	$download_result['content_type'] = 'paid';
        }

        $product = $this->productRepository->getById($product_id);
        $download_result['content_title'] = $product->getName();

        if(isset($customerType['customerType']) && $customerType['customerType'] == 'Active')
        {
            $download_result['paidCustomer'] = 1;
            //Paid Products
            $paidproductCollection = $this->productDownloadHistoryLogFactory->create()->addFieldToFilter('customer_id',$customer_id);
            $paidproductCollection->getSelect()->joinleft(["at_free" => $paidproductCollection->getTable("catalog_product_entity_int")], "(`at_free`.`entity_id` = `main_table`.`product_id`) AND (`at_free`.`attribute_id` = '126') AND (`at_free`.`store_id` = 0) ", [])
                ->columns('at_free.value AS free')
                ->where('at_free.value != 1 OR at_free.value IS NULL')
                ->group('main_table.product_id')
                ->order('main_table.download_date DESC');


            if($paidproductCollection && $paidproductCollection->getSize() > 0)
            {
                $download_result['paidProductsDownloaded'] = $paidproductCollection->getSize();
                $downloadDate = $paidproductCollection->getFirstItem()->getDownloadDate();
                $lastPaidProductDownloadDate = $this->converToTz($downloadDate,'America/Los_Angeles','GMT');
                $download_result['lastPaidProductDownloadDate'] = $lastPaidProductDownloadDate;
            }
        }

        //Free Products
        $freeproductCollection = $this->productDownloadHistoryLogFactory->create()->addFieldToFilter('customer_id',$customer_id);
        $freeproductCollection->getSelect()->join(["at_free" => $freeproductCollection->getTable("catalog_product_entity_int")], "(`at_free`.`entity_id` = `main_table`.`product_id`) AND (`at_free`.`attribute_id` = '126') AND (`at_free`.`store_id` = 0) ", [])
            ->columns('at_free.value AS free')
            ->where('at_free.value = 1')
            ->group('main_table.product_id')
            ->order('main_table.download_date DESC');
        if($freeproductCollection && $freeproductCollection->getSize() > 0)
        {
            $download_result['freeProductsDownloaded'] = $freeproductCollection->getSize();
            $downloadDate = $freeproductCollection->getFirstItem()->getDownloadDate();
            $lastFreeProductDownloadDate = $this->converToTz($downloadDate,'America/Los_Angeles','GMT');
            $download_result['lastFreeProductDownloadDate'] = $lastFreeProductDownloadDate;
        } 
        return $download_result;
    }

    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {   
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d');
        return $dateTime;
    }
}