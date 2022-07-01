<?php
/**
 * Product controller.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Seositemap\Controller\Index;

class Category extends \Magento\Framework\App\Action\Action
{
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();    
		$resultPage->getConfig()->getTitle()->set(__('Sitemap'));
        return $resultPage;
    }
}
