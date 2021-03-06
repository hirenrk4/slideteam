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
namespace Magento\RecurringPayment\Model\Quote\Total;

/**
 * Total model for recurring payments
 */
abstract class AbstractRecurring
    extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Don't add amounts to address
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = false;

    /**
     * By what key to set data into item
     *
     * @var string|null
     */
    protected $_itemRowTotalKey = null;

    /**
     * By what key to get data from payment
     *
     * @var string|null
     */
    protected $_paymentDataKey = null;

    /**
     * Collect recurring item parameters and copy to the address items
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote,$shippingAssignment,$total);
        $address = $this->_getAddress();
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            if ($item->getProduct()->getIsRecurring()) {
                $paymentData = $item->getProduct()->getRecurringPayment();
                if (!empty($paymentData[$this->_paymentDataKey])) {
                    $item->setData(
                        $this->_itemRowTotalKey,
                        $address->getQuote()->getStore()->convertPrice($paymentData[$this->_paymentDataKey])
                    );
                    $this->_afterCollectSuccess($address, $item);
                }
            }
        }
        return $this;
    }


    /**
     * Get nominal items only
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     */
    protected function _getAddressItems(\Magento\Quote\Model\Quote\Address $address)
    {
        // return $address->getAllNominalItems();
        // We used the following method as above one internally call same as below(in older code) and the changes required that for 
        // address and product entity wise also.
        return $address->getAllItems();
    }

    /**
     * Hook for successful collecting of a recurring amount
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterCollectSuccess($address, $item)
    {
    }
}
