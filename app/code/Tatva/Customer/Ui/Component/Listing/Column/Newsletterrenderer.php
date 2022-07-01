<?php
namespace Tatva\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Newsletterrenderer extends \Magento\Ui\Component\Listing\Columns\Column
{

   public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        \Magento\Framework\DataObject $row
        ) {
        $this->storeManager = $storeManager;
        $this->row = $row;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as & $item) {
                if($item['type']) {
                    $item['subscriber_status']= 1;
                }
            }
        }
        return $dataSource;
    }
}
