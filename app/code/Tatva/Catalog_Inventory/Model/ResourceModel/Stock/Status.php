<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Catalog_Inventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * CatalogInventory Stock Status per website Resource Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Status extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
{
    /**
     * Store model manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated 100.1.0
     */
    protected $_storeManager;

    /**
     * Website model factory
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var StockConfigurationInterface
     */
    protected  $stockConfiguration;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig,
         $connectionName = null

    ) {
        parent::__construct($context,$storeManager,$websiteFactory,$eavConfig,$connectionName);

        $this->_storeManager = $storeManager;
        $this->_websiteFactory = $websiteFactory;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
    }

    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        $manageStock=$this->scopeConfig->getValue('cataloginventory/item_options/manage_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($manageStock!=0)
        {
        $websiteId = $this->getWebsiteId();

            $joinCondition = $this->getConnection()->quoteInto(
                'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
                $websiteId
            );

            $joinCondition .= $this->getConnection()->quoteInto(
                ' AND stock_status_index.stock_id = ?',
                Stock::DEFAULT_STOCK_ID
            );
            $method = $isFilterInStock ? 'join' : 'joinLeft';
            $collection->getSelect()->$method(
                ['stock_status_index' => $this->getMainTable()],
                $joinCondition,
                ['is_salable' => 'stock_status']
            );

            if ($isFilterInStock) {
                $collection->getSelect()->where(
                    'stock_status_index.stock_status = ?',
                    Stock\Status::STATUS_IN_STOCK
                );
            }
    }
        return $collection;
    }
private function getWebsiteId($websiteId = null)
    {
        if (null === $websiteId) {
            $websiteId = $this->stockConfiguration->getDefaultScopeId();
        }

        return $websiteId;
    }



  }
    


