<?php
namespace Tatva\SLIFeed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;

class AttributeGenerator extends \SLI\Feed\Model\Generators\AttributeGenerator
{
    public function __construct(\Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry, 
            \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, 
            \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, 
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \SLI\Feed\Helper\GeneratorHelper $generatorHelper,
            \Magento\Swatches\Helper\Data $swatchHelper) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $attributeCollectionFactory, $storeManager, $generatorHelper, $swatchHelper);
    }
    
    public function updateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting attribute XML generation', $storeId));
        $this->initAttributes($storeId, $logger);

        $logger->debug(sprintf('[%s] Writing attributes', $storeId));

        $xmlWriter->startElement('attributes');

        if ($this->attributeValues) {
            $xmlWriter->writeAttributes($this->attributeValues);
        }

        // attributes
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Finished writing attributes', $storeId));

        return true;
    }
}