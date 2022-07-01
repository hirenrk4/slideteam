<?php
namespace Tatva\Subscription\Model\ResourceModel\SubscriptionInvitation;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class AdminChildCustomerSubReport extends AbstractCollection
{
    protected $_idFieldName = 'id';
    /**
     * Define model & resource model
     */
    protected $resourceConnection;
    protected $connection;
    protected $registry;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
        $this->_init('Tatva\Subscription\Model\Subscription', 'Tatva\Subscription\Model\ResourceModel\Subscription');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->_resource = $resource;
    }
    protected function _initSelect()
    {
        parent::_initSelect();
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $model = $objectManager->create('Tatva\Catalog\Model\Productdownloadhistory');
      if ($model) {
        $customerId = $model->getcustomerID();
        $this->getSelect()->distinct(true)->joinLeft(
          ['cp1' => $this->getTable('customer_entity')],
          'cp1.entity_id=main_table.customer_id',
          ['cp1.email','main_table.from_date','main_table.subscription_period','main_table.to_date','main_table.renew_date','main_table.download_limit','main_table.status_success']
          )->where('main_table.parent_customer_id='.$customerId);

        $this->getSelect()->order('main_table.subscription_history_id desc');

      }else{
        return $this->getSelect();
      }
    }

      public function getConnection()
      {
          if (!$this->connection) {
             $this->connection = $this->_resource->getConnection('core_write');
         }
         return $this->connection;
     }
 }
