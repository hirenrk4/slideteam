<?php
namespace Tatva\Sentence\Setup;
 class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('sentence')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('sentence')
            )
                ->addColumn(
                    'sentence_id',
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
                    'sentence',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    11,
                    [
                    'nullable => false',
                    ],
                    'sentence'
                )
                ->addColumn(
                    'number_of_usage',
                     \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'default' => 0,
                    ],
                    'number_of_usage'
                )
                ->addColumn(
                    'number_of_usage_product',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    '11',
                    [
                    'default' => 0,
                    ],
                    'number_of_usage_product'
                ) 
                ->addColumn(
                    'created_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    '11',
                    [
                    'default' => null,
                    ],
                    'created_time'
                )
                ->addColumn(
                    'update_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    '11',
                    [
                    'default' => null,
                    ],
                    'update_time'
                );

            $installer->getConnection()->createTable($table);
            }

       $installer->endSetup();
    }
}
