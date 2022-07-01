<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\RecurringPayment\Controller\Adminhtml\RecurringPayment;

// use Magento\App\Action\NotFoundException;
use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Recurring payments view/management controller
 *
 * TODO: implement ACL restrictions
 */
class RecurringPayment extends \Magento\Backend\App\Action
{
    /**#@+
     * Request parameter key
     */
    const PARAM_CUSTOMER_ID = 'id';
    const PARAM_PAYMENT = 'payment';
    const PARAM_ACTION = 'action';
    /**#@-*/

    /**#@+
     * Value for PARAM_ACTION request parameter
     */
    const ACTION_CANCEL = 'cancel';
    const ACTION_SUSPEND = 'suspend';
    const ACTION_ACTIVATE = 'activate';
    /**#@-*/

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
        parent::__construct($context);
    }

    /**
     * Recurring payments list
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_RecurringPayment::recurring_payment');
        $resultPage->getConfig()->getTitle()->prepend(__('Recurring Billing Payments'));
        return $resultPage;
    }

    /**
     * View recurring payment details
     *
     * @return void
     */
    // Migrated in RecurringPayment/View.php
    // public function viewAction()
    // {
    //     try {
    //         $this->_title->prepend(__('Recurring Billing Payments'));
    //         $payment = $this->_initPayment();
    //         $this->_view->loadLayout();
    //         $this->_setActiveMenu('Magento_RecurringPayment::recurring_payment');
    //         $this->_title->prepend(__('Payment #%1', $payment->getReferenceId()));
    //         $this->_view->renderLayout();
    //         return;
    //     } catch (LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->_logger->err($e);
    //     }
    //     $this->_redirect('sales/*/');
    // }

    /**
     * Payments ajax grid
     *
     * @return void
     */
    // Migrated in RecurringPayment/Grid.php
    // public function gridAction()
    // {
    //     try {
    //         $this->_view->loadLayout()->renderLayout();
    //         return;
    //     } catch (LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->_logger->err($e);
    //     }
    //     $this->_redirect('sales/*/');
    // }

    /**
     * Payment orders ajax grid
     *
     * @return void
     * @throws NotFoundException
     */
    // Migrated in RecurringPayment/Orders.php
    // public function ordersAction()
    // {
    //     try {
    //         $this->_initPayment();
    //         $this->_view->loadLayout()->renderLayout();
    //     } catch (\Exception $e) {
    //         $this->_logger->err($e);
    //         throw new NotFoundException();
    //     }
    // }

    /**
     * Payment state updater action
     *
     * @return void
     */
    // Migrated in RecurringPayment/UpdateState.php
    // public function updateStateAction()
    // {
    //     $payment = null;
    //     try {
    //         $payment = $this->_initPayment();
    //         $action = $this->getRequest()->getParam(self::PARAM_ACTION);

    //         switch ($action) {
    //             case self::ACTION_CANCEL:
    //                 $payment->cancel();
    //                 break;
    //             case self::ACTION_SUSPEND:
    //                 $payment->suspend();
    //                 break;
    //             case self::ACTION_ACTIVATE:
    //                 $payment->activate();
    //                 break;
    //             default:
    //                 throw new \Exception(sprintf('Wrong action parameter: %s', $action));
    //         }
    //         $this->messageManager->addSuccess(__('The payment state has been updated.'));
    //     } catch (LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->messageManager->addError(__('We could not update the payment.'));
    //         $this->_logger->err($e);
    //     }
    //     if ($payment) {
    //         $this->_redirect('sales/*/view', array(self::PARAM_PAYMENT => $payment->getId()));
    //     } else {
    //         $this->_redirect('sales/*/');
    //     }
    // }

    /**
     * Payment information updater action
     *
     * @return void
     */
    // Migrated in RecurringPayment/UpdatePayment.php
    // public function updatePaymentAction()
    // {
    //     $payment = null;
    //     try {
    //         $payment = $this->_initPayment();
    //         $payment->fetchUpdate();
    //         if ($payment->hasDataChanges()) {
    //             $payment->save();
    //             $this->messageManager->addSuccess(__('You updated the payment.'));
    //         } else {
    //             $this->messageManager->addNotice(__('The payment has no changes.'));
    //         }
    //     } catch (LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->messageManager->addError(__('We could not update the payment.'));
    //         $this->_logger->err($e);
    //     }
    //     if ($payment) {
    //         $this->_redirect('sales/*/view', array(self::PARAM_PAYMENT => $payment->getId()));
    //     } else {
    //         $this->_redirect('sales/*/');
    //     }
    // }

    /**
     * Customer grid ajax action
     *
     * @return void
     */
    // Migrated in RecurringPayment/CustomerGrid.php
    // public function customerGridAction()
    // {
    //     $customerId = (int)$this->getRequest()->getParam(self::PARAM_CUSTOMER_ID);

    //     if ($customerId) {
    //         $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
    //     }

    //     $this->_view->loadLayout(false);
    //     $this->_view->renderLayout();
    // }

    /**
     * Load/set payment
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create('Magento\RecurringPayment\Model\Payment')
            ->load($this->getRequest()->getParam(self::PARAM_PAYMENT));
        if (!$payment->getId()) {
            throw new LocalizedException(__('The payment you specified does not exist.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
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
