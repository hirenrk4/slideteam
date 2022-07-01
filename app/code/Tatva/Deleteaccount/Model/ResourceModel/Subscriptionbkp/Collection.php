<?php

namespace Tatva\Deleteaccount\Model\ResourceModel\Subscriptionbkp;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'subscription_bkp_id';

	public function __construct(
		EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
		FetchStrategyInterface $fetchStrategy,
		ManagerInterface $eventManager,
		AdapterInterface $connection = null,
		AbstractDb $resource = null
	) 
	{
		$this->_init('Tatva\Deleteaccount\Model\Subscriptionbkp', 'Tatva\Deleteaccount\Model\ResourceModel\Subscriptionbkp');   
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	}
}