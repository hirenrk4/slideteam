<?php
namespace Tatva\Subscription\Model\ResourceModel\Subscription;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CollectionSubReport extends AbstractCollection
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
        $this->_init('Tatva\Subscription\Model\Subscription', 'Tatva\Subscription\Model\ResourceModel\Subscription');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->_resource = $resource;

    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $query = "SELECT max(subscription_history_id) as `subscription_history_id` FROM `subscription_history` AS `main_table` GROUP BY `customer_id`"; 
        $result = $this->getConnection()->fetchAll($query);
        $ids=array();
        if(is_array($result) && count($result)>0)
        {
          foreach($result as $hid)
          {
            $ids[]=$hid["subscription_history_id"];
        }
    }
    $s_id=implode(',',$ids);

    if(count($ids)>0)
    {
        $this->getSelect()->joinRight(
            ['secondTable' => $this->getTable('customer_entity')],
            'secondTable.entity_id =main_table.customer_id',
            ['firstname','lastname','email']
            )->where('main_table.subscription_history_id  IN ( '.$s_id.')');
        $this->getSelect()->joinLeft(
            ['thirdTable' => $this->getTable('sales_order')],
            'thirdTable.increment_id =main_table.increment_id',
            ['coupon_code']
            )->group('main_table.customer_id');

      return $this;
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
