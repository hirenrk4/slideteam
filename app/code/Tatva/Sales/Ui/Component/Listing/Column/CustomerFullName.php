<?php

namespace Tatva\Sales\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;

class CustomerFullName extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $components = [],
        array $data = [])
    {
        $this->_searchCriteria  = $criteria;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $order  = $this->orderFactory->create()->load($items['entity_id']);
                $items['customerfullname'] = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
            }
        }
        return $dataSource;
    }
}