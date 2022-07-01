<?php
/**
 * Etatvasoft Productattachment
 *
 * @category Etatvasoft
 * @package  Etatvasoft_Productattachment
 * @author   Etatvasoft <magento@etatvasoft.com>
 * @license  http://tatvasoft.com  Open Software License (OSL 3.0)
 * @link     http://tatvasoft.com
 */
namespace Tatva\Ebook\Model\ResourceModel\ProductDownloadEbooksHistoryLog;

/**
 * Class Collection
 *
 * @category Etatvasoft
 * @package  Etatvasoft_Productattachment
 * @author   Etatvasoft <magento@etatvasoft.com>
 * @license  http://tatvasoft.com  Open Software License (OSL 3.0)
 * @link     http://tatvasoft.com
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Primary field
     *
     * @var string
     */
    protected $_idFieldName = 'log_id';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Collection Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory EntityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger        Logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy FetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager  EventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager  StoreManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection    Connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource      Resource
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
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Tatva\Ebook\Model\ProductDownloadEbooksHistoryLog', 'Tatva\Ebook\Model\ResourceModel\ProductDownloadEbooksHistoryLog');
    }

}
