<?php
namespace Tatva\Customerreport\Model;
use Tatva\Subscription\Model\ResourceModel\Subscription\CollectionSubReport;

use Magento\Framework\App\Request\DataPersistorInterface;

class DataProviderReport extends \Magento\Ui\DataProvider\AbstractDataProvider
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
        \Tatva\Subscription\Model\ResourceModel\Subscription\CollectionSubReport $collectionSubReport,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
        ) {
        $this->collection = $collectionSubReport;
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
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
        $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
       // $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        foreach ($items as $item) {
            $itemData = $item->getData();
            $this->loadedData[$item->getSubscriptionHistoryId()]['addSubscription']= $itemData;
        }

        $data = $this->dataPersistor->get('addSubscription');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getSubscriptionHistoryId()]['addSubscription'] = $item->getData();
            $this->dataPersistor->clear('addSubscription');
        }
       
        return $this->loadedData;
    }
}
