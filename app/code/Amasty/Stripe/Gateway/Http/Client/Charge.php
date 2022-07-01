<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Gateway\Http\Client;

use Amasty\Stripe\Model\StripeAccountManagement;
use Amasty\Stripe\Gateway\Config\Config as StripeConfig;

/**
 * Class Charge
 */
class Charge extends AbstractClient
{
    /**
     * @var StripeAccountManagement
     */
    private $stripeAccountManagement;

    /**
     * @var StripeConfig
     */
    private $stripeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Payment\Model\Method\Logger $paymentLogger,
        \Amasty\Stripe\Model\Adapter\StripeAdapter $adapter,
        \Amasty\Stripe\Gateway\Config\Config $stripeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\State $state,
        StripeAccountManagement $stripeAccountManagement,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($logger, $paymentLogger, $adapter, $stripeConfig, $checkoutSession, $request, $state);
        $this->stripeAccountManagement = $stripeAccountManagement;
        $this->stripeConfig = $stripeConfig;
        $this->moduleManager = $moduleManager;
        $this->_checkoutSession = $_checkoutSession;
        $this->_productFactory = $productFactory;
    }

    /**
     * @param array $data
     *
     * @return \Stripe\ApiResource|\Stripe\Error\Base
     */
    protected function process(array $data)
    {
        if (!isset($data['source'])) {
            $data['source'] = $this->getSaveCardSource();
        }
        if (!$this->moduleManager->isEnabled('Amasty_RecurringPayments') && (int)$data['save_card']
            && !empty($data['payment_method'])
            && $data['payment_method']
        ) {
            $paymentMethod = explode(":", $data['payment_method']);
            $this->stripeAccountManagement->process($paymentMethod);
        }
        $data['customer'] = $this->stripeAccountManagement->getCurrentStripeCustomerId();
        if ($this->isEmailReceiptsEnabled()) {
            $data['receipt_email'] = $this->getReceiptEmail();
        }
        $description = 'Order #' . $data['increment_id'] . ' by ' . $this->getReceiptEmail().' - New';
        
        $itemsArray = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        
        foreach($itemsArray as $item) 
        {            
            $productid = $item->getProductId();
            $productModel = $this->_productFactory->create()->load($productid);
            $subscriptionOnly = $productModel->getAmSubscriptionOnly();
            if($subscriptionOnly)
            {
                $description =  $this->getReceiptEmail()." - ".$data['increment_id']." - ".$productModel->getAttributeText('subscription_period') . " - New";
            }           
        }
       
        
        if (!empty($data['description'])) {
            $description = $data['description']->getText();
        }

        $this->adapter->intentPaymentUpdate(
            $data['source'],
            ['description' => $description]
        );

        return $this->adapter->paymentIntentRetrieve($data['source']);
    }
}
