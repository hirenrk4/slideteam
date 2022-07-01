<?php
namespace Tatva\Popup\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('tatva_popup')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tatva_popup')
                )
            ->addColumn(
                'popup_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
                'auto_increment' => true
                ],
                'popup_id'
                )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false,
                ],
                'title'
                )
            ->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false,
                ],
                'identifier'
                )
            ->addIndex(
                $installer->getIdxName(
                    $installer->getTable('tatva_popup'),
                    ['identifier'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['identifier'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                [
                'nullable' => false,
                ],
                'status'
                )
            ->addColumn(
                'download_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'nullable' => true,
                ],
                'download_count'
                )
            ->addColumn(
                'popup_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [
                'nullable' => false,
                ],
                'popup_content'
                )
            ->addColumn(
                'popup_js',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'M',
                [
                'nullable' => true,
                ],
                'popup_js'
                )
            ->addColumn(
                'popup_css',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'M',
                [
                'nullable' => true,
                ],
                'popup_css'
                )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'created_time'
                )
            ->addColumn(
                'updated_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'updated_time'
                );


            $installer->getConnection()->createTable($table);
        }


    }
}
