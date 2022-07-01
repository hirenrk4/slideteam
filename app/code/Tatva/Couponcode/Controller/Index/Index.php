<?php
namespace Tatva\Couponcode\Controller\Index;

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
			$resultPage = $this->resultPageFactory->create();
			$resultPage->getConfig()->getTitle()->set('Coupon Codes');
			$resultPage->getConfig()->setDescription('Get Coupon here'); 
			// $resultPage->getConfig()->setKeywords("Free Business PowerPoint template, Business Presentation Template, PPT Template, Free Slide Template, Free PowerPoint Template"); 

			return $resultPage;
	}

}
