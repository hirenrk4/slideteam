<?php
namespace Tatva\Subscription\Model\ResourceModel\Subscription;


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
			$this->_init('Tatva\Subscription\Model\Subscription', 'Tatva\Subscription\Model\ResourceModel\Subscription');
			parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
			$this->storeManager = $storeManager;
		}
	}
