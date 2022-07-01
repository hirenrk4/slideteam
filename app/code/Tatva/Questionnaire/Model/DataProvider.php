<?php

namespace Tatva\Questionnaire\Model;

use Tatva\Questionnaire\Model\ResourceModel\Questionnaire\CollectionFactory;
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
    $name, $primaryFieldName, $requestFieldName, CollectionFactory $collectionFactory, DataPersistorInterface $dataPersistor, array $meta = [], array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
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
        if (isset($this->loadedData))
        {
            return $this->loadedData;

        }
        $items = $this->collection->getItems();
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        // $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($items as $item)
        {
            $itemData = $item->getData();

            $this->loadedData[$item->getId()]['questionnaireData'] = $itemData;
        }

        $data = $this->dataPersistor->get('questionnaire');
        if (!empty($data))
        {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            echo $item->getId();die;
            $this->loadedData[$item->getId()]['questionnaireData'] = $item->getData();
            $this->dataPersistor->clear('questionnaire');
        }
        return $this->loadedData;
    }

}
