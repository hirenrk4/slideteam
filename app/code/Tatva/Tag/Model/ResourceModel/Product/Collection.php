<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Tag
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Tatva\Tag\Model\ResourceModel\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
/**
 * Tagged Product(s) Collection
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Customer Id Filter
     *
     * @var int
     */
    protected $_customerFilterId;

    /**
     * Tag Id Filter
     *
     * @var int
     */
    protected $_tagIdFilter;

    /**
     * Join Flags
     *
     * @var array
     */
    protected $_joinFlags            = array();

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Tatva\Tag\Model\TagFactory
     */
    protected $tagTagFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Tatva\Tag\Model\TagFactory $tagTagFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null,
        TableMaintainer $tableMaintainer = null
    ) {
        $this->moduleManager = $moduleManager;
        $this->_catalogProductFlatState = $catalogProductFlatState;
        $this->_scopeConfig = $scopeConfig;
        $this->_productOptionFactory = $productOptionFactory;
        $this->_catalogUrl = $catalogUrl;
        $this->_localeDate = $localeDate;
        $this->_customerSession = $customerSession;
        $this->_resourceHelper = $resourceHelper;
        $this->dateTime = $dateTime;
        $this->_groupManagement = $groupManagement;
        $this->resourceConnection = $resourceConnection;
        $this->tagTagFactory = $tagTagFactory;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
             $connection
        );
    }
    /**
     * Initialize collection select
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */


    /**
     * Set flag about joined table.
     * setFlag method must be used in future.
     *
     * @deprecated after 1.3.2.3
     *
     * @param string $table
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function setJoinFlag($table)
    {
        $this->setFlag($table, true);
        return $this;
    }

    /**
     * Get flag's status about joined table.
     * getFlag method must be used in future.
     *
     * @deprecated after 1.3.2.3
     *
     * @param string $table
     * @return bool
     */
    public function getJoinFlag($table)
    {
        return $this->getFlag($table);
    }

    /**
     * Unset value of join flag.
     * Set false (bool) value to flag instead in future.
     *
     * @deprecated after 1.3.2.3
     *
     * @param string $table
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function unsetJoinFlag($table = null)
    {
        $this->setFlag($table, false);
        return $this;
    }

    /**
     * Add tag visibility on stores
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addStoresVisibility()
    {
        $this->setFlag('add_stores_after', true);
        return $this;
    }

    /**
     * Add tag visibility on stores process
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    protected function _addStoresVisibility()
    {
        $tagIds = array();
        foreach ($this as $item) {
            $tagIds[] = $item->getTagId();
        }

        $tagsStores = array();
        if (sizeof($tagIds) > 0) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('tag_relation'), array('store_id', 'tag_id'))
                ->where('tag_id IN(?)', $tagIds);
            $tagsRaw = $this->getConnection()->fetchAll($select);
            foreach ($tagsRaw as $tag) {
                if (!isset($tagsStores[$tag['tag_id']])) {
                    $tagsStores[$tag['tag_id']] = array();
                }

                $tagsStores[$tag['tag_id']][] = $tag['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($tagsStores[$item->getTagId()])) {
                $item->setStores($tagsStores[$item->getTagId()]);
            } else {
                $item->setStores(array());
            }
        }

        return $this;
    }

    /**
     * Add group by tag
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addGroupByTag()
    {
        $this->getSelect()->group('relation.tag_relation_id');
        return $this;
    }

    /**
     * Add Store ID filter
     *
     * @param int|array $store
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addStoreFilter($store = null)
    {
        if (!is_null($store)) {
            $this->getSelect()->where('relation.store_id IN (?)', $store);
        }
        return $this;
    }

    /**
     * Set Customer filter
     * If incoming parameter is array and has element with key 'null'
     * then condition with IS NULL or IS NOT NULL will be added.
     * Otherwise condition with IN() will be added
     *
     * @param int|array $customerId
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addCustomerFilter($customerId)
    {
        if (is_array($customerId) && isset($customerId['null'])) {
            $condition = ($customerId['null']) ? 'IS NULL' : 'IS NOT NULL';
            $this->getSelect()->where('relation.customer_id ' . $condition);
            return $this;
        }
        $this->getSelect()->where('relation.customer_id IN(?)', $customerId);
        $this->_customerFilterId = $customerId;
        return $this;
    }

    /**
     * Set tag filter
     *
     * @param int $tagId
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addTagFilter($tagId)
    {
         $this->getSelect()->where('relation.tag_id = ?', $tagId);
        $this->setFlag('distinct', true);
        return $this;
    }

    /**
     * Add tag status filter
     *
     * @param int $status
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()->where('t.status = ?', $status);
        return $this;
    }

    /**
     * Set DESC order to collection
     *
     * @param string $dir
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function setDescOrder($dir = 'DESC')
    {
        $this->setOrder('relation.tag_relation_id', $dir);
        return $this;
    }

    /**
     * Add Popularity
     *
     * @param int $tagId
     * @param int $storeId
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addPopularity($tagId, $storeId = null)
    {
        $tagRelationTable = $this->getTable('tag_relation');

        $condition = array(
            'prelation.product_id=e.entity_id'
        );

        if (!is_null($storeId)) {
            $condition[] = $this->getConnection()->quoteInto('prelation.store_id = ?', $storeId);
        }
        $condition = join(' AND ', $condition);
        $innerSelect = $this->getConnection()->select()
        ->from(
            array('relation' => $tagRelationTable),
            array('product_id', 'store_id', 'popularity' => 'COUNT(DISTINCT relation.tag_relation_id)')
        )
        ->where('relation.tag_id = ?', $tagId)
        ->group(array('product_id', 'store_id'));

        $this->getSelect()
            ->joinLeft(
                array('prelation' => $innerSelect),
                $condition,
                array('popularity' => 'prelation.popularity')
            );

        $this->_tagIdFilter = $tagId;
        $this->setFlag('prelation', true);
        return $this;
    }

    /**
     * Add Popularity Filter
     *
     * @param mixed $condition
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addPopularityFilter($condition)
    {
        $tagRelationTable = $this->resourceConnection
            ->getTableName('tag_relation');

        $select = $this->getConnection()->select()
            ->from($tagRelationTable, array('product_id', 'popularity' => 'COUNT(DISTINCT tag_relation_id)'))
            ->where('tag_id = :tag_id')
            ->group('product_id')
            ->having($this->_getConditionSql('popularity', $condition));

        $prodIds = array();
        foreach ($this->getConnection()->fetchAll($select, array('tag_id' => $this->_tagIdFilter)) as $item) {
            $prodIds[] = $item['product_id'];
        }

        if (sizeof($prodIds) > 0) {
            $this->getSelect()->where('e.entity_id IN(?)', $prodIds);
        } else {
            $this->getSelect()->where('e.entity_id IN(0)');
        }

        return $this;
    }

    /**
     * Set tag active filter to collection
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function setActiveFilter()
    {
        $active = \Tatva\Tag\Model\Tag\Relation::STATUS_ACTIVE;
        $this->getSelect()->where('relation.active = ?', $active);
        if ($this->getFlag('prelation')) {
            $this->getSelect()->where('prelation.active = ?', $active);
        }
        return $this;
    }

    /**
     * Add Product Tags
     *
     * @param int $storeId
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function addProductTags($storeId = null)
    {
        foreach ($this->getItems() as $item) {
            $tagsCollection = $this->tagTagFactory->create()->getResourceCollection();

            if (!is_null($storeId)) {
                $tagsCollection->addStoreFilter($storeId);
            }

            $tagsCollection->addPopularity()
                ->addProductFilter($item->getEntityId())
                ->addCustomerFilter($this->_customerFilterId)
                ->setActiveFilter();

            $tagsCollection->load();
            $item->setProductTags($tagsCollection);
        }

        return $this;
    }

    /**
     * Join fields process
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
    */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->_joinFields();
        $this->getSelect()->group('e.entity_id');

        /*
         * Allow analytic function usage
        */
        $this->_useAnalyticFunction = true;

        return $this;
    }
    
    protected function _joinFields()
    {
        $tagTable           = $this->getTable('tag');
        $tagRelationTable   = $this->getTable('tag_relation');

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('small_image');

        $this->getSelect()
            ->join(array('relation' => $tagRelationTable), 'relation.product_id = e.entity_id', array(
                'product_id'    => 'product_id',
                'item_store_id' => 'store_id',
            ))
            ->join(array('t' => $tagTable),
                't.tag_id = relation.tag_id',
                array(
                    'tag_id',
                    'tag_status' => 'status',
                    'tag_name'   => 'name',
                    'store_id'   => $this->getConnection()->getCheckSql(
                        't.first_store_id = 0',
                        'relation.store_id',
                        't.first_store_id'
                    )
                )
            );

        return $this;
    }

    /**
     * After load adding data
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */

    //   protected function _afterLoad()
    // {
    //     if ($this->_addUrlRewrite) {
    //         $this->_addUrlRewrite();
    //     }

    //     $this->_prepareUrlDataObject();

    //     if (count($this)) {
    //         $this->_eventManager->dispatch('tag_tag_product_collection_load_after', ['collection' => $this]);
    //     }

    //     return $this;
    // }


    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();

        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::GROUP);

         if ($this->getFlag('group_tag')) {
            $field = 'relation.tag_id';
        } else {
            $field = 'e.entity_id';
        }

        $expr = 'COUNT('
            . ($this->getFlag('distinct') ? 'DISTINCT ' : '')
            . $field . ')';

        $countSelect->columns($expr);
        return $countSelect;
    }

    /**
     * Treat "order by" items as attributes to sort
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            parent::_renderOrders();

            $orders = $this->getSelect()
                ->getPart(\Zend_Db_Select::ORDER);

            $appliedOrders = array();
            foreach ($orders as $order) {
                $appliedOrders[$order[0]] = true;
            }

            foreach ($this->_orders as $field => $direction) {
                if (empty($appliedOrders[$field])) {
                    $this->_select->order(new \Zend_Db_Expr($field . ' ' . $direction));
                }
            }
        }
        return $this;
    }

    /**
     * Set Id Fieldname as Tag Relation Id
     *
     * @return Mage_Tag_Model_Resource_Product_Collection
     */
    public function setRelationId()
    {
        $this->_setIdFieldName('tag_relation_id');
        return $this;
    }
}
