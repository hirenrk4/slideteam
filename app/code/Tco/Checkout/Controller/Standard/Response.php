<?php

namespace Tco\Checkout\Controller\Standard;

class Response extends \Tco\Checkout\Controller\Checkout
{

    public function execute()
    {
        // Initialize return url.
        //$date = date('dmY',time());
        

        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');

        try {
            $paymentMethod = $this->getPaymentMethod();
            
            // Get payment method code
            $code = $paymentMethod->getCode();

            // Get params from response
            $params = $this->getRequest()->getParams();

            // Get quote from session
            $quoteId = $this->getQuote()->getId();
            $quote = $this->_quote->load($quoteId);           
           
            // Setup params for hash check
            $orderNumber = $params['order_number'];
            $orderTotal = number_format($quote->getGrandTotal(), 2, '.', '');
            $orderKey = $this->getRequest()->getParam('key');

            // Create the order if the response passes validation
            if ($paymentMethod->validateResponse($orderNumber, $orderTotal, $orderKey))
            {
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');

                if ($this->getCustomerSession()->isLoggedIn()) {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
                }
                else {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
                }

                $customerModel = $this->getCustomerModel()->create();
                $customerModel->setWebsiteId(1);
                $customerModel->loadByEmail($params['email']);
                $customerId =  $customerModel->getEntityId();

                $quote->setCustomerEmail($params['email']);
                $quote->setCustomerId($customerId);
                $quote->setPaymentMethod($code);
                $quote->getPayment()->importData(['method' => $code]);
                $quote->save();

                $this->initCheckout();
                try {

                    $increment_id = $params['merchant_order_id'];
                    $orderCollection = $this->getOrderCollection()->create();
                    $ordCollection = $orderCollection->addFieldToFilter('increment_id', ['eq' => $increment_id]);
                    $count = $ordCollection->getSize();
                    if(!empty($count)) :
                        $returnUrl = $this->getCheckoutHelper()->getUrl('subscription/index/list');                         
                        $this->getResponse()->setRedirect($returnUrl);
                    endif;

                    $this->cartManagement->placeOrder($this->_checkoutSession->getQuote()->getId(), $this->_quote->getPayment());
                    $order = $this->getOrder();
                    $payment = $order->getPayment();
                    $paymentMethod->postProcessing($order, $payment, $params);

                    if ($order) {
                        $this->getCheckoutSession()->setLastOrderId($order->getId())
                            ->setLastRealOrderId($order->getIncrementId())
                            ->setLastOrderStatus($order->getStatus());
                    }

                } catch (\Exception $e) {                    
                    $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
                }

            }
            else
            {                
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {            
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {            
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }

        $this->getResponse()->setRedirect($returnUrl);
    }

}
