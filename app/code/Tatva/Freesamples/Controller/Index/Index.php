<?php
namespace Tatva\Freesamples\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
		\Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
	) {
		parent::__construct($context);
		$this->scopeConfig = $scopeConfig;
		$this->resultPageFactory = $resultPageFactory;
		$this->toolbar = $toolbar;
		$this->resultForwardFactory = $resultForwardFactory;
	}
	public function execute()
	{
		$product_limit = $this->getRequest()->getParam('product_list_limit');
		$available_limits = $this->toolbar->getAvailableLimit();
		if(!in_array($product_limit, $available_limits) && !empty($product_limit)) 
		{
			return $this->resultForwardFactory->create()->forward('limit');
			die();
		}
		else {
			$resultPage = $this->resultPageFactory->create();

			$resultPage->getConfig()->getTitle()->set($this->scopeConfig->getValue("freesamples_options/general/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
			$resultPage->getConfig()->setDescription($this->scopeConfig->getValue("freesamples_options/general/meta_description", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)); 
			$resultPage->getConfig()->setKeywords("Free Business PowerPoint template, Business Presentation Template, PPT Template, Free Slide Template, Free PowerPoint Template"); 

			return $resultPage;
		}
	}

}
