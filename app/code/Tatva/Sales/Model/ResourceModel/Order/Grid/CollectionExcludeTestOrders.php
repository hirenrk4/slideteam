<?php

namespace Tatva\Sales\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Api\Search\SearchResultInterface as SearchInterface;

class CollectionExcludeTestOrders extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetch,
        \Magento\Framework\Event\ManagerInterface $event,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        $mainTable = 'sales_order_grid',
        $eventPrefix,
        $eventObject,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order::class,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct(
            $entity,
            $logger,
            $fetch,
            $event,
            $mainTable,
            $resourceModel
        );
        $this->_construct();
        $this->setConnection($this->getResource()->getConnection());
        $this->_initSelect();
    }


    protected function _initSelect()
    {   
        $email = $this->_scopeConfig->getValue('sales_email/order/sales_order_exclude_qa');
        if(!empty($email)) {
            $this->getSelect()->from(['main_table' => $this->getMainTable()])->where('customer_email NOT LIKE "%'.$email.'%"');
        }
        else{
            $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        }
        $this->getSelect()
        ->columns(
            array(
                'products' => new \Zend_Db_Expr('(SELECT GROUP_CONCAT(`sku` SEPARATOR " , ") FROM `sales_order_item` WHERE `sales_order_item`.`order_id` = main_table.`entity_id` GROUP BY `sales_order_item`.`order_id`)')
            )
        )
        ->columns(
            array(
                'coupon_code' => new \Zend_Db_Expr('(SELECT coupon_code FROM sales_order WHERE sales_order.entity_id = main_table.entity_id)')
            )
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

    // public function getItems(){

    //     return $this;
    // }
    
}
