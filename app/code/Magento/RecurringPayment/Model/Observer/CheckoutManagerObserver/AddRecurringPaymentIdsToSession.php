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
namespace Magento\RecurringPayment\Model\Observer\CheckoutManagerObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddRecurringPaymentIdsToSession implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\RecurringPayment\Model\QuoteImporter
     */
    protected $_quoteImporter;

    /**
     * @var array
     */
    protected $_recurringPayments = null;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;
    


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
        $this->_checkoutSession = $checkoutSession;
        $this->_quote = $checkoutSession->getQuote();
    }


    /**
     * Add recurring payment ids to session
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->_recurringPayments) {
            $ids = array();
            foreach ($this->_recurringPayments as $payment) {
                $ids[] = $payment->getId();
            }
            $this->_checkoutSession->setLastRecurringPaymentIds($ids);
     
        }
    }
}
