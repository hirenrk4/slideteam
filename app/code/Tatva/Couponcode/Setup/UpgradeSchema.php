<?php
/**
 * Copyright © 2016 AionNext Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Couponcode\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema, add sort_order
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            
            $installer->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'deal_of_the_day',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Coupon is Deal of the day'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('salesrule'),
                'display_on_frontend',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Coupon display on frontend'
                ]
            );
            $installer->endSetup();
        }
        
    }
}