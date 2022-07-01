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
class AddFormExcludedAttribute implements ObserverInterface
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
        $block = $observer->getEvent()->getObject();

        $block->setFormExcludedFieldList(array_merge($block->getFormExcludedFieldList(), ['recurring_payment']));
    }
}
