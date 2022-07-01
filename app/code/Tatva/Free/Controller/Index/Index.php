<?php
namespace Tatva\Free\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_resultPageFactory;

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
       	$noroute = $this->_urlInterface->getUrl('no-route');   

		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setUrl($noroute);
		return $resultRedirect;
	}
}