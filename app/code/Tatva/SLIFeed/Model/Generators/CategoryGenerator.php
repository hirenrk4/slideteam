<?php
namespace Tatva\SLIFeed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\XmlWriter;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CategoryGenerator extends \SLI\Feed\Model\Generators\CategoryGenerator
{
    public function __construct(Context $context, Registry $registry, ExtensionAttributesFactory $extensionFactory,
            AttributeValueFactory $customAttributeFactory, CollectionFactory $collectionFactory, StoreManagerInterface $storeManager, 
            ResourceModel\Category $resource, Collection $resourceCollection,ScopeConfigInterface $scopeConfig) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $collectionFactory, $storeManager, $resource, $resourceCollection,$scopeConfig);
    }
    
    public function updateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting category XML generation', $storeId));

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $entityCollection */

        // $resumecategoryId = $this->_scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
        // $ebookcategoryId = $this->_scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        
        //$catalog_ids = [$resumecategoryId,$ebookcategoryId];
        
        $entityCollection = $this->entityCollectionFactory->create();
        //$entityCollection->addFieldToFilter('entity_id',array("nin"=>$catalog_ids))
        $entityCollection->setStoreId($storeId)
            ->setProductStoreId($storeId)
            ->addAttributeToSelect(['*']);

        $page = 0;
        $processed = 0;
        $pageSize = 1000;

        $xmlWriter->startElement('categories');

        $entityCollection->setPage(++$page, $pageSize);
        while ($items = $entityCollection->getItems()) {
            foreach ($items as $item) {
                ++$processed;
                $this->writeCategory($xmlWriter, $item);
            }

            if (count($items) < $pageSize) {
                break;
            }

            $entityCollection->setPage(++$page, $pageSize);
            $entityCollection->clear();
        }

        // categories
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Category generator: processed items: %s, pages: %s', $storeId, $processed, $page));

        return true;
    }
    
}