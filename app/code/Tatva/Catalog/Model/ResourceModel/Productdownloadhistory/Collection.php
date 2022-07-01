<?php
namespace Tatva\Catalog\Model\ResourceModel\Productdownloadhistory;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var \Magento\Framework\App\ResourceConnectionFactory
     */
    protected $_idFieldName = 'download_history_id';
    protected $_eventPrefix = 'productdownload_history_log_downloadcount_collection';
    protected $_eventObject = 'productdownloadhistory_collection';
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
        $this->_init('Tatva\Catalog\Model\Productdownloadhistory', 'Tatva\Catalog\Model\ResourceModel\Productdownloadhistory');
    }

}