<?php

namespace Tatva\Deleteaccount\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if(!$installer->tableExists('tatva_delacc_customer_bkp')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('tatva_delacc_customer_bkp'))
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'primary'  => true,
                    'auto_increment' => false,
                ],
                'entity_id of customer_entity tbl'
            )
            ->addColumn(
                'email_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'email_id'
            )
            ->addColumn(
                'firstname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'firstname'
            )
            ->addColumn(
                'lastname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'lastname'
            )
            ->addColumn(
                'created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                ],
                'created_date'
            )
            ->addColumn(
                'deleted_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                ],
                'deleted_date'
            );

            $installer->getConnection()->createTable($table);
        }

        if(!$installer->tableExists('tatva_delacc_subscription_bkp')) 
        {
            $table = $installer->getConnection()->newTable($installer->getTable('tatva_delacc_subscription_bkp'))
            ->addColumn(
                'subscription_bkp_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'auto_increment' => false,
                ],
                'subscription backup id'
            )
            ->addColumn(
                'del_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'nullable' => false,
                    'unsigned' => false,
                ],
                'FK from tatva_delacc_customer_bkp tbl'
            )
            ->addColumn(
                'subscription_period',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'subscription period'
            )
            ->addColumn(
                'amount_paid',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                255,
                [
                    'nullable => false',
                ],
                'amount paid'
            )
            ->addColumn(
                'payment_method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                    'nullable => false',
                ],
                'payment method'
            )
            ->addColumn(
                'downloads',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                8,
                [
                    'nullable => false',
                ],
                'downloads'
            )
            ->addColumn(
                'start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                ],
                'start date'
            )
            ->addColumn(
                'end_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                255,
                [
                    'nullable => false',
                ],
                'end date'
            )
            ->addIndex(
                $installer->getIdxName('tatva_delacc_subscription_bkp', ['del_customer_id']),
                ['del_customer_id']
            )
            ->addForeignKey(
                $installer->getFkName('tatva_delacc_subscription_bkp', 'del_customer_id', 'tatva_delacc_customer_bkp', 'customer_id'),
                'del_customer_id',
                $installer->getTable('tatva_delacc_customer_bkp'),
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
