<?php
namespace Tatva\Formbuild\Model\Post;

use Tatva\Formbuild\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 *
 * @category Etatvasoft
 * @package  Etatvasoft_Productattachment
 * @author   Etatvasoft <magento@etatvasoft.com>
 * @license  http://tatvasoft.com  Open Software License (OSL 3.0)
 * @link     http://tatvasoft.com
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Collection
     *
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * Data Persistor
     *
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * Loaddata
     *
     * @var array
     */
    protected $loadedData;

    /**
     * Storemanager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public    $storeManager;

    /**
     * Constructor
     *
     * @param string                 $name                               name
     * @param string                 $primaryFieldName                   Primaryfieldname
     * @param string                 $requestFieldName                   Requestfieldname
     * @param CollectionFactory      $productattachmentCollectionFactory ProductattachmentCollectionFactory
     * @param StoreManagerInterface  $storeManager                       StoreManager
     * @param DataPersistorInterface $dataPersistor                      DataPersistor
     * @param array                  $meta                               Meta
     * @param array                  $data                               Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $postCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $postCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager=$storeManager;
        
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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

        foreach ($items as $block) {

            $this->loadedData[$block->getFormId()] = $block->getData();

            if ($block->getImage()) {
                $m['image'][0]['name'] = $block->getImage();
                $m['image'][0]['url'] = $this->getMediaUrl().$block->getImage();
                $fullData = $this->loadedData;
                $this->loadedData[$block->getFormId()] = array_merge($fullData[$block->getFormId()], $m);
            }
            
        }

        $data = $this->dataPersistor->get('post');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getFormId()] = $block->getData();
            $this->dataPersistor->clear('post');
        }

        return $this->loadedData;
    }

    /**
     * Return media path for attachment module
     *
     * @return string
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'tatva/';
        return $mediaUrl;
    }
}
