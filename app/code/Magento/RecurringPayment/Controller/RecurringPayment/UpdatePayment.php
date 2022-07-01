<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RecurringPayment\Controller\RecurringPayment;

use Magento\App\Action\NotFoundException;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;

class UpdatePayment extends \Magento\RecurringPayment\Controller\RecurringPayment\RecurringPayment
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    /**
     * 
     * @param \Magento\Backend\App\Action\Context        $context           
     * @param \Magento\Framework\Registry                $coreRegistry      
     * @param \Magento\Framework\View\Page\Title         $title             
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,        
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
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
        $payment = null;
        try {
            $payment = $this->_initPayment();
            $payment->fetchUpdate();
            if ($payment->hasDataChanges()) {
                $payment->save();
                $this->messageManager->addSuccess(__('The payment has been updated.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
        }
        if ($payment) {
            $this->_redirect('*/*/view', array('payment' => $payment->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

}
