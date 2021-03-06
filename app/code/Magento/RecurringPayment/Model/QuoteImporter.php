<?php
/**
 * Magento
6 *
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
 * Recurring payment quote model
 */
namespace Magento\RecurringPayment\Model;

class QuoteImporter
{
    /**
     * @var \Magento\RecurringPayment\Model\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @param \Magento\RecurringPayment\Model\PaymentFactory $paymentFactory
     */
    public function __construct(
        \Magento\RecurringPayment\Model\PaymentFactory $paymentFactory)
    {
        $this->_paymentFactory = $paymentFactory;
    }

    /**
     * Prepare recurring payments
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @throws \Exception
     * @return array
     */
    public function import(\Magento\Quote\Model\Quote $quote)
    {
        // if (!$quote->getTotalsCollectedFlag()) {
        //     throw new \Exception('Quote totals must be collected before this operation.');
        // }

        $result = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (is_object($product) && ($product->getIsRecurring()) && $payment = $this->_paymentFactory->create()->importProduct($product)) {
                $payment->importQuote($quote);
                $payment->importQuoteItem($item);
				$payment->setBillingAmount($quote->getBaseGrandTotal());
                $result[] = $payment;
            }
        }
        return $result;
    }
}
