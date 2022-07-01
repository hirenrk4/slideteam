<?php

namespace Tatva\Catalog\Plugin\Controller\Category;

use Magento\Framework\View\Result\PageFactory;


class View
{

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
       	$this->resultPageFactory = $resultPageFactory;
        $this->toolbar = $toolbar;
       	$this->resultForwardFactory = $resultForwardFactory;
    }

	public function beforeExecute(\Magento\Catalog\Controller\Category\View $category)
	{

		$product_limit = $category->getRequest()->getParam('product_list_limit');
		$available_limits = $this->toolbar->getAvailableLimit();
		if(!in_array($product_limit, $available_limits) && !empty($product_limit)) 
		{
	        return $this->resultForwardFactory->create()->forward('limit');
		}
	}
}