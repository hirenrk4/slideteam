<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Categoryreport\Controller\Adminhtml\Report;

class Category extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('Category Product Exports'));
        return $resultPage;
    }

}
