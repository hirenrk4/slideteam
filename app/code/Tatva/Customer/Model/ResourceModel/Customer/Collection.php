<?php
namespace Tatva\Customer\Model\ResourceModel\Customer;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
       $this->_init('Tatva\Customer\Model\Customer', 'Tatva\Customer\Model\ResourceModel\Customer');
       parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
       $this->storeManager = $storeManager;
        $this->_resource = $resource;
   }
   protected function _initSelect()
   {
    parent::_initSelect();
     $connection = $this->_resource->getConnection();
	 $result=$connection->fetchAll("SELECT DISTINCT(customer_id)  FROM subscription_history");

      $ids=array();
      if(is_array($result) && count($result)>0)
      {
          foreach($result as $hid)
          {
                $ids[]=$hid["customer_id"];
          }
      }
      $collection="";
      	
      if(count($ids)>0)    		
      	$id=implode(",",$ids);

	  $collection =$this->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(array('entity_id','firstname','lastname','email','created_at'));

      $collection->where("entity_id NOT IN (".$id.")");   
}

}
