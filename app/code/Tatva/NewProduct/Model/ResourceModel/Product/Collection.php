<?php
namespace Tatva\NewProduct\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Category;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_maxSize = null;

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
            \Magento\Framework\App\Request\Http $request,
            \Magento\Catalog\Model\Session $catalogSession,
            \Magento\Framework\View\LayoutInterface $layoutInterface = null,
            \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
            ProductLimitationFactory $productLimitationFactory = null,
            MetadataPool $metadataPool = null,
            TableMaintainer $tableMaintainer = null, 
            PriceTableResolver $priceTableResolver = null, 
            DimensionFactory $dimensionFactory = null, 
            Category $categoryResourceModel = null
    ) {

        $this->_request = $request;
        $this->catalogSession = $catalogSession;
        $this->layoutInterface = $layoutInterface;
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
                $connection, 
                $productLimitationFactory,
                $metadataPool, 
                $tableMaintainer, 
                $priceTableResolver, 
                $dimensionFactory, 
                $categoryResourceModel);

    }

    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        if ($this->_request->getModuleName() == 'new-powerpoint-templates') {
            $select->reset(\Magento\Framework\DB\Select::GROUP);
        }

        return $select;
    }

    public function setMaxSize($maxSize){
        $maxSize = intval($maxSize);
        $this->_maxSize = $maxSize > 0 ? $maxSize : 0;
        return $this;
    }

    public function getMaxSize(){
        return $this->_maxSize;
    }

    public function getSize(){
        $size = parent::getSize();
        $max_size = $this->getMaxSize();
        if (!empty($max_size)) {
            return $max_size < $size ? $max_size : $size;
        }

        return $size;
    }

    /**
     * Load entities records into items
     *
     * @throws \Exception
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
  
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $entity = $this->getEntity();

       if ($this->_pageSize) {
            if (!empty($this->_maxSize)
                && $this->getCurPage() == $this->getLastPageNumber()
                   && ($this->_maxSize % $this->_pageSize > 0) ) {
                       $this->getSelect()->limit($this->_maxSize % $this->_pageSize, ($this->getCurPage() - 1) * $this->_pageSize);
            }
            else{
                $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
            }
        }

        $this->printLogQuery($printQuery, $logQuery);

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->getSelect();
            $rows = $this->_fetchAll($query);
        } catch (\Exception $e) {
            $this->printLogQuery(true, true, $query);
            throw $e;
        }

        foreach ($rows as $v) {
            $object = $this->getNewEmptyItem()
                ->setData($v);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = array($object);
            }
        }

        return $this;
    }

    /**
     * Overwrite from parent
     */
    public function getLastPageNumber(){
        $lastPageNum = parent::getLastPageNumber();
        
        $this->catalogSession->unsLimitPage();
        $default_limit = $this->_scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != null ? intval($this->_scopeConfig->getValue('catalog/frontend/grid_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) : 30 ;
        $limit = (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : $default_limit;
        
        $popular_widget_status = $this->moduleManager->isEnabled("Tatva_Popularwidget");
        if($popular_widget_status == true){
            $popular_widget_block = $this->layoutInterface->createBlock('popularwidget/popularwidget');
            $popularWidget_flags = $popular_widget_block->categoryHasPopularWidget_withoutPageFlag();
            $flag = $popularWidget_flags['main'];
            $mostDownloadWidget_flags = $popular_widget_block->getMostDownloadWidgetFlags();
            $flag_mstDwnWidget =  $mostDownloadWidget_flags['main-page_flag'];
        }
        else{
            $flag = false;
            $flag_mstDwnWidget = false;
        }

        /*$popular_widget_block = Mage::getSingleton('core/layout')->createBlock('popularwidget/popularwidget');
        $popularWidget_flags = $popular_widget_block->categoryHasPopularWidget_withoutPageFlag();
        $flag = $popularWidget_flags['main'];*/
        //$flag = false;
        if($flag){
                
                $popular_widget_products = $popular_widget_block->getPopularwidgetAllProducts();
                
                $collectionSize = $this->getSize();
                $firstPageProduct = $limit - $popular_widget_products ;
                $lastPageNo = ceil((($collectionSize-$firstPageProduct)/$limit) + 1 );
                $lastPageNum = $lastPageNo;
        }

        /*$mostDownloadWidget_flags = $popular_widget_block->getMostDownloadWidgetFlags();
        $flag_mstDwnWidget =  $mostDownloadWidget_flags['main-page_flag'];*/

        if($flag_mstDwnWidget){
            $mstDwnWidgetProducts = $popular_widget_block->getMostDownloadWidget_productNums();

            $collectionSize = $this->getSize();
            $firstPageProduct = $limit - $mstDwnWidgetProducts ;
            $lastPageNo = ceil((($collectionSize-$firstPageProduct)/$limit) + 1 );
            $lastPageNum = $lastPageNo;
        }


        return $lastPageNum;
    }

    protected function _addUrlRewrite()
    {
        $productIds = [];
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getEntityId();
        }
        if (!$productIds) {
            return;
        }

        $select = $this->getConnection()
            ->select()
            ->from(['u' => $this->getTable('url_rewrite')], ['u.entity_id', 'u.request_path'])
            ->where('u.store_id = ?', $this->_storeManager->getStore($this->getStoreId())->getId())
            ->where('u.is_autogenerated = 1')
            ->where('u.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE)
            ->where('u.entity_id IN(?)', $productIds)
            ->order('u.target_path',Select::SQL_ASC);
            
        if ($this->_urlRewriteCategory) {
            $select->joinInner(
                ['cu' => $this->getTable('catalog_url_rewrite_product_category')],
                'u.url_rewrite_id=cu.url_rewrite_id'
            )->where('cu.category_id IN (?)', $this->_urlRewriteCategory);
        }

        // more priority is data with category id
        $urlRewrites = [];

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            if (!isset($urlRewrites[$row['entity_id']])) {
                $urlRewrites[$row['entity_id']] = $row['request_path'];
            }
        }

        foreach ($this->getItems() as $item) {
            if (isset($urlRewrites[$item->getEntityId()])) {
                $item->setData('request_path', $urlRewrites[$item->getEntityId()]);
            } else {
                $item->setData('request_path', false);
            }
        }
    }
}
?>