<?php
namespace Tatva\Tag\Model;
use Tatva\Tag\Model\ResourceModel\Tag\CollectionFactory;

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
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
        ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;

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

        foreach ($items as $item) {
            $item->setStoreId($this->request->getParam('store'));
            $this->loadedData[$item->getId()]['Tag']= $item->getData();            
        }

        $data = $this->dataPersistor->get('Tag');

        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
                   
                    
            $this->loadedData[$item->getId()]['Tag'] = $item->getData();
            $this->dataPersistor->clear('tag');
        }
        
        return $this->loadedData;
    }
}
