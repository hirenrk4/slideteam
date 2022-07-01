<?php

namespace Tatva\Customerloginlog\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('customerloginlog')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('customerloginlog'))
            ->addColumn(
                'customerloginlog_id',
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
                11,
                [
                    'nullable => false',
                ],
                'customer_id'
            )
            ->addColumn(
                'ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'ip'
            )
            ->addColumn(
                'timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                ],
                'timestamp'
            );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
