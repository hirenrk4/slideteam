<?php

namespace Tatva\PaidCustomerPopup\Model\ResourceModel\CustomerAdditionlData\Grid;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult {


	public function __construct(
		EntityFactoryInterface $entityFactory, 
		LoggerInterface $logger, 
		FetchStrategyInterface $fetchStrategy, 
		ManagerInterface $eventManager, 
		$mainTable, 
		$resourceModel
		) {
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
	}
	public function _initSelect()
	{
		parent::_initSelect();
		$this->getSelect()->joinLeft(
			['secondTable' => $this->getTable('customer_entity')],
			'main_table.customer_id = secondTable.entity_id',
			['email']
		);
		return $this;
	}

	public function getAggregations()
    {
        return $this->_aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }
    
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
        ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

}