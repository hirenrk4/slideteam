<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\ProductImport\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class ProductName extends Column {

    protected $_productFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
  public function prepareDataSource(array $dataSource) {
    if (isset($dataSource['data']['items'])) {
        foreach ($dataSource['data']['items'] as & $items) {
             $name = $this->getData('name'); 
            $items['productname'] = html_entity_decode('<a href="'.$items['product_url'].'" target="_blank" style="text-decoration:underline">'.$items['productname'].'</a>');
        }
    }   
 
    return $dataSource;
}
}