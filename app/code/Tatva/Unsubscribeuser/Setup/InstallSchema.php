<?php

namespace Tatva\Unsubscribeuser\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(!$installer->tableExists('tatva_unsubscribe_user')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('tatva_unsubscribe_user'))
            ->addColumn(
            'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'primary'  => true,
                    'auto_increment' => true,
                ],
                'entity_id of tbl'
            )
            ->addColumn(
            'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'primary'  => false,
                    'auto_increment' => false,
                ],
                'entity_id of customer_entity tbl'
            )
            ->addColumn(
            'subscription_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'primary'  => false,
                    'auto_increment' => false,
                ],
                'subscription_id'
            )
            ->addColumn(
                'unsubscribe_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                ],
                'unsubscribe_date'
            )
            ->addColumn(
                'sort',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => true],
                'sort'
            )->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => true,'default' => null],
                'reason'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => true,'default' => null],
                'status'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
