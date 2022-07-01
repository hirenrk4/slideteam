<?php
namespace Tatva\SLIFeed\Model\Generators;

use DateTime;
use Magento\Framework\AppInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\GeneratorHelper;
use SLI\Feed\Helper\XmlWriter;

class MetaGenerator extends \SLI\Feed\Model\Generators\MetaGenerator
{
    public function __construct(StoreManagerInterface $storeManager, GeneratorHelper $generatorHelper, ProductMetadataInterface $productMetadata) {
        parent::__construct($storeManager, $generatorHelper, $productMetadata);
    }
    public function updateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting meta XML generation', $storeId));

        $logger->debug(sprintf('[%s] Writing meta', $storeId));

        $xmlWriter->startElement('meta');

        $xmlWriter->writeAttribute('storeId', $storeId);

        $xmlWriter->writeElement('lscVersion', $this->generatorHelper->getVersion());
        $xmlWriter->writeElement('magentoVersion', $this->productMetadata->getVersion());
        $xmlWriter->writeElement('magentoEdition', $this->productMetadata->getEdition());
        $xmlWriter->writeElement('magentoName', $this->productMetadata->getName());
        $xmlWriter->writeElement('context', 'cli' == php_sapi_name() ? 'CLI' : 'UI');
        $xmlWriter->writeElement('baseUrl', $this->generatorHelper->getBaseUrl($storeId));

        $created = new DateTime();
        $xmlWriter->writeElement('created', $created->format(DateTime::ISO8601));

        $extraAttributes = $this->generatorHelper->getAttributes($storeId, $logger);
        $xmlWriter->writeElement('extraAttributes', implode(', ', $extraAttributes));

        // meta
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Finished writing meta', $storeId));

        return true;
    }
}