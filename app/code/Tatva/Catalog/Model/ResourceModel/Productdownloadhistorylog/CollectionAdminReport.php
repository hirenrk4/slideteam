<?php
namespace Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CollectionAdminReport extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected $resourceConnection;
    protected $connection;

    public function __construct(
    	\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
    	\Psr\Log\LoggerInterface $logger,
    	\Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
    	\Magento\Framework\Event\ManagerInterface $eventManager,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
    	\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    	) {

    	$this->_init('Tatva\Catalog\Model\Productdownloadhistorylog', 'Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog');

    	parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    	$this->storeManager = $storeManager;
    	$this->_resource = $resource;

    }
    protected function _initSelect()
    {
    	parent::_initSelect();
      $this->addFilterToMap('recent_download','main_table.download_date');
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

      $model = $objectManager->create('Tatva\Catalog\Model\Productdownloadhistory');

      $recent_download_array = array();
      $connection = $this->getConnection();
      $customerId = $model->getcustomerID();

      $collection=$this->addFieldToFilter('customer_id',$customerId)->addFieldToSelect('product_id');
      $collection->getSelect()->joinLeft(
        ['catalog_product' => $this->getTable('catalog_product_entity')],
        "main_table.product_id = catalog_product.entity_id",
        ['sku']);
      $collection->getSelect()->columns('COUNT(product_id) AS no_of_download');
      $collection->getSelect()->columns('MAX(download_date) AS recent_download');
      $collection->getSelect()->group('product_id');
      /*$collection->setOrder('recent_download','DESC');*/
                  
      return $this;

    }
    public function getConnection()
    {
     if (!$this->connection) {
      $this->connection = $this->_resource->getConnection('core_write');
    }
    return $this->connection;
  }
}
