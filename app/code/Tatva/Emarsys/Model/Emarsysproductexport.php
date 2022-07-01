<?php
namespace Tatva\Emarsys\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Emarsys\Emarsys\Helper\Data as EmarsysDataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\CurrencyFactory;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

use Magento\Framework\Model\AbstractModel;

class Emarsysproductexport extends \Emarsys\Emarsys\Model\Emarsysproductexport
{
    public function __construct(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
            StoreManagerInterface $storeManager, 
            ScopeConfigInterface $scopeConfig, 
            CurrencyFactory $currencyFactory, 
            \Magento\Framework\Filesystem\Io\File $ioFile,
            \Magento\Framework\File\Csv $csvWriter, 
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\CatalogInventory\Helper\Stock $stockFilter, 
            Context $context, 
            Registry $registry, 
            AbstractResource $resource = null, 
            AbstractDb $resourceCollection = null, 
            array $data = array()
            ){
        parent::__construct(
                $productCollectionFactory, 
                $storeManager, 
                $scopeConfig, 
                $currencyFactory, 
                $ioFile, 
                $csvWriter,
                $dir,
                $stockFilter, 
                $context,
                $registry,
                $resource,
                $resourceCollection, 
                $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Emarsys\Emarsys\Model\ResourceModel\Emarsysproductexport');
    }

    /**
     * Get Catalog Product Export Collection
     * @param int|object $storeId
     * @param int $currentPageNumber
     * @param array $attributes
     * @return object
     */
	public function getCatalogExportProductCollectionCount($storeId, $currentPageNumber, $attributes, $includeBundle, $excludedCategories)
	{
		try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore($storeId);

            // $resumecategoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
            // $ebookcategoryId = $this->scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $collection = $this->productCollectionFactory->create()
                //->addStoreFilter($store->getId())
                //->setPageSize(self::BATCH_SIZE)
                //->setCurPage($currentPageNumber)
                ->addAttributeToSelect($attributes)
                ->addAttributeToSelect(['visibility'])
                ->addAttributeToFilter('type_id',array('downloadable','simple','virtual'))
                //->addAttributeToFilter('is_ebook',array(array('null'=>true),array('neq'=>1)),'left')
                ->setStoreId($storeId)
                ->addCategoryIds();

            // $catalog_ids = [$resumecategoryId,$ebookcategoryId];
            // $collection->addCategoriesFilter(array('nin' => $catalog_ids));

            $collection->getSelect()->joinLeft(["product_website" => $collection->getTable("catalog_product_website")], "(`product_website`.`product_id` = `e`.`entity_id`) AND (`product_website`.`website_id` = '1')", []);
            $collection = $collection->addUrlRewrite();


            if (is_null($includeBundle)) {
                $includeBundle = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_INCLUDE_BUNDLE_PRODUCT);
            }

            if (!$includeBundle) {
                $collection->addAttributeToFilter('type_id', ['neq' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
            }
            if (is_null($excludedCategories)) {
                $excludedCategories = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_EXCLUDED_CATEGORIES);
            }
            if ($excludedCategories) {
                $excludedCategories = explode(',', $excludedCategories);
                $collection->addCategoriesFilter(['nin' => $excludedCategories]);
            }

            $this->stockFilter->addInStockFilterToCollection($collection);
		 	return $collection;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
			
	}
    public function getCatalogExportProductCollection($storeId, $currentPageNumber, $attributes, $includeBundle, $excludedCategories)
    {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore($storeId);

            // $resumecategoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
            // $ebookcategoryId = $this->scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);            

            $collection = $this->productCollectionFactory->create()
                //->addStoreFilter($store->getId())
                //->setPageSize(self::BATCH_SIZE)
                //->setCurPage($currentPageNumber)
                ->addAttributeToSelect($attributes)
                ->addAttributeToSelect(['visibility'])
                ->addAttributeToFilter('type_id',array('downloadable','simple','virtual'))
                ->addAttributeToFilter('is_ebook',array(array('null'=>true),array('neq'=>1)),'left')
                ->setStoreId($storeId)
                ->addCategoryIds();

            // $catalog_ids = [$resumecategoryId,$ebookcategoryId];
            // $collection->addCategoriesFilter(array('nin' => $catalog_ids));

            $collection->getSelect()->joinLeft(["product_website" => $collection->getTable("catalog_product_website")], "(`product_website`.`product_id` = `e`.`entity_id`) AND (`product_website`.`website_id` = '1')", []);
            $collection = $collection->addUrlRewrite();
            
            
            if (is_null($includeBundle)) {
                $includeBundle = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_INCLUDE_BUNDLE_PRODUCT);
            }

            if (!$includeBundle) {
                $collection->addAttributeToFilter('type_id', ['neq' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]);
            }
            if (is_null($excludedCategories)) {
                $excludedCategories = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_EXCLUDED_CATEGORIES);
            }
            if ($excludedCategories) {
                $excludedCategories = explode(',', $excludedCategories);
                $collection->addCategoriesFilter(['nin' => $excludedCategories]);
            }

            $this->stockFilter->addInStockFilterToCollection($collection);			
		   
			/*$collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);*/
			
			$queryFirst = new \Zend_Db_Expr("(SELECT CONCAT(GROUP_CONCAT(DISTINCT cgv.entity_id),' && ', CONCAT(COUNT(DISTINCT cg.value)),' && ',GROUP_CONCAT(DISTINCT cg.value)) 
            FROM `catalog_product_entity_media_gallery_value_to_entity` as cgv
            LEFT JOIN catalog_product_entity_media_gallery as cg 
            ON cgv.value_id = cg.value_id
			LEFT JOIN `catalog_product_entity_media_gallery_value` as cv
			ON cg.value_id =cv.value_id 
			WHERE 
			cgv.entity_id = `e`.`entity_id`
			Group BY cgv.entity_id
			)");
			
			/*$querySecond = new \Zend_Db_Expr("(
			SELECT CONCAT(GROUP_CONCAT(cv.entity_id),' && ',GROUP_CONCAT(cg.value)) 
			FROM `catalog_product_entity_media_gallery_value` as cv
			LEFT JOIN catalog_product_entity_media_gallery as cg 
			ON cg.value_id =cv.value_id 
			WHERE 
			cv.entity_id = `e`.`entity_id`
			Group BY cv.entity_id
			)");*/
			
			$queryThird = new \Zend_Db_Expr("(
			SELECT GROUP_CONCAT(cat.path)
			FROM `catalog_category_entity` as cat 
			Where entity_id IN (SELECT category_id From catalog_category_product Where product_id=`e`.`entity_id`)
			)");
			
			$queryForth = new \Zend_Db_Expr("(SELECT GROUP_CONCAT(DISTINCT `cat_val_name`.`value`) FROM `catalog_category_entity` AS `cat_main` 
			LEFT JOIN `catalog_category_entity_varchar` as `cat_val_name` 
			ON `cat_main`.entity_id = `cat_val_name`.entity_id 
			WHERE (`cat_val_name`.`attribute_id` = 33) 
			AND (`cat_main`.`level` >= 4) 
			AND (`cat_main`.`entity_id` IN (SELECT `c_product`.category_id From catalog_category_product as `c_product` Where `c_product`.product_id = `e`.`entity_id`)))");
			
			$queryFifth = new \Zend_Db_Expr("(SELECT COUNT(DISTINCT customer_id) from `productdownload_history_log` as `down_tbl` 
			where `e`.`entity_id` = `down_tbl`.`product_id` group by `e`.`entity_id`)");
			//$collection->getSelect()->columns('e.*');
				
			$collection->getSelect()->columns(array('gallary_data_default' => $queryFirst,
													//'gallary_data_gen' => $querySecond,
													'category_path' => $queryThird,
													'category_name' => $queryForth,
													'download_count' => $queryFifth));			
			
			if($currentPageNumber == 1)
			{
				$collection->getSelect()->limit(self::BATCH_SIZE);				
			}
			else
			{
				$offset = self::BATCH_SIZE*($currentPageNumber - 1);
				$collection->getSelect()->limit(self::BATCH_SIZE,$offset);
			}			
			
            return $collection;
			
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public function saveToCsv($websiteId)
    {
        $this->_mapHeader = array('item');
        $this->_preparedData = array();
        $this->_prepareData();

        $path = $this->dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::PUB) . '/Scripts/emarsys';

        if (!is_dir($path)) {
            $this->ioFile->mkdir($path, 0775);
        }

        $name = 'products_' . $websiteId . '.csv';
        $file = $path . '/' . $name;

        $columnCount = count($this->_mapHeader);
        $emptyArray = array_fill(0, $columnCount, "");

        foreach ($this->_preparedData as &$row) {
            if (count($row) < $columnCount) {
                $row = $row + $emptyArray;
            }
        }

        $this->csvWriter
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($file, ([$this->_mapHeader] + $this->_preparedData));

        return array($file, $name);
    }
}