<?php

namespace Tatva\Ebook\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
             if (!$installer->tableExists('productdownload_ebook_history_log')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('productdownload_ebooks_history_log')
                    )
                ->addColumn(
                    'log_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                    'auto_increment' => true
                    ],
                    'log_id'
                    )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [
                    'nullable => false',
                    'unsigned' => true,
                    ],
                    'product_id'
                    )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'nullable => false',
                    'default'=>0
                    ],
                    'customer_id'
                    )
                ->addColumn(
                    'download_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ],
                    'download_date'
                    )
                ->addColumn(
                    'ip',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'ip'
                    )
                ->addColumn(
                    'cookie_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    6,
                    [],
                    'cookie_id'
                    ) ->addColumn(
                    'browser',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'browser'
                    )
                ->addForeignKey( // Add foreign key for table entity
                    $installer->getFkName(
                    'productdownload_ebook_history_log', // New table
                    'product_id', // Column in New Table
                    'catalog_product_entity', // Reference Table
                    'entity_id' // Column in Reference table
                    ),
                'product_id', // New table column
                $installer->getTable('catalog_product_entity'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ;

                $installer->getConnection()->createTable($table);
            }
        }  
        $setup->endSetup();   
    }
}
