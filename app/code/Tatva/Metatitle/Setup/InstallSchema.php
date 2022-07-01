<?php
namespace Tatva\Metatitle\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('metatitle')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('metatitle')
                )
            ->addColumn(
                'metatitle_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
                'auto_increment' => true
                ],
                'metatitle_id'
                )
            ->addColumn(
                'metatitle',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [
                'nullable => false',
                ],
                'metatitle'
                )
            ->addColumn(
                'number_of_usage_product',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'nullable => false',
                ],
                'number_of_usage_product'
                )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'created_time'
                )
            ->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'update_time'
                );


            $installer->getConnection()->createTable($table);
        }


    }
}
