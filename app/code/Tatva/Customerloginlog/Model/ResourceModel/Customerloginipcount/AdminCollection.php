<?php

namespace Tatva\Customerloginlog\Model\ResourceModel\Customerloginipcount;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AdminCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'customerloginipcount_id';

	public function __construct(
		EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
		FetchStrategyInterface $fetchStrategy,
		ManagerInterface $eventManager,
		AdapterInterface $connection = null,
		AbstractDb $resource = null
	) 
	{		
		$this->_init('Tatva\Customerloginlog\Model\Customerloginipcount', 'Tatva\Customerloginlog\Model\ResourceModel\Customerloginipcount');   
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	}
	
	protected function _initSelect()
	{
		parent::_initSelect();
		$this->getSelect()->join(
			['secondTable' => $this->getTable('customer_grid_flat')],
			'secondTable.entity_id = main_table.customer_id',
			['email']
		);
	
		return $this;
	}
}