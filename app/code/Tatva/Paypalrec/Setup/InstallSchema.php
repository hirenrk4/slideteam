<?php
namespace Tatva\Paypalrec\Setup;

/**
 * 
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('tatva_pp_recurring_mapper')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tatva_pp_recurring_mapper')
            )
                ->addColumn(
                    'map_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Map Id'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Customer Id'
                )
                ->addColumn(
                    'checkout_token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Token while checkout in paypal'
                )
                ->addColumn(
                    'invoice',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'invoice/increment_id generated while order placed'
                )
                ->addColumn(
                    'rp_profile_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'paypal recurring profile id generated while recurring profile creation'
                )
                ->addColumn(
                        'created_at',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                        'Created At'
                )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At')
                ->setComment('Tatva Paypal Recurring Mapping of invoice  with rp_profile_id ');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('tatva_pp_recurring_mapper'),
                $setup->getIdxName(
                    $installer->getTable('tatva_pp_recurring_mapper'),
                    ['customer_id','invoice'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['customer_id','invoice'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );

            $installer->getConnection()->addIndex(
                $installer->getTable('tatva_pp_recurring_mapper'),
                $setup->getIdxName(
                    $installer->getTable('tatva_pp_recurring_mapper'),
                    ['checkout_token','rp_profile_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['checkout_token','rp_profile_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
    

}
