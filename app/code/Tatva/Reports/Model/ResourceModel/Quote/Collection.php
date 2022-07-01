<?php
namespace Tatva\Reports\Model\ResourceModel\Quote;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Quote\Collection
{
   /**
    * @var \Magento\Customer\Model\ResourceModel\Customer
    */
   protected $customerResource;

   /**
    * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
    * @param \Psr\Log\LoggerInterface $logger
    * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
    * @param \Magento\Framework\Event\ManagerInterface $eventManager
    * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
    * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
    * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
    * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
    */
   public function __construct(
      \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
      \Psr\Log\LoggerInterface $logger,
      \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
      \Magento\Framework\Event\ManagerInterface $eventManager,
      \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
      \Magento\Customer\Model\ResourceModel\Customer $customerResource,
      \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
      \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
   ) {
      parent::__construct(
         $entityFactory,
         $logger,
         $fetchStrategy,
         $eventManager,
         $entitySnapshot,
         $customerResource,
         $connection,
         $resource
      );
      $this->customerResource = $customerResource;
   }

   /**
    * Prepare for abandoned report
    *
    * @param array $storeIds
    * @param string $filter
    * @return $this
    */
   public function prepareForAbandonedReport($storeIds, $filter = null)
   {
      $this->addFieldToFilter(
         'items_count',
         ['neq' => '0']
      )->addFieldToFilter(
         'main_table.is_active',
         '1'
      )->addFieldToFilter(
         'main_table.customer_id',
         ['neq' => null]
      )->addFieldToFilter(
        'main_table.customer_email',
        ['nlike' => 'selenium.testing%']
      )->addSubtotal(
         $storeIds,
         $filter
      )->setOrder(
         'created_at'
      );
      if (isset($filter['email']) || isset($filter['customer_name'])) {
         $this->addCustomerData($filter);
      }
      if (is_array($storeIds) && !empty($storeIds)) {
         $this->addFieldToFilter('store_id', ['in' => $storeIds]);
      }

      return $this;
   }

   public function addCustomerData($filter = null)
    {
        if (isset($filter['customer_name']))
        {
            //$collection->getSelect()->columns();
            $this->getSelect()->where("CONCAT(customer_firstname,' ',customer_lastname)". ' LIKE ?', '%' . $filter['customer_name'] . '%');
        }
        if (isset($filter['email'])) 
        {
            $this->getSelect()->where('customer_email LIKE ?', '%' . $filter['email'] . '%');
        }
        return $this;
    }

    public function resolveCustomerNames()
    {
        $select = $this->customerResource->getConnection()->select();
        //$customerName = $this->customerResource->getConnection()->getConcatSql(['firstname', 'lastname'], ' ');

        $select->from(
            ['customer' => $this->customerResource->getTable('customer_entity')],
            ['entity_id']
        );
        
        $select->where(
            'customer.entity_id IN (?)',
            array_column(
                $this->getData(),
                'customer_id'
            )
        );
        $customersData = $this->customerResource->getConnection()->fetchAll($select);
        foreach ($customersData as $customerItemData)
        {
            $newArray[] = $customerItemData['entity_id'];
        }
        
        foreach ($this->getItems() as $item) {
            

                if (in_array($item['customer_id'],$newArray)) {
                    $customerItemData['is_deleted'] = 0;
                    $item->setData(array_merge($item->getData(),$customerItemData));
                }
                else
                {
                    $customerItemData['is_deleted'] = 1;
                    $item->setData(array_merge($item->getData(),$customerItemData));    
                }
            
        }
    }
}
