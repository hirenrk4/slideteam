<?php

namespace Tatva\CustomAttribute\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(!$installer->tableExists('coupon_customer_relation')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('coupon_customer_relation'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                'entity_id'
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
                'sales_rule_id',
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
                'coupon_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'coupon code'
            )
            ->addForeignKey(
                    $installer->getFkName('coupon_customer_relation', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                    $installer->getFkName('coupon_customer_relation', 'sales_rule_id', 'salesrule', 'rule_id'),
                    'sales_rule_id',
                    $installer->getTable('salesrule'),
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
