<?php
namespace Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var \Magento\Framework\App\ResourceConnectionFactory
     */
    protected $_idFieldName = 'log_id';
    protected $_eventPrefix = 'productdownload_history_log_downloadcount_collection';
    protected $_eventObject = 'productdownloadhistorylog_collection';
    protected $resourceConnectionFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnectionFactory $resourceConnectionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
        $this->resourceConnectionFactory = $resourceConnectionFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
            );
    }

    protected function _construct()
    {
        $this->_init('Tatva\Catalog\Model\Productdownloadhistorylog', 'Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog');
    }
    public function getSelectCountSql()
    {

        $select_sql = $this->getSelect()->__toString();
        $read = $this->resourceConnectionFactory->create()->getConnection('core_read');
        $db = $read->select()->from(new \Zend_Db_Expr("($select_sql)"), array("count(*)"));
        return $db;
    }

}