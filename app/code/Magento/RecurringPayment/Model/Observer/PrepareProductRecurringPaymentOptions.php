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
class PrepareProductRecurringPaymentOptions implements ObserverInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Recurring payment factory
     *
     * @var \Magento\RecurringPayment\Model\RecurringPaymentFactory
     */
    protected $_recurringPaymentFactory;

    /**
     * @var \Magento\RecurringPayment\Block\Fields
     */
    protected $_fields;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\RecurringPayment\Model\RecurringPaymentFactory  $recurringPaymentFactory
     * @param \Magento\RecurringPayment\Block\Fields $fields
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\RecurringPayment\Model\RecurringPaymentFactory $recurringPaymentFactory,
        \Magento\RecurringPayment\Block\Fields $fields
    ) {
        $this->_storeManager = $storeManager;
        $this->_recurringPaymentFactory = $recurringPaymentFactory;
        $this->_fields = $fields;
        $this->_locale = $locale;
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
        $product = $observer->getEvent()->getProduct(); 
        $buyRequest = $observer->getEvent()->getBuyRequest();
       
        if (!$product->getIsRecurring()) {
            return;
        }

        /** @var \Magento\RecurringPayment\Model\RecurringPayment $payment */
        $payment = $this->_recurringPaymentFactory->create(['locale' => $this->_locale]);
        $payment->setStore($this->_storeManager->getStore())
            ->importBuyRequest($buyRequest)
            ->importProduct($product);
        
        if (!$payment) {
            return;
        }

        // add the start datetime as product custom option
        $product->addCustomOption(\Magento\RecurringPayment\Model\RecurringPayment::PRODUCT_OPTIONS_KEY,
            serialize(array('start_datetime' => $payment->getStartDatetime()))
        );

        // duplicate as 'additional_options' to render with the product statically
        $infoOptions = array(array(
            'label' => $this->_fields->getFieldLabel('start_datetime'),
            'value' => $payment->exportStartDatetime(),
        ));

        foreach ($payment->exportScheduleInfo() as $info) {
            $infoOptions[] = array(
                'label' => $info->getTitle(),
                'value' => $info->getSchedule(),
            );
        }
        $product->addCustomOption('additional_options', serialize($infoOptions));
    }
}
