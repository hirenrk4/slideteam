<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RecurringPayment\Model\Observer\CheckoutManagerObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BeforeEntitySave
 */
class SubmitRecurringPayments implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\RecurringPayment\Model\QuoteImporter
     */
    protected $_quoteImporter;

    /**
     * @var array
     */
    protected $_recurringPayments = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\RecurringPayment\Model\QuoteImporter $quoteImporter
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\RecurringPayment\Model\QuoteImporter $quoteImporter
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteImporter = $quoteImporter;
        $this->_quote = $checkoutSession->getQuote();
    }


    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {   
        $tco_payment_options = [\Tco\Checkout\Model\Api::CODE,\Tco\Checkout\Model\Checkout::CODE,\Tco\Checkout\Model\Paypal::CODE,'amasty_stripe'];
        $payment_method = $this->_quote->getPayment()->getMethod();
        $isTcoPaymentMethod = in_array($payment_method, $tco_payment_options);
        
        if($isTcoPaymentMethod == false){   
            $this->_recurringPayments = $this->_quoteImporter->import($this->_quote);
            foreach ($this->_recurringPayments as $payment) {
                if (!$payment->isValid()) {
                    throw new \Magento\Framework\Exception\LocalizedException($payment->getValidationErrors());
                }       
                $payment->submit();
            }
        }        
    }
}
