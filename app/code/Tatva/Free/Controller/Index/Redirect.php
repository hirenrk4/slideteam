<?php
namespace Tatva\Free\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;


class Redirect extends \Magento\Framework\App\Action\Action
{
	protected $_resultPageFactory;

	/**
	 * [$_urlInterface ]
	 * @var [\Magento\Framework\UrlInterface]
	 */
	protected $_urlInterface;

	public function __construct(
		Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		UrlInterface $urlInterface
	){
		$this->_resultPageFactory = $resultPageFactory;
		$this->scopeConfig = $scopeConfig;
		$this->_urlInterface = $urlInterface;
		parent::__construct($context);
	}

	public function execute()
	{
		$pricing_url = $this->_urlInterface->getUrl('pricing');   
		$this->messageManager->addError(__("Error message."));

		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setUrl($pricing_url);
		return $resultRedirect;
	}
}