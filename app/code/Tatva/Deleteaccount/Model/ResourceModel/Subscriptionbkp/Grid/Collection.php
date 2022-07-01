<?php

namespace Tatva\Deleteaccount\Model\ResourceModel\Subscriptionbkp\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Tatva\Deleteaccount\Model\ResourceModel\Subscriptionbkp\Collection as GridCollection;

class Collection extends GridCollection implements SearchResultInterface
{
	public function __construct
	(
		EntityFactoryInterface $entityFactory,
		LoggerInterface $logger,
		FetchStrategyInterface $fetchStrategy,
		ManagerInterface $eventManager,
		StoreManagerInterface $storeManager,
		$mainTable,
		$eventPrefix,
		$eventObject,
		$resourceModel,
		$model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
		$connection = null,
		AbstractDb $resource = null
	) 
	{
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
		$this->_eventPrefix = $eventPrefix;
		$this->_eventObject = $eventObject;
		$this->_init($model, $resourceModel);
		$this->setMainTable($mainTable);
	}

	public function getAggregations()
	{
		return $this->aggregations;
	}

	public function setAggregations($aggregations)
	{
		$this->aggregations = $aggregations;
	}

	public function getSearchCriteria()
	{
		return null;
	}

	public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null) 
	{
		return $this;
	}

	public function getTotalCount()
	{
		return $this->getSize();
	}

	public function setTotalCount($totalCount)
	{
		return $this;
	}

	public function setItems(array $items = null)
	{
		return $this;
	}
}