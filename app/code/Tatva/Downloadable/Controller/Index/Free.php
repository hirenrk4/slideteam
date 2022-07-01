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

class Free extends \Magento\Downloadable\Controller\Download
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

	public function __construct(
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
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
		Context $context)
	{
		$this->resultRedirectFactory = $resultRedirectFactory;
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
		//$this->resultFactory = $context->getResultFactory();
		parent::__construct($context);
	}

	public function execute() 
	{
		$params = $this->getRequest()->getParams();
		if(isset($params['product_id']))
		{
			$product_id = $params['product_id'];
			$this->_catalogSession->setProductValue($product_id);
		}else{
			$this->_catalogSession->setProductValue(null);
		}
		/*For download product in wishlist*/
		if(isset($params['wishlist_status']) && $params['wishlist_status'] == 1)
		{
			$this->_customerSession->setCaptchaStatus(0);
		}
		
		if(isset($params['captchapost']))		
			$this->_catalogSession->setCaptchaPost($params['captchapost']);

		if (empty($params)) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('/');
			return $resultRedirect;
		}
	}
}