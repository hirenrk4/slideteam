<?php

namespace Tatva\Customerloginlog\Model\ResourceModel\Customerloginipcount;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'customerloginipcount_id';

	public function __construct(
		\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
	) 
	{		
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	}
	
	protected function _construct()
    {
        $this->_init('Tatva\Customerloginlog\Model\Customerloginipcount', 'Tatva\Customerloginlog\Model\ResourceModel\Customerloginipcount');   
    }
}