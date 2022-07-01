<?php
namespace Tatva\Sentence\Ui\Component\Listing\DataProviders\Tatva\Sentence;

class Listing extends \Magento\Ui\DataProvider\AbstractDataProvider
{    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Tatva\Sentence\Model\ResourceModel\Sentence\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
        ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
