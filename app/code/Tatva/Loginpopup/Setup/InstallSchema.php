<?php
namespace Tatva\Loginpopup\Setup;
 class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('customer_additional_data')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('customer_additional_data')
            )
                ->addColumn(
                    'id',
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
                    'industry',
                     \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    55,
                    [
                    'nullable => false',
                    ],
                    'industry'
                )
                ->addColumn(
                    'job_profile',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '55',
                    [
                    'nullable => false',
                    ],
                    'job_profile'
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

       $installer->endSetup();
    }
}
