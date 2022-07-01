<?php

namespace Tatva\Unsubscribeuser\Ui\Component\Listing\Column;

class PaymentMethod extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {

                //$item['yourcolumn'] is column name
                //$item['yourcolumn'] = $item['yourcolumn']."Test"; //Here you can do anything with actual data
                
                if(!empty($item['two_checkout_message_id']))
                {
                    $item['payment_method'] = "2checkout";
                }
                elseif(!empty($item["stripe_checkout_message_id"]))
                {
                    $item['payment_method'] = "Stripe";
                }
                elseif(!empty($item["paypal_id"]))
                {
                    $item['payment_method'] = "Paypal";
                }
                else
                {
                    $item['payment_method'] = "No Payment Method Define";
                }
            }
        }

        return $dataSource;
    }
}