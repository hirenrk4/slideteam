<?php
namespace Tatva\Translate\Setup;


class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('languages')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('languages')
            )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'laguage',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Language'
                )
                ->addColumn(
                    'laguage_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Language Code'
                )
                ->setComment('Multilanguages');
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('multilanguage_data')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multilanguage_data')
            )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => false,
                ],
                'entity_id of catalog_product_entity'
                )
                ->addColumn(
                'lang_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => false,
                ],
                'entity_id of languages'
                )
                ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'auto_increment' => false,
                ],
                'entity_id of eav_attribute'
                )
                ->addColumn(
                    'value',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Translated Value'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'multilanguage_data',
                        ['product_id','lang_id','attribute_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id','lang_id','attribute_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName('multilanguage_data', 'product_id', 'catalog_product_entity', 'entity_id'),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('multilanguage_data', 'lang_id', 'languages', 'entity_id'),
                    'lang_id',
                    $installer->getTable('languages'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('multilanguage_data', 'attribute_id', 'eav_attribute', 'attribute_id'),
                    'attribute_id',
                    $installer->getTable('eav_attribute'),
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Multilanguage Data Table');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
