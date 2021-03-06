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
namespace Magento\RecurringPayment\Model\Observer;

use \Magento\Quote\Model\Quote;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentAvailabilityObserver implements ObserverInterface
{
    /** @var  \Magento\RecurringPayment\Model\Quote\Filter */
    protected $quoteFilter;
    /** @var  \Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification */
    protected $specification;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;
    

    protected $_checkoutSession;


    /**
     * @param \Magento\RecurringPayment\Model\Quote\Filter $quoteFilter
     * @param \Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification $specification
     */
    public function __construct(
        \Magento\RecurringPayment\Model\Quote\Filter $quoteFilter,
        \Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification $specification,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quoteFilter = $quoteFilter;
        $this->specification = $specification;
        $this->_checkoutSession = $checkoutSession;
        $this->_quote = $checkoutSession->getQuote();
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // $quote = $observer->getEvent()->getQuote();
        $quote = $this->_quote;
        /** @var \Magento\Payment\Model\Method\AbstractMethod $paymentMethod */
        $paymentMethod = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        
        if ($quote
            && $this->quoteFilter->hasRecurringItems($quote)
            && !$this->specification->isSatisfiedBy($paymentMethod->getCode())
        ) {
            $result->isAvailable = false;
        }
    }
}
