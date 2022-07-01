<?php
namespace Tatva\Deleteaccount\Model\ResourceModel\Deletedcustomerbkp;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'customer_id';

	public function __construct(
		EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
		FetchStrategyInterface $fetchStrategy,
		ManagerInterface $eventManager,
		AdapterInterface $connection = null,
		AbstractDb $resource = null
	) 
	{
		$this->_init('Tatva\Deleteaccount\Model\Deletedcustomerbkp', 'Tatva\Deleteaccount\Model\ResourceModel\Deletedcustomerbkp');
		$this->_map['fields']['customer_id'] = 'main_table.customer_id';
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	}
}