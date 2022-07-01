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

/**
 * Recurring payments view/management controller
 */
namespace Magento\RecurringPayment\Controller\RecurringPayment;

use Magento\Framework\App\RequestInterface;
use Magento\App\Action\NotFoundException;
use Magento\Framework\Exception\LocalizedException as LocalizedException;

class RecurringPayment extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_session = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;


    protected $resultPageFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Make sure customer is logged in and put it into registry
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        $this->_session = $this->_objectManager->get('Magento\Customer\Model\Session');
        if (!$this->_session->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $this->_coreRegistry->register('current_customer', $this->_session->getCustomer());
        return parent::dispatch($request);
    }

    /**
     * Payments listing
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Recurring Billing Payments'));
        return $resultPage;
    }

    /**
     * Payment main view
     *
     * @return void
     */
    // Migrated in RecurringPayment/View.php
    // public function viewAction()
    // {
    //     $this->_viewAction();
    // }

    /**
     * Payment related orders view
     *
     * @return void
     */
    // Migrated in RecurringPayment/Orders.php
    // public function ordersAction()
    // {
    //     $this->_viewAction();
    // }

    /**
     * Attempt to set payment state
     *
     * @return void
     */
    // Migrated in RecurringPayment/UpdateState.php
    // public function updateStateAction()
    // {
    //     $payment = null;
    //     try {
    //         $payment = $this->_initPayment();

    //         switch ($this->getRequest()->getParam('action')) {
    //             case 'cancel':
    //                 $payment->cancel();
    //                 break;
    //             case 'suspend':
    //                 $payment->suspend();
    //                 break;
    //             case 'activate':
    //                 $payment->activate();
    //                 break;
    //             default:
    //                 break;
    //         }
    //         $this->messageManager->addSuccess(__('The payment state has been updated.'));
    //     } catch (\Magento\Framework\Exception\LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->messageManager->addError(__('We couldn\'t update the payment.'));
    //     }
    //     if ($payment) {
    //         $this->_redirect('*/*/view', array('payment' => $payment->getId()));
    //     } else {
    //         $this->_redirect('*/*/');
    //     }
    // }

    /**
     * Fetch an update with payment
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
    //             $this->messageManager->addSuccess(__('The payment has been updated.'));
    //         } else {
    //             $this->messageManager->addNotice(__('The payment has no changes.'));
    //         }
    //     } catch (\Magento\Framework\Exception\LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->messageManager->addError(__('We couldn\'t update the payment.'));
    //     }
    //     if ($payment) {
    //         $this->_redirect('*/*/view', array('payment' => $payment->getId()));
    //     } else {
    //         $this->_redirect('*/*/');
    //     }
    // }

    /**
     * Generic payment view action
     *
     * @return void
     */
    // Migrated in RecurringPayment/View.php
    // protected function _viewAction()
    // {
    //     try {
    //         $payment = $this->_initPayment();
    //         $this->_title->add(__('Recurring Billing Payments'));
    //         $this->_title->add(__('Payment #%1', $payment->getReferenceId()));
    //         $this->_view->loadLayout();
    //         $this->_view->getLayout()->initMessages();
    //         $this->_view->renderLayout();
    //         return;
    //     } catch (\Magento\Framework\Exception\LocalizedException $e) {
    //         $this->messageManager->addError($e->getMessage());
    //     } catch (\Exception $e) {
    //         $this->_objectManager->get('Magento\Logger')->logException($e);
    //     }
    //     $this->_redirect('*/*/');
    // }

    /**
     * Instantiate current payment and put it into registry
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initPayment()
    {
        $payment = $this->_objectManager->create('Magento\RecurringPayment\Model\Payment')
            ->load($this->getRequest()->getParam('payment'));
        if (!$payment->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the payment you specified.'));
        }
        $this->_coreRegistry->register('current_recurring_payment', $payment);
        return $payment;
    }
}
