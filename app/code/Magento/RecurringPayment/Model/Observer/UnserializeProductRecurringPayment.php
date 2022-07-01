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
class UnserializeProductRecurringPayment implements ObserverInterface
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
        $collection = $observer->getEvent()->getCollection();

        foreach ($collection as $product) {
            $payment = $product->getRecurringPayment();
            if ($product->getIsRecurring() && $payment) {
                $is_serialized = $this->isSerialized($payment);
                if($is_serialized){
                    $product->setRecurringPayment(unserialize($payment));
                }
                else{
                    $product->setRecurringPayment($payment);   
                }
            }
        }
    }

    /**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }
}
