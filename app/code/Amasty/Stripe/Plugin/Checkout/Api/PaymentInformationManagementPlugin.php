<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


declare(strict_types=1);

namespace Amasty\Stripe\Plugin\Checkout\Api;

use Amasty\Stripe\Gateway\Command\PaymentCancellation;
use Magento\Checkout\Api\PaymentInformationManagementInterface;

class PaymentInformationManagementPlugin
{
    /**
     * @var PaymentCancellation
     */
    private $paymentCancellation;

    public function __construct(PaymentCancellation $paymentCancellation)
    {
        $this->paymentCancellation = $paymentCancellation;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return int|void
     * @throws \Exception
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        try {
            return $proceed($cartId, $paymentMethod, $billingAddress);
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/stripe-debug/stripe-default-order-error.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);   
            $logger->info(">>>>>>>>>>>>>>>>>>>>>>");
            $logger->info($e);
            $logger->info("<<<<<<<<<<<<<<<<<<<<<<");
            $this->paymentCancellation->execute($paymentMethod);

            throw $e;
        }
    }
}
