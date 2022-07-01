<?php

namespace Tatva\CouponCode\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(!$installer->tableExists('tatva_coupon_rating_info')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('tatva_coupon_rating_info'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                'id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => false,
                ],
                'entity_id of customer_entity tbl'
            )
            ->addColumn(
                'coupon_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => false,
                ],
                'rule_id of salesrule tbl'
            )
            ->addColumn(
                'rating_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'rating_action like or dislike'
            )->addForeignKey(
                    $installer->getFkName('tatva_coupon_rating_info', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                    $installer->getFkName('tatva_coupon_rating_info', 'coupon_id', 'salesrule', 'rule_id'),
                    'coupon_id',
                    $installer->getTable('salesrule'),
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
