<?php
namespace Tatva\Metadescription\Model;
use Tatva\Metadescription\Model\ResourceModel\Metadescription\CollectionFactory;

use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
        ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {

       if (isset($this->loadedData)) {
        return $this->loadedData;
    }

    $items = $this->collection->getItems();

    $currentStore =  $this->storeManager->getStore();
    
    foreach ($items as $item) {
        $itemData = $item->getData();
        ;
        $this->loadedData[$item->getId()]['Metadescription']= $itemData;
    }

    $data = $this->dataPersistor->get('metadescription');

    if (!empty($data)) {
        $item = $this->collection->getNewEmptyItem();
        $item->setData($data);

        $this->loadedData[$item->getId()]['Metadescription'] = $item->getData();
        $this->dataPersistor->clear('metadescription');
    }
    return $this->loadedData;
}
}
