<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RecurringPayment\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BeforeEntitySave
 */
class SetFormRecurringElementRenderer implements ObserverInterface
{
    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $form = $observer->getEvent()->getForm();

        $recurringPaymentElement = $form->getElement('recurring_payment');
        $recurringPaymentBlock = $observer->getEvent()->getLayout()
            ->createBlock('Magento\RecurringPayment\Block\Adminhtml\Product\Edit\Tab\Price\Recurring');

        if ($recurringPaymentElement) {
            $recurringPaymentElement->setRenderer($recurringPaymentBlock);
        }
    }
}
