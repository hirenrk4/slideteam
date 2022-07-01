<?php
namespace Tatva\ProductImport\Setup;
 class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('product_import_details')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('product_import_details')
            )
                ->addColumn(
                    'profiler_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'sentence_id'
                )
                ->addColumn(
                    'profiler_start_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [
                      'nullable' => false,
                    ],
                    'profiler_start_time'
                )
                ->addColumn(
                    'productsku',
                     \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [
                     'nullable' => false,
                    ],
                    'productsku'
                )
                ->addColumn(
                    'productname',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [
                      'nullable' => false,
                    ],
                    'productname'
                ) 
                ->addColumn(
                    'import_start_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [
                      'nullable' => false,
                    ],
                    'import_start_time'
                )
                ->addColumn(
                    'import_end_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [
                    'nullable' => false,
                    ],
                    'import_end_time'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '10',
                    [
                    'nullable' => false,
                    ],
                    'status'
                )->addColumn(
                    'error',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '11',
                    [
                    'nullable' => false,
                    ],
                    'error'
                )
                ->addColumn(
                    'profiler_end_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [
                    'nullable' => false,
                    ],
                    'profiler_end_time'
                );

            $installer->getConnection()->createTable($table);
            }

       $installer->endSetup();
    }
}
