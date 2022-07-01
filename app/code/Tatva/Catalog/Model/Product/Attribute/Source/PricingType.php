<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Catalog\Model\Product\Attribute\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class PricingType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @param OptionFactory $optionFactory
     */
    public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Select Value'), 'value' => ''],
                ['label' => __('Old'), 'value' => 0],
                ['label' => __('New'), 'value' => 1],
                ['label' => __('Both'), 'value' => 2],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Pricing Type',
            ],
        ];
    }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->optionFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
