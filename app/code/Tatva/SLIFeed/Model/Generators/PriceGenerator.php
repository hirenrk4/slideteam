<?php
namespace Tatva\SLIFeed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;
use SLI\Feed\Model\Generators\Helpers\GroupMapLoader;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Helper\GeneratorHelper;

class PriceGenerator extends \SLI\Feed\Model\Generators\PriceGenerator
{
    public function __construct(Context $context, Registry $registry, ExtensionAttributesFactory $extensionFactory, 
            AttributeValueFactory $customAttributeFactory, StoreManagerInterface $storeManager, 
            ResourceModel\Product $resource, ResourceModel\Product\Collection $resourceCollection, 
            GeneratorHelper $generatorHelper, ResourceModel\Product\CollectionFactory $productCollectionFactory,
            GroupMapLoader $groupMapLoader, RuleFactory $ruleFactory, Stock $stockHelper) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $storeManager, $resource, $resourceCollection, $generatorHelper, $productCollectionFactory, $groupMapLoader, $ruleFactory, $stockHelper);
    }
    
    public function updateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->logger->debug(sprintf('[%s] starting advanced pricing generator', $storeId));

        if(!$this->generatorHelper->isPriceFeedEnabled($storeId)) {
            $this->logger->debug(sprintf('[%s] Price XML generation disabled', $storeId));
            return true;
        }

        $logger->debug(sprintf('[%s] Starting price XML generation', $storeId));

        $xmlWriter->startElement('advanced_pricing');

        $this->addPricesToFeed($storeId, $xmlWriter);

        // advanced_pricing
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Finished writing pricing', $storeId));

        return true;
    }
    
}