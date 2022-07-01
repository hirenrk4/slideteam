<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Helper\GeneratorHelper;

/**
 * Class ProductGenerator
 *
 * @package SLI\Feed\Model\Generators
 */
class ProductGenerator extends AbstractModel implements GeneratorInterface
{
    /**
     * Product collection
     *
     * @var CollectionFactory
     */
    protected $entityCollectionFactory;

    /**
     * Feed generation helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * Stock status
     * @var Status
     */
    protected $status;

    /**
     * Generate product info
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel\Product $resource
     * @param Collection $resourceCollection
     * @param CollectionFactory $entityCollectionFactory
     * @param GeneratorHelper $generatorHelper
     * @param Status $status
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        ResourceModel\Product $resource,
        Collection $resourceCollection,
        CollectionFactory $entityCollectionFactory,
        GeneratorHelper $generatorHelper,
        Status $status
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            []
        );

        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->generatorHelper = $generatorHelper;
        $this->status = $status;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] Starting product XML generation', $storeId));

        $entityCollection = $this->entityCollectionFactory->create();
        $extraAttributes = $this->generatorHelper->getAttributes($storeId, $logger);
        $logger->debug(sprintf('[%s] Extra attributes: %s', $storeId, implode(', ', $extraAttributes)));
        /** @var Collection $entityCollection */
        $entityCollection
            ->setStoreId($storeId)
            ->addAttributeToSelect($extraAttributes)// '*' for all, this also filters out all the bad values
            ->addStoreFilter($storeId)// Need to add to reduce the products that can show for the store
            ->addCategoryIds()
            ->setOrder('entity_id', Select::SQL_ASC);

        // Add the ratings to the collection if they are selected in the admin UI
        if (array_search('rating_summary', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding rating_summary to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                'rating_summary',
                'review_entity_summary',
                'rating_summary',
                'entity_pk_value=entity_id',
                ['entity_type' => 1, 'store_id' => $storeId],
                'left');
        }

        // Need to find a way to add this into the above statement
        // Add the ratings counts to the collection if they are selected in the admin UI
        if (array_search('reviews_count', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding reviews_count to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                'reviews_count',
                'review_entity_summary',
                'reviews_count',
                'entity_pk_value=entity_id',
                ['entity_type' => 1, 'store_id' => $storeId],
                'left');
        }

        $filterInStock = !$this->generatorHelper->isIncludeOutOfStock($storeId);
        $logger->debug(sprintf('[%s] Filter in stock products only = %s', $storeId, ($filterInStock ? 'true' : 'false')));
        $this->status->addStockDataToCollection($entityCollection, $filterInStock);

        $logger->debug(sprintf('[%s] Adding price data to collection', $storeId));

        // These need to be done later on as they actually call the SQL so it must go after the SQL
        $entityCollection = $entityCollection
            ->addPriceData()
            ->addUrlRewrite();

        $page = 0;
        $processed = 0;
        $pageSize = 1000;
        $uniqueIdMap = [];

        $xmlWriter->startElement('products');

        $entityCollection->setPage(++$page, $pageSize);
        $logger->debug(sprintf("[%s] Product collection select: %s ", $storeId, $entityCollection->getSelectSql(true)));

        while ($items = $entityCollection->getItems()) {
            /** @var Product $item */
            foreach ($items as $item) {
                // depending on the requested data some products might be coming back more than once
                if (array_key_exists($item->getId(), $uniqueIdMap)) {
                    continue;
                }
                $uniqueIdMap[$item->getId()] = $item->getId();
                ++$processed;

                $this->writeProduct($xmlWriter, $item, array_keys($item->_data), $extraAttributes);
            }

            $logger->debug(sprintf('[%s] Finished writing product page %s', $storeId, $page));

            if (count($items) < $pageSize) {
                break;
            }

            $entityCollection->setPage(++$page, $pageSize);
            $entityCollection->clear();
        }

        // products
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Product generator: processed items: %s, pages: %s', $storeId, $processed, $page));

        return true;
    }

    /**
     * Write XML for a single product
     *
     * @param XmlWriter $xmlWriter
     * @param Product $product
     * @param array $attributes
     * @param array $extraAttributes
     * @return void
     */
    protected function writeProduct(XmlWriter $xmlWriter, Product $product, array $attributes = [], array $extraAttributes = [])
    {
        // function to write a single (structured) value
        $writeValue = function ($value, $level, $writeValue) use ($xmlWriter) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $xmlWriter->startElement('value_' . $level);
                    $writeValue($v, $level + 1, $writeValue);
                    $xmlWriter->endElement();
                }
            } elseif (is_bool($value)) {
                $xmlWriter->text($value ? 'true' : 'false');
            } else {
                $xmlWriter->text($value);
            }
        };
        // function to add a single element with some support for types
        $writeElement = function ($name, $value) use ($xmlWriter, $writeValue) {
            $xmlWriter->startElement($name);
            $writeValue($value, 1, $writeValue);
            $xmlWriter->endElement();
        };

        $xmlWriter->startElement('product');
        $xmlWriter->writeAttribute('id', $product->getId());
        $xmlWriter->writeAttribute('sku', $product->getSku());

        // regular attributes
        foreach ($attributes as $attribute) {
            $writeElement($attribute, $product->getData($attribute));
        }

        // Array of XML Node Name => Product Object function call which obtains the ids for each relationship type.
        $linkedAttributesFunctionMap = [
            'related_products' => 'getRelatedProductIds',
            'upsell_products' => 'getUpsellProductIds',
            'crosssell_products' => 'getCrossSellProductIds'
        ];

        foreach ($linkedAttributesFunctionMap as $label => $method) {
            if (in_array($label, $extraAttributes)) {
                $writeElement($label, $product->{$method}());
            }
        }

        $type = $product->getTypeInstance();
        $childrenIds = $type->getChildrenIds($product->getId());
        $writeElement('child_ids', $childrenIds);

        // custom properties
        $properties = [
            'CategoryIds' => 'categories',
            'IsVirtual' => 'is_virtual'
        ];
        foreach ($properties as $property => $element) {
            $method = 'get' . $property;
            $value = $product->$method();
            $writeElement($element, $value);
        }

        // product
        $xmlWriter->endElement();
    }
}
