<?php

namespace Tatva\Customerloginlog\Model\ResourceModel\Loginlog;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'customerloginlog_id';

	public function __construct(
		EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
		FetchStrategyInterface $fetchStrategy,
		ManagerInterface $eventManager,
		AdapterInterface $connection = null,
		AbstractDb $resource = null
	) 
	{
		$this->_init('Tatva\Customerloginlog\Model\Loginlog', 'Tatva\Customerloginlog\Model\ResourceModel\Loginlog');   
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