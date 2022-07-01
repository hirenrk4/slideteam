<?php

namespace Tatva\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;


class Finalamount extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as & $item) {
                if($item['final_amount'] == NUll || $item['final_amount'] == 0) {
                    $item['final_amount'] = $item['final_amount_paypal'];
                    if ($item['final_amount'] == NUll || $item['final_amount'] == 0) {
                        $item['final_amount'] = $item['final_amount_stripe'];
                    }
                }
            }
        }
        return $dataSource;
    }

}