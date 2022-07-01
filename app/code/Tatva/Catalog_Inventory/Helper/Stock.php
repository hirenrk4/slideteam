<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Catalog_Inventory\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Stock
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stock extends \Magento\CatalogInventory\Helper\Stock
{
    /**
     * Store model manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Status
     */
    protected $stockStatusResource;

    /**
     * @var StatusFactory
     */
    protected $stockStatusFactory;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StatusFactory $stockStatusFactory
     * @param StockRegistryProviderInterface $stockRegistryProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        StatusFactory $stockStatusFactory,
        StockRegistryProviderInterface $stockRegistryProvider
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->stockStatusFactory  = $stockStatusFactory;
        $this->stockRegistryProvider = $stockRegistryProvider;
        return parent::__construct($storeManager,$scopeConfig,$stockStatusFactory,$stockRegistryProvider);
    }

    /**
     * Assign stock status information to product
     *
     * @param Product $product
     * @param int $status
     * @return void
     */
    public function assignStatusToProduct(Product $product, $status = null)
    {
        if ($status === null) {
            $scopeId = $this->getStockConfiguration()->getDefaultScopeId();
            $stockStatus = $this->stockRegistryProvider->getStockStatus($product->getId(), $scopeId);
            $status = $stockStatus->getStockStatus();
        }
        $product->setIsSalable(true);
    }
      protected function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
    }

  }
