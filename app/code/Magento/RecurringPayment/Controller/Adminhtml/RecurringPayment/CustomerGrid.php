<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment;

use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;

class CustomerGrid extends \Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment\RecurringPayment
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;;
        $this->_logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context,$coreRegistry,$logger,$resultPageFactory);
    }

    /**
     * Product list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {        
        $customerId = (int)$this->getRequest()->getParam(self::PARAM_CUSTOMER_ID);

        if ($customerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        // $this->_view->loadLayout(false);
        // $this->_view->renderLayout();
        // Need to work here as per loadLayout false
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_RecurringPayment::recurring_payment');
    }
}
