<?php
namespace Tatva\Unsubscribepaypal\Ui\Component\Listing\Column;

class Renderer extends \Magento\Ui\Component\Listing\Columns\Column {

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
                $item['firstname'] = $item['firstname']." ".$item['lastname']; //Here you can do anything with actual data

            }
        }

        return $dataSource;
    }
}
?>