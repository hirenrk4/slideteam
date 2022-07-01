<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RecurringPayment\Controller\RecurringPayment;

use Magento\App\Action\NotFoundException;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;

class View extends \Magento\RecurringPayment\Controller\RecurringPayment\RecurringPayment
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
        \Zend\Log\Logger $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;;
        $this->_logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context,$coreRegistry,$resultPageFactory);
    }

    /**
     * Product list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->_initPayment();
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Recurring Billing Payments'));
            $resultPage->getConfig()->getTitle()->prepend(__('Payment #%1', $payment->getReferenceId()));
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->err($e);
        }
        $this->_redirect('*/*/');

    }

}
